# SQL Injection Detection Test Script
# Usage: .\test-sqli-detection.ps1 -BaseUrl "https://your-ngrok-url.ngrok.io"

param(
    [Parameter(Mandatory=$false)]
    [string]$BaseUrl = "http://localhost:8000"
)

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " SQL Injection Detection Test" -ForegroundColor Cyan
Write-Host " Wazuh Monitoring Effectiveness Test" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Target URL: $BaseUrl" -ForegroundColor White
Write-Host ""

$testCases = @(
    @{
        name = "1. Classic OR-based SQLi"
        email = "admin' OR '1'='1"
        password = "test123"
        description = "Tests basic boolean-based SQL injection"
    },
    @{
        name = "2. Comment-based SQLi"
        email = "admin'--"
        password = "anything"
        description = "Tests SQL comment injection"
    },
    @{
        name = "3. UNION-based SQLi"
        email = "admin' UNION SELECT NULL,NULL,NULL--"
        password = "test"
        description = "Tests UNION query injection"
    },
    @{
        name = "4. Boolean SQLi with spaces"
        email = "admin' OR 1=1--"
        password = "test"
        description = "Tests boolean with numeric comparison"
    },
    @{
        name = "5. DROP TABLE attempt"
        email = "admin'; DROP TABLE users--"
        password = "test"
        description = "Tests destructive SQL command"
    },
    @{
        name = "6. SELECT FROM injection"
        email = "admin' OR 1=1; SELECT * FROM users--"
        password = "test"
        description = "Tests data extraction attempt"
    },
    @{
        name = "7. Time-based blind SQLi"
        email = "admin' AND SLEEP(5)--"
        password = "test"
        description = "Tests time-based blind injection"
    },
    @{
        name = "8. Stacked queries"
        email = "admin'; UPDATE users SET role='admin'--"
        password = "test"
        description = "Tests privilege escalation via stacked queries"
    }
)

$passed = 0
$failed = 0
$errors = 0

foreach ($test in $testCases) {
    Write-Host "Testing: $($test.name)" -ForegroundColor Yellow
    Write-Host "  Description: $($test.description)" -ForegroundColor Gray
    Write-Host "  Payload: $($test.email)" -ForegroundColor Gray
    
    $body = "email=$([System.Web.HttpUtility]::UrlEncode($test.email))&password=$([System.Web.HttpUtility]::UrlEncode($test.password))"
    
    try {
        $response = Invoke-WebRequest `
            -Uri "$BaseUrl/login" `
            -Method POST `
            -Body $body `
            -ContentType "application/x-www-form-urlencoded" `
            -UseBasicParsing `
            -ErrorAction Stop `
            -TimeoutSec 10
        
        Write-Host "  ❌ FAILED: Expected 403, got $($response.StatusCode)" -ForegroundColor Red
        Write-Host "  Risk: Injection not detected by security monitor!" -ForegroundColor Red
        $failed++
    } 
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        
        if ($statusCode -eq 403) {
            Write-Host "  ✅ PASSED: Blocked with 403 Forbidden" -ForegroundColor Green
            $passed++
        }
        elseif ($statusCode) {
            Write-Host "  ⚠️  UNEXPECTED: Got status code $statusCode" -ForegroundColor Yellow
            Write-Host "  Expected: 403 Forbidden" -ForegroundColor Yellow
            $errors++
        }
        else {
            Write-Host "  ⚠️  CONNECTION ERROR: $($_.Exception.Message)" -ForegroundColor Yellow
            $errors++
        }
    }
    
    Write-Host ""
    Start-Sleep -Milliseconds 300
}

# XSS Tests (Bonus)
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " XSS Detection Tests (Bonus)" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

$xssTests = @(
    @{
        name = "Script tag injection"
        email = "<script>alert('XSS')</script>"
        password = "test"
    },
    @{
        name = "Event handler injection"
        email = "<img src=x onerror=alert(1)>"
        password = "test"
    },
    @{
        name = "JavaScript protocol"
        email = "javascript:alert(document.cookie)"
        password = "test"
    }
)

foreach ($test in $xssTests) {
    Write-Host "Testing: $($test.name)" -ForegroundColor Yellow
    Write-Host "  Payload: $($test.email)" -ForegroundColor Gray
    
    $body = "email=$([System.Web.HttpUtility]::UrlEncode($test.email))&password=$([System.Web.HttpUtility]::UrlEncode($test.password))"
    
    try {
        $response = Invoke-WebRequest `
            -Uri "$BaseUrl/login" `
            -Method POST `
            -Body $body `
            -ContentType "application/x-www-form-urlencoded" `
            -UseBasicParsing `
            -ErrorAction Stop `
            -TimeoutSec 10
        
        Write-Host "  ❌ FAILED: Expected 403, got $($response.StatusCode)" -ForegroundColor Red
        $failed++
    } 
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        
        if ($statusCode -eq 403) {
            Write-Host "  ✅ PASSED: Blocked with 403 Forbidden" -ForegroundColor Green
            $passed++
        }
        else {
            Write-Host "  ⚠️  UNEXPECTED: Got status code $statusCode" -ForegroundColor Yellow
            $errors++
        }
    }
    
    Write-Host ""
    Start-Sleep -Milliseconds 300
}

# Summary
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Test Summary" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total Tests: $($passed + $failed + $errors)" -ForegroundColor White
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor Red
Write-Host "Errors: $errors" -ForegroundColor Yellow
Write-Host ""

if ($failed -eq 0 -and $errors -eq 0) {
    Write-Host "✅ All tests passed! Security monitoring is working correctly." -ForegroundColor Green
}
elseif ($failed -gt 0) {
    Write-Host "⚠️  Some injections were NOT blocked. Review security configuration." -ForegroundColor Red
}
else {
    Write-Host "⚠️  Some tests encountered errors. Check application availability." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Next Steps" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Check Laravel logs:" -ForegroundColor White
Write-Host "   type storage\logs\laravel.log | findstr /i SQLI_DETECTED" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Check Wazuh dashboard:" -ForegroundColor White
Write-Host "   - Navigate to Security Events" -ForegroundColor Gray
Write-Host "   - Filter by level 12" -ForegroundColor Gray
Write-Host "   - Search for 'SQLI_DETECTED'" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Check AI Security Reports page:" -ForegroundColor White
Write-Host "   - Login as superadmin" -ForegroundColor Gray
Write-Host "   - Navigate to AI Security Reports" -ForegroundColor Gray
Write-Host "   - Verify alerts are displayed" -ForegroundColor Gray
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
