@echo off
REM Replace C:\xampp\php\php.exe with your PHP path if different.
REM This creates a scheduled task that runs every 1 minute.

SET PHP_PATH=C:\xampp\php\php.exe
SET SCRIPT="d:\xampp\htdocs\LEGAL CONNECT\scripts\run_force_sync.ps1"
SET TASKNAME=LegalConnect_IMAP_Sync






pause
necho Task created. Verify with:
necho schtasks /Query /TN "%TASKNAME%"
necho Logs will be appended to d:\xampp\htdocs\LEGAL CONNECT\storage\logs\imap_force.logschtasks /Create /SC MINUTE /MO 1 /TN "%TASKNAME%" /TR "powershell -NoProfile -ExecutionPolicy Bypass -File %SCRIPT%" /F /RL HIGHESTREM Note: Adjust /RL and /RU if you need to run as specific user.nREM Use schtasks to create a new task that runs PowerShell to execute our wrapper.