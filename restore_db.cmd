@echo off
echo Restoring tyro_project from opspilot_db_dump.sql...
"D:\xampp\mysql\bin\mysql.exe" -u root tyro_project < "%~dp0opspilot_db_dump.sql"
echo.
echo Resetting admin password to 'password'...
"D:\xampp\php\php.exe" "%~dp0artisan" tinker --execute="\App\Models\User::where('email','admin@tyro.project')->update(['password'=>bcrypt('password')]);"
echo.
echo Clearing stale sessions...
if exist "%~dp0storage\framework\sessions\*" del /q "%~dp0storage\framework\sessions\*"
echo Done! Login with admin@tyro.project / password
pause
