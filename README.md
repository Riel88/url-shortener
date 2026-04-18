# URL Shortener

![CI](https://github.com/Riel88/url-shortener/actions/workflows/ci.yml/badge.svg)

Aplikasi REST API untuk mempersingkat URL, dibangun dengan Laravel 10.

## Fitur Utama

- **Shorten URL** — mengubah URL panjang menjadi short link
- **Redirect** — mengakses short link dan redirect ke URL asli
- **Statistik** — melihat jumlah kunjungan per short link

## Cara Menjalankan Aplikasi

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Set database ke SQLite di file .env
DB_CONNECTION=sqlite

# Generate app key
php artisan key:generate

# Buat file database
touch database/database.sqlite

# Jalankan migration
php artisan migrate

# Jalankan server
php artisan serve
```

## Endpoint API

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/shorten` | Membuat short URL baru |
| GET | `/api/stats/{code}` | Melihat statistik short URL |
| GET | `/{code}` | Redirect ke URL asli |

### Contoh Request

**Shorten URL:**
```json
POST /api/shorten
{
  "url": "https://www.example.com"
}
```

**Response:**
```json
{
  "short_code": "aB3xYz",
  "short_url": "http://localhost:8000/aB3xYz",
  "original_url": "https://www.example.com"
}
```

## Cara Menjalankan Test

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan coverage report
php artisan test --coverage --min=60
```

## Strategi Pengujian

### Unit Testing (20 test case)
Menguji logika bisnis di `UrlShortenerService` secara terisolasi:
- Validasi URL (valid/invalid/empty)
- Generate short code (panjang, format, keunikan)
- Shorten URL (membuat record, return model)
- Resolve short code (valid/invalid)
- Record visit (increment counter)
- Get statistics (struktur data, null handling)

### Integration Testing (11 test case)
Menguji endpoint API secara end-to-end:
- `POST /api/shorten` — response 201, struktur JSON, validasi input
- `GET /api/stats/{code}` — response 200/404, data akurat
- `GET /{code}` — redirect, increment visit count, 404 handling

### Coverage
Target minimum: 60% | Hasil: **84.4%**

## CI/CD Pipeline

GitHub Actions otomatis berjalan saat `push` dan `pull request` ke branch `main`:

1. Setup PHP 8.1 + Xdebug
2. Install dependencies
3. Generate app key
4. Setup SQLite database
5. Jalankan migrations
6. Jalankan semua test + coverage report (minimum 60%)