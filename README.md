# Ceylon Coconut Mill — Containerized Web App with CI/CD

A PHP + MySQL business website (Thelambugammana Oil Mills / Ceylon Coconut Mill)
containerised with Docker and deployed automatically via GitHub Actions.

## Stack
- PHP 8.2 + Apache
- MySQL 8.0
- Docker & Docker Compose
- GitHub Actions CI/CD
- Docker Hub

## Local development

```bash
docker compose up --build
```

Visit http://localhost:8080

Default login (from `users_tables.sql`):
- Username: `Admin`  Password: `admin123`

## CI/CD Pipeline

On every push to `main`, GitHub Actions:
1. Checks out the code and lints the PHP files.
2. Builds the Docker image.
3. Pushes the image to Docker Hub.
4. Deploys the new container on the self-hosted runner (local machine).

## Required GitHub repo secrets
- `DOCKERHUB_USERNAME`
- `DOCKERHUB_TOKEN` (a Docker Hub access token, not your password)

## Project structure
- `index.html`, `about.html`, `products.html`, `contact.html` — public pages
- `login.php` — staff/admin login
- `admin.php` — user management (add/edit/search/delete/report)
- `orders.php` — order management
- `db_config.php` — reads DB connection settings from environment variables
- `users_tables.sql` — database schema + seed data, auto-loaded by MySQL on first start
- `Dockerfile` — builds the PHP/Apache image
- `docker-compose.yml` — runs the app + MySQL together
- `.github/workflows/main.yml` — the CI/CD pipeline
