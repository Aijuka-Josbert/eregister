# LMO Digital Records Management System (DRMS)

A secure, mobile-first institutional **Digital Records Management System (DRMS)** built for the **Lord Mayor's Office (LMO)** using **PHP 8.4**, **Laravel**, **MariaDB / MySQL (with FULLTEXT search)**, **Bootstrap 5 (Civic Institutional Modern UI)**, and **Poppler / Tesseract OCR**.

---

## Key Capabilities

1. **Document Registration & Official Reference Preservation:** Preserves external organizations' official reference numbers verbatim alongside an auto-generated internal LMO tracking ID (`LMO-2026-00001`), with real-time duplicate detection.
2. **Mobile Camera Multi-Page Scanning & Drag-and-Drop Uploads:** Photograph multi-page physical letters directly from a smartphone/tablet camera or upload PDF/JPEG/PNG files from desktop scanners.
3. **Private Storage & Magic-Byte Validation:** All files are verified by binary header signature, hashed with `SHA-256`, stored in `storage/app/private/documents/`, and streamed exclusively through policy-protected routes.
4. **Automated OCR Text Extraction:** Extracts searchable text from PDFs (`pdftotext` / `pdftoppm`) and images (`tesseract-ocr`) with status badges and 1-click retry.
5. **Global Full-Text + Metadata Search:** Search across reference numbers, titles, senders, organizations, tags, and OCR-extracted text with keyword highlighting (`<mark>`) and strict role/office/confidentiality scoping.
6. **Role-Based & Office-Separated Access Control:** Server-enforced Laravel Policies (`DocumentPolicy`, `UserPolicy`, `AuditLogPolicy`) across 5 institutional roles and 4 confidentiality tiers (`Public`, `Internal`, `Confidential`, `Strictly Confidential`).
7. **Digital Archive Vault:** Non-destructive archiving with mandatory reasons, retention dates, vault search, and authorized restoration.
8. **Append-Only Accountability & Audit Trail:** Tamper-protected `document_activity_logs` recording registrations, confidential views, downloads, edits, archives, user changes, and `403` access denials.
9. **Executive Dashboard, Reports & Encrypted Backups:** Real-time KPIs, Excel/CSV & Printable PDF exports, and `AES-256-CBC` encrypted backups (`php artisan drms:backup --verify`).

---

## Quick Start & Demonstration

```bash
cd /var/www/html/register

# 1. Run migrations and seed fictional demonstration data
php artisan migrate:fresh --seed

# 2. Run the 14-point automated QA & Security verification suite
php artisan drms:test

# 3. Run an encrypted backup and verify decryption/SHA-256 integrity
php artisan drms:backup --type=full --verify

# 4. Start the web server
php artisan serve --host=0.0.0.0 --port=8000
```

### Demonstration Staff Accounts (Password: `Lmo@2026!`)
- **System Administrator:** `admin@lmo.go.ug`
- **Records Manager:** `manager@lmo.go.ug`
- **Records Officer:** `officer@lmo.go.ug`
- **Office Manager (Executive):** `executive@lmo.go.ug`
- **Read-Only Staff:** `readonly@lmo.go.ug`
- **Deputy Lord Mayor Office (Office Separation Test):** `deputy.records@lmo.go.ug`

---

## Documentation Index (`/docs`)

- [Requirements Specification](docs/REQUIREMENTS.md)
- [System Architecture](docs/ARCHITECTURE.md)
- [Database Design & Data Dictionary](docs/DATABASE.md)
- [Security & Data Protection](docs/SECURITY.md)
- [Backup & Disaster Recovery Manual](docs/BACKUP_AND_RECOVERY.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [Staff User Manual](docs/USER_GUIDE.md)
- [System Administrator Manual](docs/ADMIN_GUIDE.md)
- [Management Demo Script & Handover Guide](docs/PROJECT_HANDOVER.md)
- [Project Progress Log](PROJECT_PROGRESS.md)


# requirement download ext-xml extenxion when wifi is back 