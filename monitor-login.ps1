#!/usr/bin/env pwsh

# Monitor login attempts in real-time
$logFile = "storage/logs/laravel.log"

Write-Host "=== Login Monitor ===" -ForegroundColor Cyan
Write-Host "Monitoring: $logFile" -ForegroundColor Yellow
Write-Host "Waiting for login attempts..." -ForegroundColor Green
Write-Host ""

# Get initial file size
$initialSize = if (Test-Path $logFile) { (Get-Item $logFile).Length } else { 0 }

# Monitor for changes
$lastCheck = Get-Date
$checkInterval = 1  # Check every 1 second

while ($true) {
    Start-Sleep -Seconds $checkInterval
    
    if (Test-Path $logFile) {
        $currentSize = (Get-Item $logFile).Length
        
        # If file size changed, read new content
        if ($currentSize -gt $initialSize) {
            $newContent = Get-Content $logFile -Tail 50 | Select-String -Pattern "LOGIN|Captcha|Auth|captcha|Validation|credentials"
            
            if ($newContent) {
                Write-Host "`n[$(Get-Date -Format 'HH:mm:ss')] New log entries:" -ForegroundColor Green
                $newContent | ForEach-Object {
                    if ($_ -match "error|failed|FAIL") {
                        Write-Host $_ -ForegroundColor Red
                    } elseif ($_ -match "success|SUCCESS|passed") {
                        Write-Host $_ -ForegroundColor Green
                    } else {
                        Write-Host $_ -ForegroundColor Yellow
                    }
                }
                Write-Host ""
            }
            
            $initialSize = $currentSize
        }
    }
}
