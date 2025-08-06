# Running with Docker

This project is set up for multi-service development using Docker and Docker Compose. The stack includes a PHP 8.2 FPM backend, a Go backend for Simba Chatting, and a React frontend for Simba Chatting. Each service is containerized with its own Dockerfile and coordinated via `docker-compose`.

## Project-specific Requirements

- **PHP Service**: Uses `php:8.2-fpm-alpine` and Composer 2.7 for dependency management. Requires writable `storage` and `bootstrap/cache` directories.
- **Go Backend (Simba Chatting)**: Built with Go 1.24.2. Requires a `service-account.json` file for Google credentials (see below).
- **React Frontend (Simba Chatting)**: Built and served with Node.js 22.13.1. Uses `serve` to host the production build.

## Environment Variables

- The Go backend expects the environment variable:
  - `GOOGLE_APPLICATION_CREDENTIALS=/app/service-account.json`
    - The `service-account.json` file is required at runtime. For production, mount the real credentials file securely.
- The PHP and React services can optionally use `.env` files. Uncomment the `env_file` lines in `docker-compose.yml` if you want to pass environment variables from `.env` files.

## Build and Run Instructions

1. **Ensure you have Docker and Docker Compose installed.**
2. **Place your `service-account.json` in `./Simba-chatting/backend/`** (or mount it securely in production).
3. **Build and start all services:**
   ```sh
   docker compose up --build
   ```
   This will build and start:
   - PHP backend (`php-app`)
   - Go Simba Chatting backend (`go-simba-chat-backend`)
   - React Simba Chatting frontend (`js-simba-chatting`)

## Ports Exposed

- **PHP App**: Exposes port `8102` (internal, for PHP-FPM; not mapped to host by default)
- **Go Simba Chatting Backend**: Exposes port `8080` (internal; not mapped to host by default)
- **React Simba Chatting Frontend**: Exposes port `3000` (mapped to host, access via [http://localhost:3000](http://localhost:3000))

## Special Configuration

- If you need to use environment variables, provide `.env` files in the root and/or `Simba-chatting` directories and uncomment the `env_file` lines in `docker-compose.yml`.
- The Go backend requires a valid `service-account.json` for Google API access. For production, do **not** commit secrets; mount them securely.
- All services run as non-root users for improved security.

---

_This section was updated to reflect the current Docker-based setup for this project._
