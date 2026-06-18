@echo off
REM Direct SQL Injection Testing with cURL
REM Bypasses browser form validation

setlocal EnableDelayedExpansion

set BASE_URL=%1
if "%BASE_URL%"=="" set BASE_URL=http://localhost:8000

echo.
echo =============================================
echo  Direct SQL Injection Testing with cURL
echo  Bypassing Browser Form Validation
echo =============================================
echo.
echo Target URL: %BASE_URL%
echo.

REM Check if curl is available
curl --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: cURL is not installed or not in PATH
    echo.
    echo Please install cURL or use the PowerShell script instead:
    echo   .\test-direct-injection.ps1
    echo.
    pause
    exit /b 1
)

echo [Test 1] Classic OR-based SQL Injection
curl -X POST "%BASE_URL%/login" -d "email=admin' OR '1'='1" -d "password=test123" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 2] Comment-based SQL Injection
curl -X POST "%BASE_URL%/login" -d "email=admin'--" -d "password=anything" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 3] UNION SELECT Injection
curl -X POST "%BASE_URL%/login" -d "email=admin' UNION SELECT NULL,NULL,NULL--" -d "password=test" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 4] Boolean-based Blind SQLi
curl -X POST "%BASE_URL%/login" -d "email=admin' OR 1=1--" -d "password=test" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 5] DROP TABLE Attempt
curl -X POST "%BASE_URL%/login" -d "email=admin'; DROP TABLE users--" -d "password=test" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 6] XSS in Email Field
curl -X POST "%BASE_URL%/login" -d "email=^<script^>alert('XSS')^</script^>" -d "password=test" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 7] SQLi in Password Field
curl -X POST "%BASE_URL%/login" -d "email=admin@test.com" -d "password=' OR '1'='1" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo [Test 8] Command Injection
curl -X POST "%BASE_URL%/login" -d "email=admin; ls -la" -d "password=test" -w "\nStatus Code: %%{http_code}\n\n" -s -o nul

echo.
echo =============================================
echo  Tests Completed
echo =============================================
echo.
echo Expected Results:
echo   - 403 Forbidden = Attack blocked by middleware
echo   - 422 Unprocessable = Validation error
echo   - 419 Token Error = CSRF protection (expected)
echo.
echo Next Steps:
echo   1. Check Laravel logs:
echo      type storage\logs\laravel.log ^| findstr /i SQLI_DETECTED
echo.
echo   2. Check Wazuh Dashboard for security alerts
echo.
echo   3. Check AI Security Reports page
echo.
pause
