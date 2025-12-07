#!/bin/sh
# Debug auto test run inside container; writes output to project src/ so host can read
OUTDIR=/var/www/html/src/debug-output
mkdir -p $OUTDIR
BASE=http://127.0.0.1:8000
COOKIEJ=/tmp/cookies.txt
rm -f $COOKIEJ $OUTDIR/root_resp.txt $OUTDIR/csrf.txt $OUTDIR/login_headers.txt $OUTDIR/login_err.txt $OUTDIR/dashboard.html $OUTDIR/dashboard_err.txt $OUTDIR/cookies_after_login.txt

# 1) GET root to obtain cookies
curl -si -c $COOKIEJ $BASE/ > $OUTDIR/root_resp.txt
# extract XSRF-TOKEN from cookie jar
csrf=$(awk '/XSRF-TOKEN/ {print $7; exit}' $COOKIEJ || true)
# URL-decode (basic)
if [ -n "$csrf" ]; then
  csrf_decoded=$(printf '%b' "$(echo $csrf | sed 's/+/ /g; s/%/\\x/g')")
else
  csrf_decoded=""
fi
printf "%s" "$csrf_decoded" > $OUTDIR/csrf.txt

# Save cookies file in human readable form
if [ -f $COOKIEJ ]; then
  cat $COOKIEJ > $OUTDIR/cookies_after_login.txt
fi

# 2) POST /login using token header and cookie jar (JSON)
curl -si -b $COOKIEJ -c $COOKIEJ -H "X-CSRF-TOKEN: $csrf_decoded" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"email":"admin@modalrakyat.com","password":"admin123"}' $BASE/login > $OUTDIR/login_headers.txt 2> $OUTDIR/login_err.txt || true

# Save cookies after login
if [ -f $COOKIEJ ]; then
  cat $COOKIEJ > $OUTDIR/cookies_after_login.txt
fi

# 3) GET dashboard using same cookie jar
curl -si -b $COOKIEJ $BASE/dashboard/admin > $OUTDIR/dashboard.html 2> $OUTDIR/dashboard_err.txt || true

# List outputs
ls -la $OUTDIR
