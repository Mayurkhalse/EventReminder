<?php
// Purpose: Configuration for the Python AI extraction microservice.

// URL of the running FastAPI service (python_api/main.py)
define('AI_API_URL', 'http://localhost:8000/extract-events');

// Max seconds to wait for Gemini to respond (PDF parsing + LLM call can be slow)
define('AI_API_TIMEOUT', 90);
