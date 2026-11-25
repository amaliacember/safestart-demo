# Security Headers and Locale Middleware

> 9 nodes · cohesion 0.36

## Key Concepts

- **SecurityHeaders.php** (6 connections) — `app/Http/Middleware/SecurityHeaders.php`
- **SetLocale.php** (5 connections) — `app/Http/Middleware/SetLocale.php`
- **.handle()** (4 connections) — `app/Http/Middleware/SecurityHeaders.php`
- **.handle()** (4 connections) — `app/Http/Middleware/SetLocale.php`
- **Closure** (4 connections)
- **Symfony\Component\HttpFoundation\Response** (4 connections)
- **SecurityHeaders** (3 connections) — `app/Http/Middleware/SecurityHeaders.php`
- **SetLocale** (3 connections) — `app/Http/Middleware/SetLocale.php`
- **Illuminate\Support\Facades\App** (3 connections)

## Relationships

- [App Service Provider and Rate Limit](App_Service_Provider_and_Rate_Limit.md) (4 shared connections)
- [Laravel Bootstrap](Laravel_Bootstrap.md) (2 shared connections)
- [Demo Preflight Command](Demo_Preflight_Command.md) (1 shared connections)
- [Security and Sharing Settings](Security_and_Sharing_Settings.md) (1 shared connections)
- [Session Log and Its Tests](Session_Log_and_Its_Tests.md) (1 shared connections)
- [Middleware Tests](Middleware_Tests.md) (1 shared connections)

## Source Files

- `app/Http/Middleware/SecurityHeaders.php`
- `app/Http/Middleware/SetLocale.php`

## Audit Trail

- EXTRACTED: 23 (100%)
- INFERRED: 0 (0%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*