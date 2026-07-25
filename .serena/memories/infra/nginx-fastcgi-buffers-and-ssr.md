# Prod 502 on /tickets — nginx FastCGI header buffer + SSR disabled

## Symptom (2026-07-25)
Full page reload of `/tickets` in production returned `502 Bad Gateway (nginx)`.
SPA navigation was fine. nginx error log:
`upstream sent too big header while reading response header from upstream ... fastcgi://.../php8.5-fpm.sock`

## Root cause
NOT a PHP crash. PHP-FPM rendered a valid response; nginx rejected it because the
response **header block exceeded nginx's default `fastcgi_buffer_size` (4 KB, one page)**.
`.deploy/nginx/helpdesk.sinergilogistik.com` set no `fastcgi_buffer*` directives.
`/tickets` is the heaviest authenticated landing page, so its headers (session +
`XSRF-TOKEN` cookies + cache/vary) tipped over 4 KB. Sessions are `database` driver,
so cookies are small — this is a buffer-size limit, not a runaway/custom header
(app sets no CSP/custom response headers).

## Fix
- `.deploy/nginx/helpdesk.sinergilogistik.com`, inside `location ~ ^/index\.php`:
  `fastcgi_buffer_size 32k; fastcgi_buffers 16 16k; fastcgi_busy_buffers_size 64k;`
- Apply on VPS: edit `/etc/nginx/sites-available/helpdesk.sinergilogistik.com`
  (or re-copy from `.deploy/nginx/`), `sudo nginx -t && sudo systemctl reload nginx`.
  No redeploy required to unblock; repo edit keeps it across future deploys.

## Separate cleanup: Inertia SSR disabled
`config/inertia.php` had `ssr.enabled => true` while prod builds client assets only
(`pnpm run build`, not `build:ssr`) and runs NO SSR process (supervisor only runs
reverb + queue; nothing on 127.0.0.1:13714 from deploy). Inertia v3 `HttpGateway`
falls back gracefully when no bundle exists, so SSR was NOT the 502 cause, but the
flag was a latent misconfig — set `ssr.enabled => false`. Re-enable only with a real
`build:ssr` bundle + a running SSR server + supervisor program.

## Known follow-up (not today's bug)
php-fpm `pm.max_children = 5` is low; logs show it being reached under load. Raise
for concurrency; unrelated to the 502.
