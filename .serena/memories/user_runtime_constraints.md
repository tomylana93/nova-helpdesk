# User Runtime Constraints

## Active Constraint

The user is already running `composer run dev` in another terminal.

## Do Not Run

- `npm run dev`
- `npm run build`
- `php artisan wayfinder:generate` (or any `wayfinder:generate` command)

## Reason

Avoid duplicate dev/build/generation processes and avoid interfering with the user's active terminal workflow.

## Enforcement

Treat this as an active operational constraint for subsequent tasks unless the user explicitly revokes it.
