@echo off
setlocal

cd /d "%~dp0"

echo [1/4] Checking Docker availability...
where docker >nul 2>&1
if errorlevel 1 (
  echo Docker is not installed or not in PATH.
  exit /b 1
)

docker info >nul 2>&1
if errorlevel 1 (
  echo Docker Desktop is not running. Please start Docker Desktop and retry.
  exit /b 1
)

echo [2/4] Starting application containers...
docker-compose up --build -d
if errorlevel 1 (
  echo Failed to start containers.
  exit /b 1
)

echo [3/4] App should be available at:
echo     http://localhost:8080/Web_Application/src/pages/index.php

echo [4/4] Opening app in browser...
start "" "http://localhost:8080/Web_Application/src/pages/index.php"

echo.
echo Optional temporary public demo URL:
echo   cloudflared tunnel --url http://localhost:8080
echo   OR
echo   ngrok http 8080

echo.
echo Use demo-stop.bat to stop containers after your demo.

endlocal
