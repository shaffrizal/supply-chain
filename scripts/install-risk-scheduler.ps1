param(
    [string]$ProjectPath = 'C:\xampp\htdocs\supply-chain',
    [string]$PhpPath = 'C:\xampp\php\php.exe'
)

$ErrorActionPreference = 'Stop'
$resolvedProject = (Resolve-Path -LiteralPath $ProjectPath).Path
$resolvedPhp = (Resolve-Path -LiteralPath $PhpPath).Path
$artisanPath = Join-Path $resolvedProject 'artisan'

if (-not (Test-Path -LiteralPath $artisanPath)) {
    throw "Laravel artisan was not found at $artisanPath"
}

$action = New-ScheduledTaskAction `
    -Execute $resolvedPhp `
    -Argument "`"$artisanPath`" schedule:run" `
    -WorkingDirectory $resolvedProject
$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date).AddMinutes(1) `
    -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -MultipleInstances IgnoreNew

Register-ScheduledTask `
    -TaskName 'SupplyChain-Laravel-Scheduler' `
    -Description 'Runs Laravel scheduler every minute; risk:update executes daily at 01:00 Asia/Jakarta.' `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Force

Write-Host 'SupplyChain-Laravel-Scheduler installed successfully.' -ForegroundColor Green
Write-Host 'Verify with: php artisan schedule:list'
