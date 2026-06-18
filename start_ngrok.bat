@echo off
:: ============================================================
:: Start the ngrok tunnel for Laravel (port 80)
:: The LSTM service is proxied through Apache at /ai/
:: so only ONE tunnel is needed.
::
:: Architecture:
::   Browser → underwear-unmade-acclimate.ngrok-free.dev → Apache :80
::   /ai/* requests → Apache reverse proxy → FastAPI :8001
::
:: Before running this:
::   1. Start LSTM service:  app\Services\AI\start_lstm.bat
::   2. Start Laragon (Apache must be running)
::   3. Run this script
:: ============================================================
echo.
echo  Starting ngrok tunnel...
echo  Public URL: https://underwear-unmade-acclimate.ngrok-free.dev
echo  Laravel:    https://underwear-unmade-acclimate.ngrok-free.dev/
echo  LSTM API:   https://underwear-unmade-acclimate.ngrok-free.dev/ai/
echo.
"C:\Users\Davina\Downloads\ngrok-v3-stable-windows-amd64\ngrok.exe" start laravel
