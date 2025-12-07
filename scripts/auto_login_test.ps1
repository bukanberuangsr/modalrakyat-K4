$ErrorActionPreference = 'Stop'
$base = 'http://localhost:8000'
Write-Output "Base URL: $base"

# Create a session to hold cookies
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1) GET root to obtain initial cookies (XSRF-TOKEN)
Write-Output "GET / to obtain cookies"
$r = Invoke-WebRequest -Uri $base -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue

# Try to read XSRF-TOKEN cookie
$xsrfCookie = $session.Cookies.GetCookies($base) | Where-Object { $_.Name -eq 'XSRF-TOKEN' } | Select-Object -ExpandProperty Value -ErrorAction SilentlyContinue
if ($xsrfCookie) {
    $csrf = [System.Uri]::UnescapeDataString($xsrfCookie)
} else {
    $csrf = ''
}
Write-Output "XSRF-TOKEN (decoded): $csrf"

# 2) POST /login using the session
Write-Output "POST /login (email=admin@modalrakyat.com, password=admin123)"
$headers = @{'X-CSRF-TOKEN' = $csrf }
$body = @{ email = 'admin@modalrakyat.com'; password = 'admin123' }
$login = Invoke-WebRequest -Uri ($base + '/login') -Method Post -Body $body -Headers $headers -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue

if ($login) {
    Write-Output "Login HTTP Status: $($login.StatusCode)"
    $login.Headers | Out-File -FilePath login_headers.txt -Encoding utf8
    Write-Output "Saved login headers to login_headers.txt"
} else {
    Write-Output "Login request did not return a response object."
}

Write-Output "Cookies after login:"
$session.Cookies.GetCookies($base) | Format-Table -AutoSize | Out-File cookies_after_login.txt -Encoding utf8
Get-Content cookies_after_login.txt | Write-Output

# 3) GET /dashboard/admin using the same session
Write-Output "GET /dashboard/admin using same session"
$dash = Invoke-WebRequest -Uri ($base + '/dashboard/admin') -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue
if ($dash) {
    Write-Output "Dashboard HTTP Status: $($dash.StatusCode)"
    $dash.Content | Out-File -FilePath dashboard_after_login.html -Encoding utf8
    Write-Output "Saved dashboard HTML to dashboard_after_login.html"
} else {
    Write-Output "Dashboard request did not return a response object."
}

# Print cookies and session info one more time
Write-Output "Final cookies:"
$session.Cookies.GetCookies($base) | Format-Table -AutoSize | Out-String | Write-Output

Write-Output "Done. Files created: login_headers.txt, cookies_after_login.txt, dashboard_after_login.html"
