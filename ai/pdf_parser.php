<?php
// Purpose: PDF text extraction.
// ── REPLACED ──────────────────────────────────────────────────────────────────
// Text extraction is now handled entirely by the Python AI service (python_api/main.py)
// using PyMuPDF, which is far more accurate for real-world PDFs (multi-column,
// tables, scanned-style layouts) than any pure-PHP alternative.
//
// This file is retained only for reference and is no longer included anywhere.
// See: ai/ai_event_extractor.php → calls http://localhost:8000/extract-events
// ─────────────────────────────────────────────────────────────────────────────
