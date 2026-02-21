# Suggested Commands

## Setup

- `composer run setup`

## Local Development

- `composer run dev` (server + queue + logs + vite concurrently)
- `npm run dev` (frontend only)
- `php artisan serve` (backend only)

## Build

- `npm run build`
- `npm run build:ssr`

## Testing

- `php artisan test --compact`
- `php artisan test --compact --filter=<TestName>`
- `php artisan test --compact tests/Feature/<File>.php`

## Formatting & Linting

- `vendor/bin/pint --dirty --format agent` (project-required before finalize)
- `composer run lint` (pint --parallel)
- `npm run lint` (eslint --fix)
- `npm run format`
- `npm run format:check`

## Code Generation

- `php artisan make:table <Name>`
- `php artisan make:table <Name> --model=<Model> --resource=<Resource>`
- `php artisan make:action <Name>`

## Useful System Commands (Linux)

- `git status`, `git diff`, `git add -p`, `git commit`
- `ls -la`, `cd <dir>`, `pwd`
- `rg <pattern>`, `rg --files`, `find . -name '<pattern>'`
