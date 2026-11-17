# Security & Data Protection Architecture: LMO Digital Records Management System (DRMS)

**Document Version:** 1.0 (Phase 12 Deliverable)  
**Compliance Baseline:** Uganda Data Protection and Privacy Act (2019), National Records and Archives Act, OWASP ASVS Level 2  

---

## 1. Defense-in-Depth Security Controls Implemented

### 1.1 Authentication & Session Security
- **Password Hashing:** Passwords are hashed using Bcrypt (`BCRYPT_ROUNDS=12`) via `Illuminate\Support\Facades\Hash`.
- **Login Rate Limiting:** `AuthController` enforces strict per-email + IP rate limiting (`5 attempts / 60 seconds`) and records every failed attempt (`auth.login_failed`) and rate-limit lockout (`auth.rate_limited`) in `document_activity_logs`.
- **Immediate Deactivation Enforcement:** `EnsureUserIsActive` middleware runs on every authenticated request. If an administrator deactivates a user account (`is_active = false`), any active session is immediately terminated and invalidated.
- **Encrypted Database Sessions:** `.env` configures `SESSION_DRIVER=database` and `SESSION_ENCRYPT=true`, encrypting session payloads at rest.

### 1.2 Server-Side Authorization & Office Isolation
- **Laravel Policies (`DocumentPolicy`, `UserPolicy`, `AuditLogPolicy`):** Every controller action (`viewAny`, `view`, `create`, `update`, `download`, `archive`, `restore`, `retryOcr`, `assign`) enforces server-side authorization via `Gate::authorize()` or `$user->can()`.
- **Multi-Office Separation:** Staff belonging to one office (e.g., `DLMO`) cannot view, search, or download documents belonging to another office (`LMO`) unless explicitly routed to them via `document_assignments`.
- **4-Tier Confidentiality Classification:**
  - `public` & `internal`: Visible to authorized staff within the owning office.
  - `confidential`: Restricted to `Records Manager`, `Office Manager`, `System Admin`, the registering officer, or explicitly assigned officers.
  - `strictly_confidential`: Restricted strictly to `Office Manager (Executive)`, `Records Manager`, `System Admin`, or explicitly assigned officers.
- **Unauthorized Access Logging:** Any attempt to view, preview, or download a document without clearance triggers a `critical` severity entry (`security.unauthorized_access_attempt`) in `document_activity_logs` before returning HTTP `403 Forbidden`.

### 1.3 Private File Storage & Magic-Byte Validation
- **Zero Public Directory Exposure:** Uploaded PDFs and camera scans are stored exclusively in `storage/app/private/documents/{YYYY}/{MM}/{uuid}.{ext}` (or a private S3/R2 bucket in production). No confidential file is ever placed in `public/`.
- **Binary Header (Magic-Byte) Verification:** `FileStorageService::storeUploadedFile()` inspects the first 8 raw bytes of every uploaded file (`%PDF-`, `\xFF\xD8\xFF`, `\x89PNG\r\n\x1A\n`) in addition to MIME checks, blocking disguised scripts or executables.
- **Cryptographic File Integrity:** Every stored file is hashed with `SHA-256` upon upload and stored in `document_files.sha256_hash`.
- **No-Store Streaming Headers:** `DocumentFileController` streams previews and downloads with `Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0` and `X-Content-Type-Options: nosniff`.
- **PWA Service Worker Isolation:** `public/service-worker.js` caches only the 4 static UI shell files (`civic-institutional.css`, `drms-app.js`, `lmo-icon.svg`, `manifest.json`) and explicitly bypasses all `/documents`, `/archives`, `/reports`, and `/audit-logs` routes.

### 1.4 Append-Only Immutable Audit Trail
- `DocumentActivityLog` overrides Eloquent's `updating` and `deleting` model lifecycle hooks to throw a `LogicException`, preventing modification or deletion of historical accountability records through normal application interfaces.

---

## 2. Ugandan Data Protection & Records Compliance Alignment

1. **Uganda Data Protection and Privacy Act, 2019:**
   - **Accountability & Security Safeguards (Sections 20–23):** Enforced through role-based access control, encrypted sessions, encrypted backups (`AES-256-CBC`), and immutable access logs.
   - **Data Sovereignty:** Stage B production deployment supports hosting within Uganda (NITA-U National Cloud or City Hall on-premises server) so official correspondence remains within national jurisdiction.
2. **National Records and Archives Act (Uganda):**
   - **Preservation of Context & Reference Numbers:** Original reference numbers (`official_reference`) are preserved verbatim alongside sequential institutional identifiers (`LMO-YYYY-NNNNN`).
   - **Retention & Non-Destructive Archiving:** Documents are never deleted by archiving; `document_categories.retention_years` and `document_archives.retention_until` govern archival lifecycle review.
