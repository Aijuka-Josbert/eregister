# System Administrator Manual: LMO Digital Records Management System (DRMS)

**Document Version:** 1.0  
**Target Audience:** System Administrators & Government IT Personnel  

---

## 1. Managing Staff Accounts & Role Permissions

1. Navigate to **Staff & Permissions** (`/users`).
2. **Provisioning a New Account:**
   - Enter the staff member's full name, official email, employee number, job title, assigned role, office (`LMO`, `DLMO`, `CSEC`), and department.
3. **Deactivating a Departing or Suspended User:**
   - Never delete user records linked to historical registrations or audit logs.
   - Click **Deactivate** next to the staff member's row. The `EnsureUserIsActive` middleware immediately terminates any active session for that user and blocks future sign-ins.

---

## 2. Inspecting the Accountability & Security Audit Trail

1. Navigate to **Accountability & Audit** (`/audit-logs`).
2. Filter events by **Severity** (`INFO`, `WARNING`, `CRITICAL`), **Action** (e.g., `document.viewed_confidential`, `security.unauthorized_access_attempt`, `document.downloaded`), **Staff Member**, or **Date Range**.
3. Click **View Context JSON** on any entry to inspect before/after metadata diffs or file SHA-256 hashes.

---

## 3. Managing Offices, Departments & Categories

- **Offices & Departments (`/offices`):** Add new directorates or internal units. Each office uses its unique code prefix (e.g., `LMO`, `DLMO`) when generating internal tracking numbers (`LMO-2026-00001`).
- **Categories & Retention (`/categories`):** Add new filing categories with custom badge colors and default retention periods (in years).

---

## 4. Running Backups & Automated QA Verification

- **Run Encrypted Backup via UI:** Go to **Reports & Backups** (`/reports`) and click **Run Snapshot Now**.
- **Run Encrypted Backup via CLI:**
  ```bash
  php artisan drms:backup --type=full --verify
  ```
- **Run 14-Point Automated Verification Suite:**
  ```bash
  php artisan drms:test
  ```
