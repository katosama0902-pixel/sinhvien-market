param(
    [Parameter(Mandatory = $true)]
    [string]$Database,
    [Parameter(Mandatory = $true)]
    [string]$ConfirmDatabase,
    [string]$OutputPath = ""
)

$ErrorActionPreference = "Stop"
$allowedDatabase = "sinhvien_market_demo"
if ($Database -ne $allowedDatabase -or $ConfirmDatabase -ne $allowedDatabase) {
    throw "Chỉ cho phép backup database demo cô lập. Hãy xác nhận chính xác tên $allowedDatabase."
}

$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location $projectRoot
try {
    $backupDir = Join-Path $projectRoot "storage\backups"
    New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
    if ([string]::IsNullOrWhiteSpace($OutputPath)) {
        $OutputPath = Join-Path $backupDir ("{0}-{1}.sql" -f $Database, (Get-Date -Format "yyyyMMdd-HHmmss"))
    }
    $resolved = [System.IO.Path]::GetFullPath($OutputPath)
    $backupRoot = [System.IO.Path]::GetFullPath($backupDir).TrimEnd('\') + '\'
    if (-not $resolved.StartsWith($backupRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Backup chỉ được lưu trong storage\backups."
    }
    if ([System.IO.Path]::GetExtension($resolved) -ne ".sql" -or (Test-Path -LiteralPath $resolved)) {
        throw "Đường dẫn phải là tệp .sql mới; không ghi đè tệp hiện có."
    }

    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = "docker"
    $startInfo.WorkingDirectory = $projectRoot
    $startInfo.Arguments = '-f docker-compose.demo.yml exec -T db sh -c "exec mysqldump -uroot -p\"$MYSQL_ROOT_PASSWORD\" --single-transaction --routines --triggers --databases sinhvien_market_demo"'
    $startInfo.UseShellExecute = $false
    $startInfo.RedirectStandardOutput = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    $stream = $null
    $exitCode = -1
    try {
        $stream = [System.IO.File]::Open($resolved, [System.IO.FileMode]::CreateNew, [System.IO.FileAccess]::Write)
        if (-not $process.Start()) {
            throw "Could not start Docker."
        }
        $process.StandardOutput.BaseStream.CopyTo($stream)
        $stream.Dispose()
        $stream = $null
        $process.WaitForExit()
        $exitCode = $process.ExitCode
    }
    finally {
        if ($null -ne $stream) { $stream.Dispose() }
        $process.Dispose()
    }
    if ($exitCode -ne 0 -or -not (Test-Path $resolved) -or (Get-Item $resolved).Length -lt 100) {
        Remove-Item -Force -ErrorAction SilentlyContinue $resolved
        throw "Backup thất bại. Kiểm tra Docker demo; không thay đổi database."
    }
    Write-Output "Đã tạo backup demo: $resolved"
}
finally {
    Pop-Location
}
