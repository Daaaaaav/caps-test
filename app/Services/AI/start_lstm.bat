@echo off
:: ============================================================
:: LSTM Service Startup Script
:: Run this from the project root OR from app/Services/AI
:: ============================================================

:: Move to the directory containing LSTM_Service.py
cd /d "%~dp0"

echo.
echo  Starting LSTM Forecast Service...
echo  URL: http://127.0.0.1:8001
echo  Docs: http://127.0.0.1:8001/docs
echo  Press Ctrl+C to stop.
echo.

python -m uvicorn LSTM_Service:app --host 0.0.0.0 --port 8001 --reload
