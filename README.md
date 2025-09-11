# 🚀 Laravel Docker Boilerplate (Laravel 12 in `src/`)

This boilerplate sets up a complete development environment for **Laravel 12** using **Docker**.
It includes: PHP-FPM 8.2, Nginx, MySQL 8, Redis, and Mailhog for email testing.

---

## 📦 Requirements
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Mac/Windows/Linux)
- `make` installed (optional but recommended)

---

## ▶️ Getting Started

1. **Clone the repository**:
   ```bash
   git clone https://github.com/epinaresg/laravel-boilerplate
   cd laravel-boilerplate
   ```

2. **Build and start containers**:
   ```bash
   make up
   ```

3. **Create a new Laravel project inside `src/`**:
   ```bash
   rm src/.gitkeep
   docker compose run --rm app composer create-project laravel/laravel:^12 .
   ```

4. **Configure environment**:
   ```bash
   make artisan cmd="key:generate"
   ```

5. **Run migrations and seeders**:
   ```bash
   make migrate
   make seed
   ```

6. **Open in browser**:
   - Laravel: [http://localhost:8080](http://localhost:8080)
   - Mailhog: [http://localhost:8025](http://localhost:8025)

---

## 🔧 Useful Commands

- Start environment:
  ```bash
  make up
  ```
- Stop environment:
  ```bash
  make down
  ```
- Access app container shell:
  ```bash
  make shell
  ```
- Run Artisan command:
  ```bash
  make artisan cmd="migrate"
  ```
- Run tests:
  ```bash
  make test
  ```

---

## 📂 Structure

```
laravel-docker-boilerplate/
├─ docker/          # PHP, Nginx and custom scripts configuration
├─ src/             # Laravel lives here
├─ docker-compose.yml
├─ Makefile
└─ README.md
```

---

## 🛠️ Services

- **App** → PHP 8.2 with Composer
- **Web** → Nginx (port 8080)
- **DB** → MySQL 8 (port 3306)
- **Mailhog** → SMTP (1025) + Web UI (8025)
