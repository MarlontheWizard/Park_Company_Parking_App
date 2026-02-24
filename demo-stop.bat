@echo off
setlocal

cd /d "%~dp0"

echo Stopping Parking Finder demo containers...
docker-compose down

if errorlevel 1 (
  echo Failed to stop containers.
  exit /b 1
)

echo Demo containers stopped.

endlocal
