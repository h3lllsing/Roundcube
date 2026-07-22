@echo off
REM ==============================================
REM IMAP IDLE Worker - Real-time New Mail Monitor
REM ==============================================
REM This script starts the IMAP IDLE worker in the background.
REM The worker connects to IMAP and uses IDLE command
REM for instant new mail detection.
REM
REM Usage:
REM   Double-click this file to start the worker
REM   OR run: start "" "C:\xampp\htdocs\roundcube\scripts\start-idle-worker.bat"
REM
REM To stop: Task Manager > find php.exe > End Task
REM ==============================================

echo [%date% %time%] Starting IMAP IDLE Worker...

"C:\xampp\php\php.exe" "C:\xampp\htdocs\roundcube\scripts\imap-idle-worker.php"

echo [%date% %time%] Worker exited.
pause
