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

set "PYTHON_EXE=python"
if exist "%~dp0.venv\Scripts\python.exe" set "PYTHON_EXE=%~dp0.venv\Scripts\python.exe"

"%PYTHON_EXE%" -m uvicorn app.Services.AI.LSTM_Service:app --host 127.0.0.1 --port 8001 --reload

pause
