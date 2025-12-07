# Test login and clear cache
Set-Location 'd:\Projek Akhir KDP\modalrakyat-K4'

# Clear caches
Write-Host "Clearing caches..." -ForegroundColor Green
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear

Write-Host "Done! Try login at http://localhost:8000/login" -ForegroundColor Green
