@echo off
setlocal

cd /d "%~dp0"

if exist "C:\Program Files\Docker\Docker\resources\bin\docker.exe" (
  set "PATH=C:\Program Files\Docker\Docker\resources\bin;%PATH%"
)

if exist "C:\ProgramData\DockerDesktop\version-bin\docker.exe" (
  set "PATH=C:\ProgramData\DockerDesktop\version-bin;%PATH%"
)

echo Stopping Parking Finder demo containers...
docker compose down

if errorlevel 1 (
  echo Failed to stop containers.
  exit /b 1
)

echo Demo containers stopped.

endlocal
