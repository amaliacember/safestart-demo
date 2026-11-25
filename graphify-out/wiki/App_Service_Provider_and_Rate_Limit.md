# App Service Provider and Rate Limit

> 12 nodes · cohesion 0.20

## Key Concepts

- **Illuminate\Http\Request** (12 connections)
- **AppServiceProvider.php** (9 connections) — `app/Providers/AppServiceProvider.php`
- **AppServiceProvider** (5 connections) — `app/Providers/AppServiceProvider.php`
- **{closure#1}()** (2 connections) — `app/Providers/AppServiceProvider.php`
- **Illuminate\Support\ServiceProvider** (2 connections)
- **GET /trusted proxies/_proxy()** (2 connections) — `tests/Feature/MiddlewareTest.php`
- **.boot()** (1 connections) — `app/Providers/AppServiceProvider.php`
- **.register()** (1 connections) — `app/Providers/AppServiceProvider.php`
- **{closure#2}()** (1 connections) — `app/Providers/AppServiceProvider.php`
- **providers.php** (1 connections) — `bootstrap/providers.php`
- **Illuminate\Cache\RateLimiting\Limit** (1 connections)
- **Illuminate\Support\Facades\RateLimiter** (1 connections)

## Relationships

- [Security Headers and Locale Middleware](Security_Headers_and_Locale_Middleware.md) (4 shared connections)
- [Results Text and Start Page](Results_Text_and_Start_Page.md) (2 shared connections)
- [Results Download Controller](Results_Download_Controller.md) (2 shared connections)
- [Laravel Bootstrap](Laravel_Bootstrap.md) (2 shared connections)
- [Middleware Tests](Middleware_Tests.md) (2 shared connections)

## Source Files

- `app/Providers/AppServiceProvider.php`
- `bootstrap/providers.php`
- `tests/Feature/MiddlewareTest.php`

## Audit Trail

- EXTRACTED: 25 (100%)
- INFERRED: 0 (0%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*