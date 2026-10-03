# Lance les trois services en local avec le serveur intégré de PHP (Windows).
#   auth -> http://127.0.0.1:8001   api -> http://127.0.0.1:8002   web -> http://127.0.0.1:8003
# Prérequis : PHP 8.1+ (pdo_mysql, curl), bases importées, fichiers .env créés (voir README.md).
$racine = Split-Path -Parent $PSScriptRoot
foreach ($service in 'auth', 'api', 'web') {
    if (-not (Test-Path "$racine\$service\.env")) { Write-Error "Il manque $service\.env (copiez $service\.env.example)"; exit 1 }
}
$php = if ($env:PHP_BIN) { $env:PHP_BIN } else { 'php' }
$procs = @(
    Start-Process $php -ArgumentList '-S', '127.0.0.1:8001', '-t', "`"$racine\auth`"" -PassThru -WindowStyle Hidden
    Start-Process $php -ArgumentList '-S', '127.0.0.1:8002', '-t', "`"$racine\api`"" -PassThru -WindowStyle Hidden
    Start-Process $php -ArgumentList '-S', '127.0.0.1:8003', '-t', "`"$racine\web\src`"" -PassThru -WindowStyle Hidden
)
Write-Host "Application : http://127.0.0.1:8003  (appuyez sur Entrée pour tout arrêter)"
[void](Read-Host)
$procs | Stop-Process -Force
