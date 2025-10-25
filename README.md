# Asset Management Boilerplate

Reusable Laravel 12 + Inertia + Vue starter focused on admin back-office apps.

## Quick Start
1. cp .env.example .env
2. composer install && npm install
3. php artisan key:generate
4. php artisan migrate --seed
5. composer dev (or run php artisan serve, php artisan queue:listen, 
pm run dev separately)

## Handy Commands
- php artisan make:module Inventory scaffolds a clean-architecture module (controller/service/DTO/request/resource). Adjust stubs in stubs/module/.
- composer lint / 
pm run lint (see GitHub Actions pipelines .github/workflows/*.yml).
- php artisan test (Pest + PHPUnit). API smoke tests live under 	ests/Feature/Api.

## WebSockets / Laravel Reverb
- Set BROADCAST_DRIVER=reverb and populate the REVERB_* variables in .env.
- Run php artisan reverb:start alongside your dev processes to boot the websocket server.
- Front-end clients can connect through Laravel Echo pointing at REVERB_HOST/REVERB_PORT.

## API Clients
- Sanctum-protected endpoints are documented in Upcomming/Must/ApiIntegrationGuide.md with an OpenAPI spec under docs/api/openapi.yaml.

## Containers & Devcontainer
- Copy docker-compose.example.yml to docker-compose.yml to spin up pp + db services for local development.
- VS Code users can open the project in a container via .devcontainer/devcontainer.json.

## CI
GitHub Actions provide lint + test pipelines (.github/workflows/lint.yml and 	ests.yml). Mirror these steps in your own CI to keep code style and tests green.

## Documentation Hub
Planning and implementation notes live under Upcomming/Must/ (README.md, BoilerplateFeaturesPlan.md, etc.).
