@echo off
echo ============================================
echo  Biometric Agent - Windows Service Setup
echo ============================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator.
    echo Right-click and select "Run as administrator".
    pause
    exit /b 1
)

echo [1/4] Checking Python installation...
python --version >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Python is not installed or not in PATH.
    echo Please install Python 3.9+ from https://www.python.org/downloads/
    pause
    exit /b 1
)
echo OK: Python found.
echo.

echo [2/4] Installing Python dependencies...
cd /d "%~dp0"
pip install -r requirements.txt
if %errorLevel% neq 0 (
    echo ERROR: Failed to install dependencies.
    pause
    exit /b 1
)
echo OK: Dependencies installed.
echo.

echo [3/4] Creating startup script...
set SCRIPT_DIR=%~dp0
set STARTUP_FOLDER=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup

echo @echo off > "%STARTUP_FOLDER%\start_biometric_agent.bat"
echo cd /d "%SCRIPT_DIR%" >> "%STARTUP_FOLDER%\start_biometric_agent.bat"
echo python biometric_agent.py >> "%STARTUP_FOLDER%\start_biometric_agent.bat"
echo echo Biometric Agent started. >> "%STARTUP_FOLDER%\start_biometric_agent.bat"
echo OK: Startup script created in %STARTUP_FOLDER%
echo.

echo [4/4] Configuration reminder...
echo.
echo IMPORTANT: Before running, edit biometric_agent.py and update:
echo   - ZK_IP: The static IP of your ZKTeco uFace800 Plus device
echo   - YII2_BASE_URL: The URL of your Yii2 server
echo.
echo To start the agent now, run:
echo   python biometric_agent.py
echo.
echo The agent will also start automatically on Windows login.
echo.
echo ============================================
echo  Setup Complete!
echo ============================================
pause
