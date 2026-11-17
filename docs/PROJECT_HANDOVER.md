# Management Demonstration Script, Production Proposal & Developer Handover

**Document Version:** 1.0 (Phases 16 & 17 Deliverable)  
**Project:** Lord Mayor's Office Digital Records Management System (LMO DRMS)  

---

## 1. Phase 16: Live Management Demonstration Script (10-Step Walkthrough)

Use the seeded demonstration accounts on the login screen (all share password `Lmo@2026!` with 1-click quick-fill buttons on `/login`).

### Pre-Configured Demonstration Personas
| Role | Name | Email | Password | Clearance |
| :--- | :--- | :--- | :--- | :--- |
| **Records Officer** | Samuel Mugisha | `officer@lmo.go.ug` | `Lmo@2026!` | Register, Scan, Upload, Search Public/Internal |
| **Records Manager** | Grace Nabirye | `manager@lmo.go.ug` | `Lmo@2026!` | Full Registry, Confidential, Archive/Restore, Reports |
| **Office Manager** | Patricia Achieng | `executive@lmo.go.ug` | `Lmo@2026!` | Executive View, Strictly Confidential, Routing |
| **Read-Only Staff** | Ronald Ssemanda | `readonly@lmo.go.ug` | `Lmo@2026!` | Public/Internal View Only (Blocked from Confidential) |
| **System Admin** | Eng. Daniel Kato | `admin@lmo.go.ug` | `Lmo@2026!` | Full System, Users, Audit Ledger, Encrypted Backups |

### Step-by-Step Live Scenario for Leadership
1. **Scenario Step 1 — Physical Letter Arrives:**
   - Sign in as **Records Officer** (`officer@lmo.go.ug`).
   - Click **Scan / Register Record** (`/documents/create`).
2. **Scenario Step 2 — Mobile Camera Scan / Upload:**
   - On a smartphone or laptop, click **Open Live Scanner** (or **Capture Photo Page**) to snap a page, or drag and drop a PDF letter.
3. **Scenario Step 3 — Enter Official Reference & Metadata:**
   - Enter Official Reference: `MoLG/INV/2026/501` (demonstrating verbatim preservation).
   - Enter Title: `Official Invitation to National Urban Governance Dialogue`, Organization: `Ministry of Local Government`, Category: `Official Invitation`, Document Type: `Official Invitation`.
4. **Scenario Step 4 — Complete Registration:**
   - Click **Complete Registration & Run OCR**. Point out the auto-generated internal tracking ID (`LMO-2026-00006`) and SHA-256 file hash.
5. **Scenario Step 5 — Inspect Extracted OCR Text:**
   - Scroll to **Extracted OCR Searchable Content** on the document page to show that the text inside the PDF/scan has been extracted automatically.
6. **Scenario Step 6 & 7 — Full-Text OCR Search by Another User:**
   - Sign in as **Office Manager** (`executive@lmo.go.ug`).
   - In the top Global Search bar, type `Nakivubo Channel` (a phrase that appears only inside the scanned PDF body of `LMO-2026-00001`, not in its title).
   - Show that the system retrieves `LMO-2026-00001` (`MKCCMA/PS/2026/084`) immediately with `<mark>` keyword highlighting.
7. **Scenario Step 8 — Digital Archiving by Records Manager:**
   - Sign in as **Records Manager** (`manager@lmo.go.ug`), open a completed record, click **Archive Record**, enter the archival reason, and confirm. Show it inside the **Digital Archive Vault** (`/archives`).
8. **Scenario Step 9 — Unauthorized Access Attempt Denied & Logged:**
   - Sign in as **Read-Only Staff** (`readonly@lmo.go.ug`).
   - Attempt to search for or open the Strictly Confidential Legal Advisory (`LMO-2026-00003` / `LMO/LEG/CONF/2026/017`). Show that it is hidden from search and returns HTTP `403 Access Denied` if accessed by URL.
9. **Scenario Step 10 — Administrator Inspects the Immutable Audit Trail:**
   - Sign in as **System Administrator** (`admin@lmo.go.ug`) and open **Accountability & Audit** (`/audit-logs`).
   - Show the `CRITICAL` log entry `security.unauthorized_access_attempt` alongside every registration, confidential view, file download, and archival action.

---

## 2. Implemented Features (Stage A) vs. Proposed Future Features (Stage B+)

### Implemented & Verified in Stage A Prototype
- Multi-office & department data model (16 core tables) with MariaDB `FULLTEXT` indexing.
- Role-based access control (5 roles, 16 granular permissions) and server-side Laravel Policies.
- Verbatim official reference preservation + automatic `LMO-YYYY-NNNNN` tracking IDs + real-time duplicate detection.
- Multi-file drag-and-drop upload + mobile camera multi-page capture + binary magic-byte validation + private storage.
- Automated PDF/Image OCR extraction (`pdftotext`, `pdftoppm`, `tesseract`) with manual retry.
- Global search with matched keyword highlighting, category/date filters, and Invitations register.
- Non-destructive Digital Archive Vault with restoration history.
- Append-only `DocumentActivityLog` audit ledger and Administrator Security Dashboard.
- Executive Dashboard + Excel/CSV export + Printable PDF Register report.
- AES-256-CBC encrypted backup & SHA-256 recovery verification (`php artisan drms:backup --verify`).

### Proposed Future Enhancements for Stage B Adoption
- Outgoing mail courier/dispatch tracking register.
- Executive PDF in-browser annotation / digital stamp & signature integration.
- Institutional SMS/Email gateway integration for urgent correspondence alerts.
- National Single Sign-On (SSO) / Active Directory integration.

---

## 3. Stage B Institutional Adoption & Production Cost Proposal

| Infrastructure / Service Item | Stage A Prototype | Stage B Option 1: Managed Cloud VPS + R2 | Stage B Option 2: NITA-U / City Hall Server |
| :--- | :---: | :---: | :---: |
| **Compute (4 vCPU, 8 GB RAM)** | $0 (Local / Free) | $28.00 / month | $0 (Institutional Server Allocation) |
| **Managed MySQL / MariaDB** | $0 (Local MariaDB) | $18.00 / month | $0 (Institutional DB Allocation) |
| **Private Object Storage (100 GB)** | $0 (Local Private Disk) | $1.50 / month (Cloudflare R2) | $0 (Institutional SAN) |
| **Offsite Encrypted Backups (200 GB)**| $0 (Local Encrypted Vault)| $4.00 / month | $5.00 / month (Offsite Cloud Vault) |
| **Official Domain & TLS (`.go.ug`)** | $0 | $2.50 / month | $0 (Existing `.go.ug` Subdomain) |
| **Total Estimated Infrastructure** | **$0.00 / month** | **~$54.00 / month (~$648/year)** | **~$5.00 / month (~$60/year)** |

---

## 4. Developer & IT Team Handover Checklist

1. **Source Code & Git Repository:** Hosted cleanly in `/var/www/html/register` with `.gitignore` excluding `.env` and private storage files.
2. **Database Schema:** Managed entirely via `database/migrations/` (`php artisan migrate --seed`).
3. **Configuration Secrets:** Isolated in `.env` (no hardcoded credentials in source code).
4. **Automated Verification Command:** Run `php artisan drms:test` after any upgrade or server migration to verify all 14 functional and security controls.
