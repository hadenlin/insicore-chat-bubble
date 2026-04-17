# build-zip.ps1 - WordPress plugin zip builder
# Usage: .\build-zip.ps1

[System.Reflection.Assembly]::LoadWithPartialName('System.IO.Compression') | Out-Null

$pluginDir  = Split-Path -Parent $MyInvocation.MyCommand.Path
$pluginName = Split-Path -Leaf $pluginDir
$destZip    = Join-Path (Split-Path -Parent $pluginDir) "$pluginName.zip"

$excludeDirs = 'git', '.claude', 'node_modules'

$excludeFiles = '.gitignore', '.gitattributes', 'build-zip.ps1', 'phpcs.xml', 'phpunit.xml', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json'

$excludeSuffixes = 'premium-contact-bubble.pot'

if (Test-Path $destZip) { Remove-Item $destZip }

$zipStream = New-Object System.IO.FileStream($destZip, [System.IO.FileMode]::Create)
$archive   = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create, $false)

$added = 0
Get-ChildItem -Path $pluginDir -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($pluginDir.Length + 1)

    foreach ($d in $excludeDirs)    { if ($rel -match "^[.]?$d[/\\]") { return } }
    foreach ($f in $excludeFiles)   { if ($rel -eq $f)                 { return } }
    foreach ($s in $excludeSuffixes){ if ($rel.EndsWith($s))           { return } }

    $entryName   = "$pluginName/" + $rel.Replace('\', '/')
    $entry       = $archive.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
    $entryStream = $entry.Open()
    $bytes       = [System.IO.File]::ReadAllBytes($_.FullName)
    $entryStream.Write($bytes, 0, $bytes.Length)
    $entryStream.Close()

    Write-Host "  + $entryName"
    $added++
}

$archive.Dispose()
$zipStream.Close()

$sizekb = [math]::Round((Get-Item $destZip).Length / 1KB)
Write-Host ""
Write-Host "Done! $added files, $sizekb KB -> $destZip"
