# PHP Session Loss After Refresh

## Symptom

A user logs in successfully. The dashboard opens. When the page is refreshed or a menu item is clicked, the user is redirected back to the login page.

Common examples:

```text
/student/index.php login succeeds
/student/dashboard.php opens
refresh or menu click
/student/index.php appears again
```

## Do Not Assume Login Is Broken

This issue is often not caused by the login form itself.

Before changing login code, determine whether:

- The session ID changes.
- The session file becomes empty.
- Another request triggers root PHP code.
- Access-control logic destroys the session.
- Static asset 404s are routed to PHP.

## Diagnostic Snippet

Temporarily add this to the top of a protected page such as `student/dashboard.php`:

```php
<?php
ini_set('session.cookie_path', '/');
session_start();

echo '<pre>';
echo 'SESSION ID: ' . session_id() . PHP_EOL;
echo 'COOKIE PHPSESSID: ' . ($_COOKIE['PHPSESSID'] ?? 'NO COOKIE') . PHP_EOL;
print_r($_SESSION);
echo '</pre>';
exit;
```

Compare output immediately after login and after refresh.

## What To Look For

### Case A — Session ID changes

Likely causes:

- `session_regenerate_id(true)` is being called outside login.
- Another PHP entrypoint sends a new PHPSESSID cookie.
- Root `index.php` is being executed as a 404 handler.

### Case B — Session ID same, but session data empty

Likely causes:

- `session_destroy()` is being called.
- `$_SESSION = []` or `unset($_SESSION...)` is used incorrectly.
- A shared session file is being overwritten.

### Case C — Protected page redirects because `user_id` missing

Check:

- Login sets `$_SESSION['user_id']`.
- Protected page checks the same key.
- The session file still contains the expected data.

## Production Case Pattern

Real-world root causes observed:

1. Nginx had:

```nginx
error_page 404 /index.php;
```

This caused missing `.map` files to execute root PHP.

2. Panel mismatch logic contained:

```php
session_destroy();
```

This destroyed valid student sessions when a mismatched route was detected.

## Correct Fix

Nginx:

```nginx
error_page 404 /404.html;
```

Access-control logic:

```php
if (!empty($panel) && $panel !== $expected) {
    header("location: " . $path . "index.php?error=access");
    exit();
}
```

Do not destroy the session for routing or panel mismatch.
