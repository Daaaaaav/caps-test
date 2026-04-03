@echo off
echo ========================================
echo Starting LSTM Prediction Service
echo ========================================
echo.
echo Service will run on: http://127.0.0.1:8001
echo.
echo Press Ctrl+C to stop the service
echo.

cd /d "%~dp0"

python -m uvicorn app.Services.AI.LSTM_Service:app --host 127.0.0.1 --port 8001 --reload

pause
