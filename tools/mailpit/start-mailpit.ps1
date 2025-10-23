#Requires -Version 5.1
param(
    [string]$Host = "127.0.0.1",
    [int]$SmtpPort = 1025,
    [int]$HttpPort = 8025,
    [string]$ExePath = ""
)

function Resolve-MailpitPath {
    param([string]$ExplicitPath)

    if ($ExplicitPath) {
        return $ExplicitPath
    }

    if ($env:MAILPIT_EXE -and (Test-Path $env:MAILPIT_EXE)) {
        return $env:MAILPIT_EXE
    }

    $default = "C:\\tools\\Mailpit\\mailpit.exe"
    if (Test-Path $default) {
        return $default
    }

    throw "Mailpit executable not found. Pass -ExePath, set MAILPIT_EXE, or install Mailpit to C:\tools\Mailpit\mailpit.exe."
}

$mailpitExe = Resolve-MailpitPath -ExplicitPath $ExePath

Write-Host "Starting Mailpit from $mailpitExe"
Write-Host "SMTP endpoint: $Host:$SmtpPort"
Write-Host "HTTP endpoint: $Host:$HttpPort"

$arguments = @(
    "--smtp", "$Host`:$SmtpPort",
    "--listen", "$Host`:$HttpPort",
    "--label", "assetmanagement"
)

& $mailpitExe @arguments
