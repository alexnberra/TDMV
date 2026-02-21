# Laravel Forge Deployment (TDMV)

## 1) App Environment
1. Create your Forge site and point it to this repository.
2. In Forge, ensure `FORGE_SITE_PATH` is available (Forge provides this), and it points to your site path:
   - Usually `/home/forge/<your-site-domain>`
   - If you override it, use either full path or a path relative to `/home/forge`
3. Copy values from `deploy/forge/.env.production.example` into Forge's environment editor.
4. Set all secrets (DB, AWS, Stripe, Mail) to real values.

## 2) First Deploy Script (one-time)
Use `scripts/forge/first-deploy.sh` for initial bootstrapping.

## 3) Ongoing Deploy Script
Use `scripts/forge/deploy.sh` for normal deployments.

## 4) Queue Worker
Enable a queue worker in Forge for `redis` queue connection.

## 5) Seed Mode
For production data:
- Ensure `.env` has `TDMV_SEED_MODE=live`
- If needed: `php artisan tdmv:mode live`

## 6) Smoke Test (optional)
Run after deploy:
- `php artisan tdmv:smoke`
