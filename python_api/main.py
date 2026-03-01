"""
Event Reminder – AI Extraction Service
=======================================
Stack : FastAPI + PyMuPDF + Google Gemini
Run   : uvicorn main:app --reload --port 8000
"""

import os
import json
import re
import tempfile
import subprocess
from contextlib import asynccontextmanager

import fitz  # PyMuPDF
import google.generativeai as genai
from apscheduler.schedulers.background import BackgroundScheduler
from dotenv import load_dotenv
from fastapi import FastAPI, File, HTTPException, UploadFile
from fastapi.responses import JSONResponse

load_dotenv()

# ── Gemini setup ──────────────────────────────────────────────────────────────
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
if not GEMINI_API_KEY:
    raise RuntimeError("GEMINI_API_KEY is not set. Copy .env.example to .env and add your key.")

genai.configure(api_key=GEMINI_API_KEY)

def run_php_reminders():
    """Execute the PHP reminder script via the CLI."""
    print("⏰ [Background Task] Running automated reminders...")
    try:
        # Resolve the path to send_reminders.php relative to this script
        # This assumes python_api/ is a subfolder of the project root
        project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        script_path = os.path.join(project_root, "send_reminders.php")
        
        if not os.path.exists(script_path):
            print(f"❌ Error: Could not find {script_path}")
            return

        # Attempt to run with 'php' from PATH
        result = subprocess.run(["php", script_path], capture_output=True, text=True)
        
        if result.returncode == 0:
            print(f"✅ Reminders output: {result.stdout.strip()}")
        else:
            print(f"⚠️ PHP Error: {result.stderr.strip()}")

    except Exception as e:
        print(f"❌ Exception during reminder task: {e}")

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Start the background scheduler on startup and shut it down on exit."""
    scheduler = BackgroundScheduler()
    # Schedule to run every 5 minutes
    scheduler.add_job(run_php_reminders, 'interval', minutes=5)
    scheduler.start()
    print("🚀 Background scheduler started (Interval: 5m)")
    yield
    scheduler.shutdown()
    print("🛑 Background scheduler shut down")

app = FastAPI(
    title="Event Extractor API", 
    version="1.0.0",
    lifespan=lifespan
)

# ── Helpers ───────────────────────────────────────────────────────────────────

def extract_text_from_pdf(pdf_bytes: bytes) -> str:
    """Extract plain text from a PDF using PyMuPDF (handles multi-column, tables, etc.)."""
    with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as tmp:
        tmp.write(pdf_bytes)
        tmp_path = tmp.name
    try:
        doc = fitz.open(tmp_path)
        pages = [page.get_text() for page in doc]
        doc.close()
        return "\n".join(pages).strip()
    finally:
        os.unlink(tmp_path)


def extract_events_with_gemini(raw_text: str) -> list[dict]:
    """Send raw calendar text to Gemini and get back a structured list of events."""
    model = genai.GenerativeModel("gemini-2.5-flash")

    prompt = f"""
You are an expert academic calendar parser.

Your task: read the calendar text below and extract every event, deadline, examination, holiday, or important date you find.

Return ONLY a valid JSON array — no markdown, no explanation, no code fences.
Each element must be a JSON object with exactly these three keys:
  "title"       : short name of the event (string)
  "description" : one-sentence detail or empty string ""
  "event_date"  : date/time in "YYYY-MM-DD HH:MM:SS" format
                  • Use 09:00:00 as the default time when no time is given.
                  • For date ranges (e.g. "Oct 14-18"), use the first day.
                  • Infer the year from context if not explicitly stated.

Calendar Text:
\"\"\"
{raw_text}
\"\"\"

JSON Array:
"""

    response = model.generate_content(prompt)
    text = response.text.strip()

    # Strip markdown code fences if Gemini wrapped the JSON
    text = re.sub(r"^```(?:json)?\s*", "", text)
    text = re.sub(r"\s*```$", "", text)

    try:
        events = json.loads(text)
    except json.JSONDecodeError as exc:
        raise ValueError(f"Gemini returned non-JSON output: {exc}\n\nRaw response:\n{text}") from exc

    if not isinstance(events, list):
        raise ValueError("Gemini did not return a JSON array.")

    return events


# ── Routes ────────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "service": "event-extractor"}


@app.post("/extract-events")
async def extract_events(file: UploadFile = File(...)):
    """
    Accept a PDF, extract text with PyMuPDF, parse events with Gemini.
    Returns: { "events": [...], "total": N }
    """
    if not file.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Only PDF files are accepted.")

    pdf_bytes = await file.read()
    if not pdf_bytes:
        raise HTTPException(status_code=400, detail="Received an empty file.")

    # Step 1 – extract raw text
    try:
        raw_text = extract_text_from_pdf(pdf_bytes)
    except Exception as exc:
        raise HTTPException(status_code=422, detail=f"PDF text extraction failed: {exc}")

    if not raw_text:
        raise HTTPException(status_code=422, detail="No readable text found in PDF.")

    # Step 2 – parse events with Gemini
    try:
        events = extract_events_with_gemini(raw_text)
    except ValueError as exc:
        raise HTTPException(status_code=502, detail=str(exc))
    except Exception as exc:
        raise HTTPException(status_code=502, detail=f"Gemini API error: {exc}")

    return JSONResponse(content={"events": events, "total": len(events)})