@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../felixfbecker/language-server/bin/php-language-server.php
php "%BIN_TARGET%" %*
