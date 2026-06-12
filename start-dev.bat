@echo off
setlocal enabledelayedexpansion
set PHPRC=%cd%
php artisan serve --host=127.0.0.1 --port=8000
