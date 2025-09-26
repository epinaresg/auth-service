# Auth Service

Authentication microservice with Laravel + JWT.

## Requirements
- Docker and Docker Compose
- Composer

## Quick installation
```bash
cp src/.env.example src/.env
make up
make artisan cmd="key:generate"
make artisan cmd="jwt:secret"
make migrate
```

API base URL: `http://localhost:8080`

## Endpoints
- POST `/api/auth/login`   → Authenticate and get JWT
- POST `/api/auth/logout`  → Invalidate token
- POST `/api/auth/refresh` → Refresh token
- GET  `/api/auth/me`      → Get authenticated user

## Useful commands
```bash
make up      # Turn on services
make down    # Turn off services
make migrate # Run migrations
make test    # Run tests
```

## Notes
- Run `make artisan cmd="jwt:secret"` to generate `JWT_SECRET` before using the API.
- Token TTL and blacklist settings are available in `src/config/jwt.php`.
