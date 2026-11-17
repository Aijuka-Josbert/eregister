# Deployment & Hosting Guide: Stage A Prototype & Stage B Production

**Document Version:** 1.0 (Phases 15 & 17 Deliverable)  

---

## 1. Local & Intranet Prototype Deployment (Stage A)

### 1.1 Prerequisites
- Linux (Ubuntu / Debian)
- PHP 8.3 or 8.4 (`pdo_mysql`, `mbstring`, `fileinfo`, `zip`, `openssl`, `curl`, `xml`)
- MariaDB 11.8 or MySQL 8.0+
- Poppler Utils (`pdftotext`, `pdftoppm`) and Tesseract OCR (`tesseract-ocr`, `tesseract-ocr-eng`)

### 1.2 Step-by-Step Installation
```bash
cd /var/www/html/register
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan drms:test
```

### 1.3 Starting the Demonstration Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
To allow leadership to test smartphone camera scanning over HTTPS during a live demonstration, run a free Cloudflare Quick Tunnel:
```bash
cloudflared tunnel --url http://127.0.0.1:8000
```

---

## 2. Cloud Staging & Production Deployment (Stage B)

### 2.1 Recommended Institutional Production Options
1. **Option A: NITA-U Government Cloud / Municipal Data Centre (Recommended Primary)**
   - **Data Sovereignty:** Keeps all official government records inside Uganda under institutional ownership.
   - **Specification:** Ubuntu 24.04 LTS VM (4 vCPU, 8 GB RAM, 200 GB SSD) + Managed MySQL/MariaDB + Encrypted Offsite Backup Vault.
2. **Option B: Managed Cloud VPS (Hetzner / DigitalOcean / Linode) + Cloudflare R2 Private Object Storage**
   - **Specification:** 4 vCPU, 8 GB RAM VPS ($24–$40/mo) + Managed MySQL ($15–$30/mo) + Cloudflare R2 Private S3 Bucket ($1.50/mo for 100 GB, zero egress fees).

### 2.2 Production Web Server (Nginx + PHP-FPM + Supervisor)
- Point Nginx `root` strictly to `/var/www/html/register/public`.
- Enforce TLS 1.3 (HTTPS) via Let's Encrypt (`certbot --nginx`).
- Configure `supervisor` to run `php artisan queue:work --tries=2 --timeout=180` continuously for asynchronous Tesseract OCR jobs.
- Configure `FILESYSTEM_DISK=s3` in `.env` when using private S3/Cloudflare R2 storage.
