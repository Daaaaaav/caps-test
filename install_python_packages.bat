@echo off
echo Installing Python packages for LSTM Service...
echo.
echo NOTE: If this fails, run PowerShell as Administrator and execute:
echo python -m pip install --user fastapi uvicorn tensorflow pandas scikit-learn
echo.

python -m pip install --user fastapi uvicorn tensorflow pandas scikit-learn

echo.
echo Installation complete!
echo.
echo To start the LSTM service, run:
echo python app/Services/AI/LSTM_Service.py
echo.
pause
