# ============================================================
# update_lstm_url.ps1
#
# Queries the ngrok local API to find the LSTM tunnel URL
# and automatically patches LSTM_SERVICE_URL in .env
#
# Usage (run from project root after ngrok is already running):
#   .\update_lstm_url.ps1
# ============================================================

$envFile = Join-Path $PSScriptRoot ".env"

Write-Host ""
Write-Host " Querying ngrok API for active tunnels..." -ForegroundColor Cyan

try {
    $tunnels = (Invoke-RestMethod "http://127.0.0.1:4040/api/tunnels").tunnels
} catch {
    Write-Host " ERROR: Could not reach ngrok API at http://127.0.0.1:4040" -ForegroundColor Red
    Write-Host "        Make sure ngrok is running (start_ngrok.bat)" -ForegroundColor Yellow
    exit 1
}

# Find the tunnel pointing to port 8001 (the LSTM service)
$lstmTunnel = $tunnels | Where-Object {
    $_.config.addr -match "8001" -and $_.proto -eq "https"
}

if (-not $lstmTunnel) {
    # ngrok may have created an http tunnel only; grab that and convert to https
    $lstmTunnel = $tunnels | Where-Object { $_.config.addr -match "8001" } | Select-Object -First 1
}

if (-not $lstmTunnel) {
    Write-Host ""
    Write-Host " Active tunnels:" -ForegroundColor Yellow
    $tunnels | ForEach-Object { Write-Host "   $($_.public_url) -> $($_.config.addr)" }
    Write-Host ""
    Write-Host " ERROR: No tunnel found for port 8001." -ForegroundColor Red
    Write-Host "        Start the LSTM service first: app\Services\AI\start_lstm.bat" -ForegroundColor Yellow
    exit 1
}

$lstmUrl = $lstmTunnel.public_url -replace "^http://", "https://"

Write-Host " LSTM tunnel found: $lstmUrl" -ForegroundColor Green

# Read .env, replace LSTM_SERVICE_URL line
$envContent = Get-Content $envFile -Raw
$updated = $envContent -replace "LSTM_SERVICE_URL=.*", "LSTM_SERVICE_URL=$lstmUrl"

if ($envContent -eq $updated) {
    Write-Host " WARNING: LSTM_SERVICE_URL line not found in .env — appending." -ForegroundColor Yellow
    $updated = $envContent.TrimEnd() + "`nLSTM_SERVICE_URL=$lstmUrl`n"
}

Set-Content $envFile $updated -NoNewline -Encoding UTF8

Write-Host ""
Write-Host " .env updated:" -ForegroundColor Green
Write-Host "   LSTM_SERVICE_URL=$lstmUrl" -ForegroundColor White
Write-Host ""
Write-Host " Run 'php artisan config:clear' to flush the Laravel config cache." -ForegroundColor Cyan
Write-Host ""
