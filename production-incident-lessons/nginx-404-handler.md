# Nginx 404 Handler Rules for PHP Applications

## Forbidden Pattern

Do not use:

```nginx
error_page 404 /index.php;
```

in PHP applications that use sessions.

## Why It Is Dangerous

When a static file is missing, the browser requests it and receives 404.

If Nginx internally routes that 404 to `/index.php`, PHP executes.

That PHP file may include:

- session start logic
- root site session logic
- cookie manipulation
- `session_regenerate_id()`
- access-control logic

This can corrupt an active panel session.

## Example Failure Chain

```text
/assets_panel/js/select2.min.js.map → 404
↓
Nginx error_page routes to /index.php
↓
root index.php executes
↓
inc/db/session.php executes
↓
PHPSESSID changes or session data is overwritten
↓
student dashboard refresh redirects to login
```

## Required Pattern

Use a static 404 page:

```nginx
error_page 404 /404.html;
```

Create the file:

```bash
echo '<h1>404</h1>' > /var/www/example/public/404.html
```

Validate and reload:

```bash
nginx -t && systemctl reload nginx
```

## Stronger Static Asset Guard

For static assets, PHP should never execute:

```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|map)$ {
    try_files $uri =404;
}
```

## Verification Commands

```bash
grep -R "error_page 404" /etc/nginx
```

Expected:

```text
error_page 404 /404.html;
```

Check recent 404s:

```bash
grep " 404 " /var/log/nginx/access.log | tail -100
```

404s are acceptable. The critical requirement is that they must not execute PHP.
