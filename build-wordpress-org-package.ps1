param(
    [string] $Version = '1.3.0'
)

$ErrorActionPreference = 'Stop'

$sourceRoot = (Resolve-Path -LiteralPath $PSScriptRoot).Path
$releaseRoot = Join-Path (Split-Path $sourceRoot -Parent) 'release'
$stageRoot = Join-Path $releaseRoot 'stage'
$stagePlugin = Join-Path $stageRoot 'apex-addons-for-elementor'
$zipPath = Join-Path $releaseRoot ("apex-addons-for-elementor-{0}.zip" -f $Version)

if ( -not $stageRoot.StartsWith($releaseRoot, [System.StringComparison]::OrdinalIgnoreCase) ) {
    throw 'Refusing to use a staging directory outside the release directory.'
}

if ( Test-Path -LiteralPath $stageRoot ) {
    Remove-Item -LiteralPath $stageRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $stagePlugin -Force | Out-Null

$excludedFiles = @(
    'assets\css\icomoon_brands.css',
    'assets\js\icons\brands.json',
    'build-wordpress-org-package.ps1'
)

Get-ChildItem -LiteralPath $sourceRoot -Recurse -File | ForEach-Object {
    $relative = $_.FullName.Substring($sourceRoot.Length + 1)
    $extension = $_.Extension.ToLowerInvariant()

    if ( $_.Name.StartsWith('.', [System.StringComparison]::Ordinal) ) {
        return
    }

    if ( $relative.StartsWith('docs\', [System.StringComparison]::OrdinalIgnoreCase) ) {
        return
    }

    if ( $excludedFiles -contains $relative ) {
        return
    }

    if ( $relative.StartsWith('assets\fonts\icomoon_brands.', [System.StringComparison]::OrdinalIgnoreCase) ) {
        return
    }

    if ( $relative.StartsWith('assets\fonts\', [System.StringComparison]::OrdinalIgnoreCase) -and
        $extension -in @('.eot', '.otf', '.svg', '.ttf') ) {
        return
    }

    $destination = Join-Path $stagePlugin $relative
    $destinationDirectory = Split-Path $destination -Parent
    if ( -not (Test-Path -LiteralPath $destinationDirectory) ) {
        New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
    }
    Copy-Item -LiteralPath $_.FullName -Destination $destination
}

if ( Test-Path -LiteralPath $zipPath ) {
    Remove-Item -LiteralPath $zipPath -Force
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::Open(
    $zipPath,
    [System.IO.Compression.ZipArchiveMode]::Create
)

try {
    Get-ChildItem -LiteralPath $stagePlugin -Recurse -File | ForEach-Object {
        $entryRelative = $_.FullName.Substring($stageRoot.Length + 1).Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $archive,
            $_.FullName,
            $entryRelative,
            [System.IO.Compression.CompressionLevel]::Optimal
        ) | Out-Null
    }
} finally {
    $archive.Dispose()
}

$zip = Get-Item -LiteralPath $zipPath
if ( $zip.Length -ge 10MB ) {
    throw ("Package is {0:N2} MB; WordPress.org requires a ZIP under 10 MB." -f ($zip.Length / 1MB))
}

[pscustomobject]@{
    Package = $zip.FullName
    SizeMB = [math]::Round($zip.Length / 1MB, 2)
    Files = (Get-ChildItem -LiteralPath $stagePlugin -Recurse -File).Count
}
