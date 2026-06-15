# Production Case: Evo Eğitim Student Panel Login Loop

## Incident Summary

Student panel login succeeded and dashboard opened. On refresh or menu click, the user was redirected to `/student/index.php`.

## Symptoms

- Login worked.
- `/student/dashboard.php` opened.
- Refresh returned to login.
- Menu click returned to login.
- Session ID changed.
- Empty session file was observed.

## Initial Misleading Leads

The issue first looked like:

- wrong login code
- wrong cookie path
- missing `session_start()`
- wrong `$_SESSION['user_id']` key

Those were not the final root causes.

## Root Cause 1 — Nginx 404 Handler Executed PHP

Nginx config contained:

```nginx
error_page 404 /index.php;
```

Missing static files such as `.map` sourcemaps returned 404 and were internally routed to root `index.php`.

Examples from access log:

```text
/assets_panel/css/select2.min.css.map 404
/assets_panel/js/bootstrap.bundle.min.js.map 404
/assets_panel/js/quill.js.map 404
/assets_panel/css/dataTables.bs5.min.css.map 404
```

Because `/index.php` executed root PHP and session includes, the active student session was corrupted.

## Fix 1

Changed Nginx config:

```nginx
error_page 404 /404.html;
```

Created static 404 file:

```bash
echo '<h1>404</h1>' > /var/www/evoegitim/public/404.html
```

Validated and reloaded:

```bash
nginx -t
systemctl reload nginx
```

## Root Cause 2 — Panel Mismatch Destroyed Session

`config/connection.php` contained:

```php
if (!empty($panel) && $panel !== $expected) {
    session_destroy();
    header("location: " . $path . "index.php?error=access");
    exit();
}
```

This destroyed a valid user session when a route/panel mismatch occurred.

## Fix 2

Removed `session_destroy()`:

```php
if (!empty($panel) && $panel !== $expected) {
    header("location: " . $path . "index.php?error=access");
    exit();
}
```

PHP syntax validation:

```bash
php -l /var/www/evoegitim/public/config/connection.php
```

## Final Result

Student panel no longer logs out after refresh or menu click.

## Regression Risk

High if any future deployment restores:

```nginx
error_page 404 /index.php;
```

or reintroduces:

```php
session_destroy();
```

inside access-control or panel-mismatch logic.

## Required Deployment Files

- `config/connection.php`
- `404.html`
- `evoegitim.com.conf`

## Release Note

Student panel login loop fixed.

- Nginx 404 handler changed from `/index.php` to static `/404.html`.
- `session_destroy()` removed from panel-mismatch access-control logic.
- Static asset 404 requests no longer execute PHP and no longer corrupt active sessions.
