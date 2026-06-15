# Multi Panel Session Rules

## Context

This rule applies to PHP systems with multiple panels such as:

- `/student/`
- `/teacher/`
- `/vbs/`
- `/management/`

These systems often store the active panel in session:

```php
$_SESSION['panel'] = 'student';
```

and compare it against the current URL.

## Forbidden Access-Control Pattern

Do not destroy the session when the current route does not match the expected panel.

Forbidden:

```php
if (!empty($panel) && $panel !== $expected) {
    session_destroy();
    header("location: " . $path . "index.php?error=access");
    exit();
}
```

## Required Pattern

Redirect only:

```php
if (!empty($panel) && $panel !== $expected) {
    header("location: " . $path . "index.php?error=access");
    exit();
}
```

## Reason

Panel mismatch may occur because of:

- included files
- relative paths
- base href
- Ajax calls
- shared assets
- legacy routing
- unexpected URLs containing another panel path

A route mismatch is not the same as a logout request.

## Session Lifecycle Rule

Use `session_destroy()` only for:

- explicit logout
- account invalidation
- forced sign-out for a confirmed security reason

Do not use it for:

- authorization failure
- panel mismatch
- 403 redirects
- route mismatches
- missing permission redirects

## Debug Command

```bash
grep -R "session_destroy" /var/www/example/public -n
```

Every occurrence must be justified.

Logout files are acceptable. Access-control functions should generally not destroy sessions.
