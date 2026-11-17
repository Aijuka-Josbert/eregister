# Backup & Disaster Recovery Manual: LMO Digital Records Management System (DRMS)

**Document Version:** 1.0 (Phase 13 Deliverable)  
**Encryption Standard:** `AES-256-CBC` with `SHA-256` Integrity Verification  

---

## 1. What Is Backed Up

Every full backup (`php artisan drms:backup --type=full --verify`) creates a self-contained, encrypted archive containing:
1. **Relational Database Snapshot (`database_snapshot.json`):** Offices, categories, documents metadata, tags, archival history, and audit trail records.
2. **Extracted OCR Text:** Full extracted text (`documents.ocr_text` and `document_files.file_ocr_text`).
3. **Original Uploaded & Scanned Files (`files/*`):** Every original PDF, JPEG, and PNG stored in `storage/app/private/documents/`.
4. **Backup Audit Record (`backup_logs` table):** Records the backup reference (`LMO-BKP-YYYYMMDD-HHMMSS-XXXX`), byte size, `SHA-256` checksum, and verification status.

---

## 2. Running Backups & Verification

### 2.1 Manual CLI Backup with Live Recovery Verification
```bash
php artisan drms:backup --type=full --verify
```
Expected output:
```text
Starting LMO DRMS [full] encrypted backup...
Backup created: LMO-BKP-20261117-143419-5211 (9175 bytes)
SHA-256: f25480cfcd871de9f3ecc2b4448d987f6152c63f4a35de1ff3af1bf361888a28
Backup decryption & SHA-256 verification passed.
```

### 2.2 Automated Daily Scheduled Backups
Configured in `routes/console.php` to run daily at `23:30` East Africa Time (`Africa/Kampala`):
```php
Schedule::command('drms:backup --type=full --verify')->dailyAt('23:30');
```
On a production Linux server, enable the cron scheduler by adding:
```cron
* * * * * cd /var/www/html/register && php artisan schedule:run >> /dev/null 2>&1
```

### 2.3 One-Click Administrator UI Backup
System Administrators can also trigger and verify an immediate encrypted snapshot from **Reports & Backups** (`/reports` → **Run Snapshot Now**).

---

## 3. Disaster Recovery & Restoration Procedure

1. **Locate the Encrypted Backup File:**
   Backups are stored in `storage/app/private/backups/{YYYY}/{MM}/LMO-BKP-*.zip.enc` (and replicated to offsite S3/R2 storage in production).
2. **Verify Integrity & Decrypt:**
   `BackupService::verifyBackup($backupLog)` validates the `SHA-256` checksum against `backup_logs.sha256_hash`, strips the `LMOENC1` header, extracts the 16-byte IV, and decrypts the payload using the `SHA-256` digest of `APP_KEY` via `openssl_decrypt(..., 'AES-256-CBC')`.
3. **Restore Database & Private Files:**
   - Extract `files/*` back into `storage/app/private/documents/{YYYY}/{MM}/`.
   - Import the MariaDB/MySQL dump or `database_snapshot.json` records.
   - Run `php artisan drms:test` to confirm 100% system readiness after recovery.
