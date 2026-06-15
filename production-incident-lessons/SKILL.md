# Production Incident Lessons Learned

## Purpose

Use this skill when debugging production incidents in legacy PHP, Nginx, multi-panel web applications.

This skill is especially relevant when any of the following symptoms are reported:

- Login succeeds, dashboard opens, but refresh returns to login page.
- User is randomly logged out.
- PHPSESSID changes unexpectedly.
- Session file becomes empty or new session files are created unexpectedly.
- A multi-panel structure exists, such as `student`, `teacher`, `vbs`, `management`.
- Nginx is used in front of PHP-FPM.
- Static asset 404s appear before authentication/session failures.

## Critical Rules

### Rule 1 — Never use PHP as a generic 404 handler

Forbidden:

```nginx
error_page 404 /index.php;
```

Required:

```nginx
error_page 404 /404.html;
```

or static asset-specific handling:

```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|map)$ {
    try_files $uri =404;
}
```

Reason:

A missing JS/CSS/MAP file must not execute PHP. If a 404 request is routed to `index.php`, root PHP code and included session files may execute and corrupt the active user session.

Typical failure chain:

```text
Missing static asset
↓
404
↓
error_page 404 /index.php
↓
root index.php executes
↓
session side effects
↓
PHPSESSID changes or session becomes empty
↓
refresh returns to login page
```

### Rule 2 — Never use `session_destroy()` for access control

Forbidden:

```php
if ($panel !== $expected) {
    session_destroy();
    header("location: ...");
    exit();
}
```

Required:

```php
if ($panel !== $expected) {
    header("location: ...");
    exit();
}
```

Reason:

Access control and session lifecycle are separate responsibilities. A wrong panel, wrong route, include path, Ajax call, or URL mismatch must not destroy the user session.

Only destroy a session on explicit logout, account invalidation, or security-critical forced sign-out.

## Mandatory Debug Order

Before editing code, inspect in this order:

1. Which session keys are set at login?
2. Which session keys are required by the protected pages?
3. Does `session_id()` change after refresh?
4. Does `$_COOKIE['PHPSESSID']` change after refresh?
5. Does `$_SESSION` become empty?
6. Where are `session_destroy()`, `session_regenerate_id()`, `unset($_SESSION...)`, and `setcookie(session_name(...))` used?
7. Are there static asset 404s in Nginx access logs?
8. Does Nginx contain `error_page 404 /index.php;`?
9. Does root `index.php` include a session file?
10. Does panel mismatch logic destroy the session?

## Useful Commands

Find dangerous session calls:

```bash
grep -R "session_destroy\|session_regenerate_id\|unset(\$_SESSION\|\$_SESSION = \[\]\|setcookie(session_name" /var/www/PROJECT/public -n
```

Find Nginx 404 handler:

```bash
grep -R "error_page 404" /etc/nginx
```

Find recent 404s:

```bash
grep " 404 " /var/log/nginx/access.log | tail -100
```

Test common missing static resources:

```bash
curl -o /dev/null -w "%{http_code}\n" https://example.com/assets/js/calendar.js
curl -o /dev/null -w "%{http_code}\n" https://example.com/vbs/chat/index.php
```

Check PHP syntax after changes:

```bash
php -l /var/www/PROJECT/public/config/connection.php
```

Check and reload Nginx:

```bash
nginx -t && systemctl reload nginx
```

## Expected Output When Using This Skill

When this skill is triggered, provide:

1. Symptoms observed
2. Session keys set at login
3. Session keys required by protected pages
4. Evidence of session ID change or session data loss
5. Nginx 404 findings
6. Dangerous session lifecycle calls found
7. Root cause hypothesis
8. Minimal-risk fix plan
9. Files/configs to change
10. Rollback plan

Never jump directly to rewriting login logic unless the checklist proves login is the root cause.
