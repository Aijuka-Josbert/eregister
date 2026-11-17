# Requirements Specification: LMO Digital Records Management System (DRMS)

**Document Version:** 1.0 (Phase 0 Baseline)  
**Project:** Lord Mayor's Office Digital Records Management System  
**Target Stage:** Stage A (Self-Funded Prototype) & Stage B (Institutional Production Readiness)  
**Environment Baseline:** PHP 8.4.27, Laravel 12, MariaDB 11.8.9, Bootstrap 5, Alpine.js, Tesseract OCR  

---

## 1. Executive Summary & Problem Statement

The Lord Mayor's Office (LMO) currently manages incoming correspondence, invitations, memoranda, and official records through physical box files and manual paper registers. Physical files are handled by multiple staff members without centralized tracking, resulting in misplaced documents, slow retrieval times, lack of access control for confidential records, and absence of a verifiable audit trail.

The **LMO Digital Records Management System (DRMS)** is a centralized, responsive, institutional web application and Progressive Web App (PWA) designed to digitize, register, index (via Optical Character Recognition), search, archive, and audit approximately 25–50 incoming and internal records per working day.

---

## 2. Confirmed Requirements vs. Proposed Enhancements

### 2.1 Confirmed Requirements (Baseline)
1. **Organizational Scope:** Built initially for the Lord Mayor's Office (LMO), architected to support multiple offices and internal departments/units.
2. **Daily Volume:** Designed to handle 25–50 incoming documents per working day (approx. 6,500–13,000 records/year) with multi-year archival scalability.
3. **Document Capture & Upload:** Support desktop file uploads (PDF, JPEG, PNG), drag-and-drop, external scanner outputs, and direct mobile phone camera capture (including multi-page document capture).
4. **Metadata Registration:** Preserve original official reference numbers verbatim (non-unique across external organizations) alongside a system-generated unique internal tracking ID. Record title/subject, sender, recipient, organization, date issued, date received, category (including invitations), document type, department, confidentiality classification, and tags.
5. **Optical Character Recognition (OCR):** Asynchronous background extraction of searchable text from scanned PDFs and images using Tesseract OCR (English baseline), with failure reporting and manual retry.
6. **Full-Text & Metadata Search:** Fast global search across reference numbers, titles, organizations, senders, recipients, tags, and OCR-extracted text, filtered by date ranges, categories, and permissions.
7. **Role-Based & Office-Level Access Control:** Server-enforced authorization via Laravel Policies and Middleware supporting distinct user roles and confidentiality levels.
8. **Digital Archiving:** Non-destructive archiving with recorded reasons, timestamps, archive search, and authorized restoration (no permanent deletion without retention policy governance).
9. **Immutable Accountability & Audit Trail:** Append-only logging of registrations, sensitive views, metadata edits, file downloads, archiving, restorations, user/permission changes, and unauthorized access attempts.
10. **Responsive & Installable PWA:** Mobile-first Civic Institutional Modern UI (`#142B4A` Deep Navy, `#245B91` Civic Blue, `#C5A15B` Gold Accent) supporting Android, iOS, tablets, and desktops without caching confidential files in public service-worker storage.
11. **Digital-Only Scope:** Tracks digital copies and metadata only; physical box-file inventory tracking is excluded from the current scope.

### 2.2 Proposed Enhancements (Subject to Client Approval)
1. **Internal Routing & Action Assignment (`document_assignments`):** Ability for the Records Manager or Office Manager to assign a registered document to a specific officer/department with a due date and action notes.
2. **Executive Approval Workflows (`document_approvals`):** Multi-step sign-off/endorsement tracking for documents requiring formal clearance.
3. **Automated Duplicate Detection Warning:** Real-time check warning the Records Officer if the combination of `(Organization + Official Reference Number + Date Issued)` already exists.
4. **Watermarked PDF Viewing/Download:** Optional dynamic stamping of downloaded confidential PDFs with the downloading user's name and timestamp.

---

## 3. User Roles & Permission Matrix

| Capability / Module | System Admin | Records Manager | Records Officer | Office Manager | Read-Only User |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **System Settings & Backups** | Full | No | No | No | No |
| **User & Role Management** | Full | View Only | No | No | No |
| **Office & Category Setup** | Full | Full | View Only | View Only | View Only |
| **Register New Documents** | Yes | Yes | Yes | No | No |
| **Upload / Camera Scan Files** | Yes | Yes | Yes | No | No |
| **Edit Document Metadata** | Yes | Yes | Own / Recent | No | No |
| **Trigger / Retry OCR** | Yes | Yes | Yes | No | No |
| **View Public/Internal Docs** | Office Scope | Office Scope | Office Scope | Office Scope | Office Scope |
| **View Confidential/Secret Docs**| If Authorized | Yes (Office) | Registered By | Yes (Office) | No |
| **Archive / Restore Documents**| Yes | Yes | No | View Archive | No |
| **View Audit Logs** | Full Global | Office Scope | No | Office Scope | No |
| **Export Reports (PDF/Excel)** | Yes | Yes | Basic | Yes | No |

---

## 4. User Stories

### 4.1 Records Officer
- **US-01 (Mobile Scan & Register):** *As a Records Officer receiving a physical letter at the reception desk, I want to photograph its pages using my smartphone camera and register its metadata immediately so that the document is secured before the paper copy moves to another desk.*
- **US-02 (Preserve Official Reference):** *As a Records Officer, I want to enter the sender's exact reference number (e.g., `KCCA/ED/2026/045` or `REF: 12/B`) even if it contains special characters or matches another organization's numbering format, while the system generates an internal LMO tracking number automatically.*
- **US-03 (Multi-File & Multi-Page Upload):** *As a Records Officer scanning a letter with three annexures on a desktop scanner, I want to drag and drop multiple PDF/image files into a single registration record and order them properly.*
- **US-04 (Duplicate Alert):** *As a Records Officer, I want the system to alert me if a document with the same reference number and sender has already been registered so I do not create duplicate records.*

### 4.5 General & Executive Staff (Office Manager / Read-Only User)
- **US-05 (Full-Text OCR Search):** *As an Office Manager looking for an invitation or letter about "Kampala City Festival" from three months ago, I want to search for keywords inside the scanned document body and filter by Category = "Invitation" so I can retrieve the PDF in under 5 seconds.*
- **US-06 (Confidentiality Enforcement):** *As an Executive Officer, I want strictly confidential correspondence restricted to cleared roles so unauthorized staff cannot see it in search results or download the file.*

### 4.3 Records Manager
- **US-07 (Digital Archiving):** *As a Records Manager, I want to move closed or historical records into the Digital Archive with a mandatory archival reason so active views remain clean while preserving 100% of the record and its searchability in the archive.*
- **US-08 (OCR Queue Monitoring):** *As a Records Manager, I want to see which scanned documents failed OCR processing and retry them or edit their metadata.*

### 4.4 System Administrator
- **US-09 (Immutable Audit Trail):** *As a System Administrator investigating a leaked or altered record, I want to inspect an unalterable audit log showing every user who viewed, downloaded, edited, or attempted unauthorized access to that specific document, complete with timestamps and IP addresses.*
- **US-10 (Independent Encrypted Backups):** *As a System Administrator, I want automated scheduled backups of both the MariaDB database and private document storage so the institution can recover from hardware failure or corruption.*

---

## 5. Functional Requirements (FR)

- **FR-01 (Authentication & Account Lifecycle):** Support login, logout, password reset, account activation/deactivation, session timeout, and optional Time-based One-Time Password (TOTP) MFA for privileged roles. Deactivated users must never be deleted if linked to historical records or audit logs.
- **FR-02 (Document Registration):** Capture Reference Number (nullable if unnumbered), Subject/Title (required), Organization (required), Sender, Recipient, Date Issued, Date Received (required, defaults to today), Category (required), Document Type (Letter, Invitation, Memo, Report, Circular, Legal, Other), Office, Department, Confidentiality Level (`Public`, `Internal`, `Confidential`, `Strictly Confidential`), Description/Summary, and Tags.
- **FR-03 (Internal Tracking ID):** Automatically generate a human-readable, immutable internal tracking ID upon registration (e.g., `LMO-2026-00001`).
- **FR-04 (Private File Storage):** Store all uploaded files outside the web root (`storage/app/private/documents/{year}/{month}/`) using UUID/ULID storage filenames while recording the original filename, MIME type, file size, and SHA-256 checksum in `document_files`.
- **FR-05 (Asynchronous OCR Pipeline):** Dispatch a queued Laravel Job upon file upload. For images, run `tesseract` directly; for PDFs, extract embedded text or convert pages via `pdftoppm` and run `tesseract`. Store extracted text and update status (`pending`, `processing`, `completed`, `failed`, `skipped`).
- **FR-06 (Authorized Search Engine):** Provide a global search bar and advanced filter panel combining MariaDB `FULLTEXT` indexing (on title, description, reference number, organization, sender, and OCR text) with exact filters (category, status, confidentiality, date range) scoped strictly to the authenticated user's policy permissions.
- **FR-07 (Archival & Restoration):** Allow authorized users to transition documents to `archived` status, recording `archived_by`, `archived_at`, and `archive_reason` in `document_archives`. Provide a dedicated Archive Search and authorized restoration workflow.
- **FR-08 (Append-Only Audit Logging):** Record every security-relevant and document-relevant event in `document_activity_logs` with `user_id`, `document_id` (nullable for system/auth events), `action`, `ip_address`, `user_agent`, and JSON `metadata` (before/after diffs). Expose no update or delete methods on the model.
- **FR-09 (Dashboard & Institutional Reporting):** Display real-time metrics (total records, received today, monthly trend, category breakdown, office breakdown, archived count, pending actions, OCR failures) and export filtered register reports to PDF and Excel/CSV.

---

## 6. Non-Functional Requirements (NFR)

- **NFR-01 (Performance):** Metadata search and dashboard queries must return within `< 500ms` for up to 50,000 records. File uploads up to 25 MB must stream cleanly with client-side progress feedback.
- **NFR-02 (Availability & Reliability):** Queue workers must process OCR jobs asynchronously so document registration completes in `< 2 seconds` regardless of document page count.
- **NFR-03 (Security & Ugandan Compliance):** Align with the **Uganda Data Protection and Privacy Act, 2019** and the **National Records and Archives Act**. Enforce CSRF, XSS sanitization, prepared SQL statements, magic-byte MIME validation, rate limiting, and zero public URL exposure for stored files.
- **NFR-04 (Portability & Maintainability):** Standard Laravel 12 conventions, database-agnostic Eloquent queries (compatible with MySQL 8 / MariaDB 11.8 and PostgreSQL), comprehensive Markdown documentation in `/docs`, and automated PHPUnit/Pest feature tests.
- **NFR-05 (Responsive & PWA Compliance):** Usable on viewports from `320px` (mobile phones) to `1920px+` (desktop monitors). Service worker caches static shell assets (CSS, JS, icons) only; never caches authenticated HTML or private document streams.

---

## 7. MVP Scope (Stage A Prototype) vs. Future Feature List (Stage B+)

### 7.1 MVP Scope (Included in Phases 1–16)
- Complete database schema (16 tables) with migrations and realistic fictional LMO seed data.
- Role-based authentication, account activation/deactivation, and policy-based authorization.
- Full Document Registration module with duplicate warning, metadata editing, and multi-file attachment.
- Desktop drag-and-drop upload + Mobile camera multi-page scan/capture workflow.
- Background Tesseract OCR pipeline with status tracking and retry.
- Global Full-Text + Metadata Search with permission filtering and keyword highlighting.
- Digital Archive & Restoration module.
- Append-only Audit Trail & Administrator Security Dashboard.
- Executive Dashboard with PDF and Excel/CSV register exports.
- Installable PWA manifest, service worker, and Civic Institutional Modern UI.
- Automated backups command and comprehensive automated test suite.

### 7.2 Future Feature List (Post-Adoption / Phase B Expansions)
- Outgoing mail dispatch register and courier tracking.
- Multi-tier digital signature and executive minute-writing / annotation on PDFs.
- Integration with Uganda government SMS/Email gateway for urgent correspondence alerts.
- Physical archive box-file barcode printing and shelf-location mapping (if physical tracking is later requested).
- Multi-language OCR packs (Luganda, Swahili, French) if foreign or local-language correspondence increases.
- SAML2 / Active Directory / Government Single Sign-On (SSO) integration.

---

## 8. Assumptions

1. **Sample Data for Prototype:** Only fictional correspondence, names, and reference numbers will be used during Stage A development and demonstration until formal authorization is granted.
2. **Network Connectivity:** Users registering or retrieving documents have active intranet or internet connectivity to the server (offline caching of confidential government records on personal devices is intentionally disabled for security).
3. **Browser Capabilities:** Mobile staff use modern mobile browsers (Chrome on Android, Safari on iOS) that support standard HTML5 `<input type="file" capture="environment">` and `MediaDevices` camera APIs.
4. **Server Dependencies:** The host environment permits installation of `tesseract-ocr` and `poppler-utils` (`pdftoppm` / `pdftotext`) for PDF and image OCR processing.

---

## 9. Risk Register

| Risk ID | Risk Description | Likelihood | Impact | Mitigation Strategy |
| :--- | :--- | :---: | :---: | :--- |
| **R-01** | Poor quality phone photos or handwritten letters result in low OCR accuracy. | Medium | Medium | Combine OCR search with structured metadata fields (Title, Sender, Ref No, Tags); allow staff to add summary notes and retry OCR. |
| **R-02** | Unauthorized staff attempt to guess document URLs or view confidential letters. | Medium | High | Store all files in private storage (`storage/app/private`); stream downloads exclusively through authenticated controller routes protected by `DocumentPolicy`; log all 403 attempts. |
| **R-03** | Duplicate registration of the same physical letter by different officers. | Medium | Medium | Implement automated duplicate checks on `(Reference Number + Organization + Date)` during registration with a visible warning alert. |
| **R-04** | Free/ephemeral cloud hosting wipes uploaded files on container restart during demo. | High (on PaaS) | High | Use persistent storage or external S3-compatible private bucket (e.g., Cloudflare R2 free tier) or persistent local/VPS disk for Stage A demo; include repeatable demo seeder. |
| **R-05** | Vendor lock-in or developer dependency blocks government IT takeover in Stage B. | Low | High | Use standard Laravel 12 + Bootstrap 5 architecture, zero proprietary paid SDKs, complete `/docs` handover manuals, and environment-driven configuration. |
