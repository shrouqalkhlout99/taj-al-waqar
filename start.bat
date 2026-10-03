@echo off
chcp 65001 >nul
cd /d "%~dp0"

set "PHP=php"
if exist "%USERPROFILE%\tools\php\php.exe" set "PHP=%USERPROFILE%\tools\php\php.exe"

if not exist "database\database.sqlite" type nul > "database\database.sqlite"

echo جاري تجهيز النظام...
if not exist "public\storage" "%PHP%" artisan storage:link
"%PHP%" artisan migrate --force
"%PHP%" artisan db:seed --force

echo.
echo افتحي المتصفح على: http://127.0.0.1:8000
start "" "http://127.0.0.1:8000"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
pause
