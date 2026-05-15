Direct fetch scheduled task setup

1) Edit `scripts/run_force_sync.ps1` and set `$phpPath` to your PHP executable (e.g., C:\xampp\php\php.exe).
2) Option A - Create using the included command file (runs schtasks):
   - Open an elevated Command Prompt (Run as Administrator)
   - Run: d:\xampp\htdocs\LEGAL CONNECT\scripts\create_task_example.cmd

   This creates a scheduled task named "LegalConnect_IMAP_Sync" that runs every minute and calls `run_force_sync.ps1`.

3) Option B - Create manually with schtasks (replace PHP path if needed):
   schtasks /Create /SC MINUTE /MO 1 /TN "LegalConnect_IMAP_Sync" /TR "powershell -NoProfile -ExecutionPolicy Bypass -File \"d:\xampp\htdocs\LEGAL CONNECT\scripts\run_force_sync.ps1\"" /F /RL HIGHEST

4) Option C - PowerShell registration (elevated PowerShell):
   $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -ExecutionPolicy Bypass -File "d:\xampp\htdocs\LEGAL CONNECT\scripts\run_force_sync.ps1"'
   $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration ([TimeSpan]::MaxValue)
   Register-ScheduledTask -TaskName 'LegalConnect_IMAP_Sync' -Action $action -Trigger $trigger -RunLevel Highest -Force

5) Verify the task runs:
   - Check scheduled task list: schtasks /Query /TN "LegalConnect_IMAP_Sync"
   - Check recent log output: d:\xampp\htdocs\LEGAL CONNECT\storage\logs\imap_force.log

Notes
- The wrapper logs output to storage/logs/imap_force.log. Ensure the web server user has write permission to the storage folder.
- If you prefer calling PHP directly, you can change the wrapper to call php.exe with the force_sync_imap.php script.
- The task will run every minute; adjust frequency by changing /MO 1 to /MO 5 for every 5 minutes.
