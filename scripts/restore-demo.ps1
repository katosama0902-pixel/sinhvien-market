param(
    [Parameter(Mandatory = $true)]
    [string]$BackupPath,
    [Parameter(Mandatory = $true)]
    [string]$Database,
    [Parameter(Mandatory = $true)]
    [string]$ConfirmDatabase
)

$ErrorActionPreference = "Stop"
$allowedDatabase = "sinhvien_market_demo"
if ($Database -ne $allowedDatabase -or $ConfirmDatabase -ne "RESTORE $allowedDatabase") {
    throw "Từ chối restore. Chỉ cho phép gõ chính xác 'RESTORE $allowedDatabase'."
}
$resolvedBackup = (Resolve-Path -LiteralPath $BackupPath).Path
if ([System.IO.Path]::GetExtension($resolvedBackup) -ne ".sql") {
    throw "Chỉ chấp nhận tệp .sql."
}

$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location $projectRoot
try {
    Write-Warning "Restore sẽ ghi đè nội dung database demo $allowedDatabase. Xác nhận đã kiểm tra backup."
    if ((Get-Item -LiteralPath $resolvedBackup).Length -lt 100) {
        throw "Tệp backup rỗng hoặc quá ngắn; không gửi lệnh restore."
    }
    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = "docker"
    $startInfo.WorkingDirectory = $projectRoot
    $startInfo.Arguments = '-f docker-compose.demo.yml exec -T db sh -c "exec mysql -uroot -p\"$MYSQL_ROOT_PASSWORD\" sinhvien_market_demo"'
    $startInfo.UseShellExecute = $false
    $startInfo.RedirectStandardInput = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    $exitCode = -1
    if (-not $process.Start()) {
        throw "Could not start Docker."
    }
    $input = [System.IO.File]::OpenRead($resolvedBackup)
    try {
        $input.CopyTo($process.StandardInput.BaseStream)
        $process.StandardInput.Close()
        $process.WaitForExit()
        $exitCode = $process.ExitCode
    }
    finally {
        $input.Dispose()
        $process.Dispose()
    }
    if ($exitCode -ne 0) {
        throw "Restore thất bại. Kiểm tra log MySQL demo."
    }
    Write-Output "Đã gửi restore tới database demo $allowedDatabase."
}
finally {
    Pop-Location
}
