$root = 'C:\wamp\www\WOODIN_SITE-WEB'
$imagesDir = Join-Path $root 'assets\images'
$logDir = Join-Path $root 'logs'
$backupsDir = Join-Path $root 'backups'
New-Item -ItemType Directory -Force -Path $imagesDir | Out-Null
New-Item -ItemType Directory -Force -Path $logDir | Out-Null
New-Item -ItemType Directory -Force -Path $backupsDir | Out-Null
New-Item -ItemType File -Force -Path (Join-Path $logDir 'error.log') | Out-Null

Add-Type -AssemblyName System.Drawing

$files = @(
    @{ Name='og-default.jpg'; Title='Cameroun'; Subtitle="Pagne d'exception"; },
    @{ Name='pagne_succes.jpg'; Title="Collection Succès"; Subtitle='WOODIN CAMEROUN'; },
    @{ Name='pagne_maxior.jpg'; Title='Collection MaxiOr'; Subtitle='WOODIN CAMEROUN'; },
    @{ Name='pagne_royal.jpg'; Title='Collection Royal'; Subtitle='WOODIN CAMEROUN'; },
    @{ Name='pagne_ghana.jpg'; Title='Collection Ghana'; Subtitle='WOODIN CAMEROUN'; },
    @{ Name='haut_croise.jpg'; Title="Haut croisé"; Subtitle='WOODIN CAMEROUN'; }
)

foreach ($f in $files) {
    $img = New-Object System.Drawing.Bitmap(1200,1500)
    $g = [System.Drawing.Graphics]::FromImage($img)
    $g.Clear([System.Drawing.Color]::FromArgb(245,236,226))
    $pen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(93,68,42), 8)
    $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(93,68,42))
    $white = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255,255,255))
    $g.DrawRectangle($pen, 80, 80, 1040, 1340)
    $g.FillRectangle($brush, 180, 220, 840, 50)
    $fontTitle = New-Object System.Drawing.Font('Arial', 54, [System.Drawing.FontStyle]::Bold)
    $fontText = New-Object System.Drawing.Font('Arial', 30, [System.Drawing.FontStyle]::Regular)
    $g.DrawString('WOODIN', $fontTitle, $white, 220, 300)
    $g.DrawString($f.Title, $fontText, $brush, 220, 430)
    $g.DrawString($f.Subtitle, $fontText, $brush, 220, 980)
    $path = Join-Path $imagesDir $f.Name
    $img.Save($path, [System.Drawing.Imaging.ImageFormat]::Jpeg)
    $g.Dispose()
    $img.Dispose()
}

Write-Host 'ASSET_IMAGES_REPAIRED'
