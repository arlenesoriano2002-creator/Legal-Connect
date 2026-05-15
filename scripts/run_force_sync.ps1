# PowerShell wrapper to force IMAP sync and append output to log
# Usage: Run this in Task Scheduler or manually

$phpPath = 'C:\xampp\php\php.exe' # <-- Replace with your PHP executable path
$script = 'd:\xampp\htdocs\LEGAL CONNECT\scripts\force_sync_imap.php'
$log = 'd:\xampp\htdocs\LEGAL CONNECT\storage\logs\imap_force.log'

# Ensure log directory exists
$logDir = Split-Path $log -Parent
if (!(Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }

$time = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
"`n=== Run at: $time ===`n" | Out-File -FilePath $log -Encoding utf8 -Append

# Execute PHP script and capture output
$cmd = "`"$phpPath`" `"$script`""
try {
    $proc = Start-Process -FilePath $phpPath -ArgumentList $script -NoNewWindow -RedirectStandardOutput $log -RedirectStandardError $log -PassThru -Wait
    if ($proc.ExitCode -eq 0) { "Success: ExitCode=0" | Out-File -FilePath $log -Encoding utf8 -Append }
    else { "Warning: ExitCode=$($proc.ExitCode)" | Out-File -FilePath $log -Encoding utf8 -Append }
} catch {
    "Error running sync: $($_.Exception.Message)" | Out-File -FilePath $log -Encoding utf8 -Append
}
