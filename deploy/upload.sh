#!/bin/bash
# FTP batch upload script for Solid-SocialMedia
FTP_HOST="ftp://ftp.aiagentprojects.com"
FTP_USER="jason@aiagentprojects.com"
FTP_PASS='Ngcxlebp#000'
REMOTE_BASE="marketing.aiagentprojects.com/public_html/social"
LOCAL_BASE="."

upload_file() {
    local file="$1"
    local remote="$FTP_HOST/$REMOTE_BASE/$file"
    curl -s --ftp-create-dirs -T "$LOCAL_BASE/$file" "$remote" --user "$FTP_USER:$FTP_PASS" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "  OK: $file"
    else
        echo "  FAIL: $file"
    fi
}

echo "=== Uploading core framework ==="
for f in core/Controller.php core/Database.php core/Model.php core/Router.php; do
    upload_file "$f"
done

echo "=== Uploading config ==="
upload_file "config/routes.php"
# env.php already uploaded

echo "=== Uploading public entry + assets ==="
upload_file "public/index.php"
upload_file "public/css/app.css"
upload_file "public/js/app.js"
upload_file "public/favicon.ico"
upload_file "public/favicon-16.png"
upload_file "public/favicon-32.png"
upload_file "public/favicon-48.png"
upload_file "public/apple-touch-icon.png"

echo "=== Uploading .htaccess ==="
cat > /tmp/htaccess_social <<'HTEOF'
RewriteEngine On
RewriteBase /social/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
HTEOF
curl -s --ftp-create-dirs -T "/tmp/htaccess_social" "$FTP_HOST/$REMOTE_BASE/public/.htaccess" --user "$FTP_USER:$FTP_PASS"
echo "  OK: public/.htaccess"

echo "=== Uploading controllers ==="
for f in app/controllers/*.php; do
    upload_file "$f"
done

echo "=== Uploading models ==="
for f in app/models/*.php; do
    upload_file "$f"
done

echo "=== Uploading services ==="
for f in app/services/*.php; do
    upload_file "$f"
done

echo "=== Uploading views ==="
for dir in app/views/*/; do
    for f in "$dir"*.php; do
        [ -f "$f" ] && upload_file "$f"
    done
done

echo "=== Uploading database migrations ==="
for f in database/migrations/*.sql; do
    upload_file "$f"
done

echo "=== Uploading cron ==="
upload_file "cron/run_scheduled_posts.php"

echo "=== Uploading doc images ==="
for f in public/uploads/docs/*.jpg; do
    [ -f "$f" ] && upload_file "$f"
done

echo "=== Uploading AI-generated images ==="
for f in public/uploads/*.jpg; do
    [ -f "$f" ] && upload_file "$f"
done

echo "=== Uploading misc files ==="
for f in public/uploads/*.png; do
    [ -f "$f" ] && upload_file "$f"
done
upload_file "CLAUDE.md"
upload_file "PROJECT-CONTEXT.md"

echo ""
echo "=== UPLOAD COMPLETE ==="
echo "Visit: https://marketing.aiagentprojects.com/social/public/login"
