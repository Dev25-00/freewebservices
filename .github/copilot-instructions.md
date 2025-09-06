## Quick orientation for AI coding agents

This project is a small PHP-based collection of micro-services (UI pages under `services/`, server endpoints under `api/`) served from a WAMP/Apache/PHP stack. The goal of these notes is to give you concrete, actionable patterns so you can be immediately productive editing, debugging, or extending the code.

1) Big picture
- Public pages and service UIs: top-level PHP files and `services/*.php` (examples: `services/file-converter.php`, `services/hashtag-generator.php`).
- API endpoints: `api/*.php` (examples: `api/convert-file.php`, `api/generate-hashtags.php`, `api/download.php`). Frontend JS calls these endpoints.
- Configuration: global bootstrap in `config.php`; service-specific configuration in `services/configs/*.php`.
- Helpers & includes: `includes/` contains shared UI fragments and the `service-template.php` used by many service pages.
- Storage and runtime state: `storage/` (subfolders: `temp_conversions`, `temp_images`, `quarantine`, `rate_limits`, `encryption_keys`, `cache`, `backups`). Many classes reference these exact paths.

2) Common patterns and conventions (be strict — follow existing style)
- Every `api/*.php` file starts with `require_once '../config.php'`. Frontend pages usually `require_once 'config.php'` (relative to root). Keep that pattern when adding endpoints or pages.
- Service pages include a small `services/forms/*.php` snippet for the UI. Example: `services/file-converter.php` uses `services/forms/file-converter-form.php` and `assets/js/file-converter-script.js` to implement client behavior.
- Service-config pattern: `services/<service>.php` will `require_once __DIR__ . '/configs/<service>-config.php'`. Add a config file there when introducing a new service.
- Storage usage: use the existing storage folders (do not create new arbitrary temp folders). See `converters/FFmpegConverter.php` and `converters/ImageMagickConverter.php` for exact temp dir usage.

3) Example — file converter flow (concrete, copyable)
- UI: `services/file-converter.php` -> includes `services/forms/file-converter-form.php` and `assets/js/file-converter-script.js`.
- Client -> Server: `assets/js/file-converter-script.js` posts to `api/convert-file.php`.
- Server: `api/convert-file.php` stores uploads under `storage/temp_conversions/` and returns a path used by `api/download.php` which validates with a realpath-allowedPath check before streaming.

4) Security and runtime gotchas to check first
- DB/env: `config.php` expects DB constants (DB_HOST, DB_USER, DB_PASS, DB_NAME) — logs show an undefined DB_HOST in `logs/error.log`. Ensure environment or config constants are present before running.
- Storage should be protected by server config (existing `.htaccess_backup` and `config/ssl-config.conf` contain examples). Do not expose `storage/` or `uploads/` publicly.
- Middleware & protections: CSRF protection and rate limiting are implemented under `security/` (`CSRFProtection.php`, `RateLimiter.php`). APIs frequently call `api/check-credit.php` to gate usage.

5) Developer workflows & useful commands (local WAMP/Apache)
- Local server: host with WAMP and open `http://localhost/miniservices/` (or configure a virtual host pointing to the project root). Many markdown docs reference these example URLs.
- Quick smoke tests: the project includes example pages like `test-hashtag-api.php` and service UI pages under `services/` for manual testing.
- Node deps: `package.json` contains `@anthropic-ai/sdk` (likely used by a node helper or future scripts). `npm install` will populate `node_modules/` if you need to run any JS tooling.

6) Files & classes to check when changing behavior
- `config.php` — global constants and Database bootstrap.
- `includes/header-bootstrap.php` / `includes/footer-bootstrap.php` — site chrome and `BASE_URL` usage.
- `services/configs/*` — configuration arrays for each service (scripts, CSS, limits).
- `api/convert-file.php`, `api/download.php` — conversion and download logic (careful with path validation).
- `security/*` — FileValidator, RateLimiter, CacheManager, CSRFProtection — these encapsulate security policies the rest of the app assumes.

7) Editing rules for PRs (concrete expectations)
- Preserve the existing include pattern (`require_once 'config.php'` or `require_once __DIR__ . '/configs/...'`).
- Use existing storage folders and the same realpath-based validation for any user-supplied paths (mirror `api/download.php`).
- When adding JS, keep names under `assets/js/services/` and register them in the matching `services/configs/<service>-config.php` as other services do.

8) Where to look for operational notes and examples
- `CONVERTISSEUR-FIXED.md` — step-by-step notes and curl examples for the converter.
- `RESUME-COMPLET.md` — a map of files and example URLs for manual tests.
- `logs/error.log` — helpful for runtime errors (missing constants, stack traces).

If anything above is unclear or you want more examples (unit-test harness, local virtual-host setup, or a template for adding a new service), tell me which section to expand and I will iterate.
