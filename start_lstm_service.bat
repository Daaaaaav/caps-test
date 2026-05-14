@echo off
echo ========================================
echo  Starting LSTM Prediction Service
echo ========================================
echo.
echo  URL  : http://127.0.0.1:8001
echo  Docs : http://127.0.0.1:8001/docs
echo  Demo : http://127.0.0.1:8001/demo
echo.
echo  Press Ctrl+C to stop the service
echo ========================================
echo.

cd /d "%~dp0"

:: Suppress TensorFlow oneDNN info messages
set TF_ENABLE_ONEDNN_OPTS=0
set TF_CPP_MIN_LOG_LEVEL=2

:: Use venv python if available, otherwise system python
set "PYTHON_EXE=python"
if exist "%~dp0.venv\Scripts\python.exe" set "PYTHON_EXE=%~dp0.venv\Scripts\python.exe"

:: --reload causes crashes on Windows with TensorFlow — do NOT add it back
"%PYTHON_EXE%" -m uvicorn app.Services.AI.LSTM_Service:app --host 127.0.0.1 --port 8001

pause
