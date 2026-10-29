# Backup and recovery

The current backup is a **signed operational database backup**, not a complete server image. It contains customers, products, leads, tasks, documents, line items, payments, expenses, business settings and the business profile. It excludes user accounts/password hashes, provider credentials, conversations, server configuration, and generated PDFs. PDFs can be regenerated from document snapshots.

Only an authorized user can create/download a backup. Restore is Owner-only and requires fresh password authentication, a valid signature, matching business/schema, a replacement preview and explicit confirmation. A recovery backup is created before data is replaced. Referential and financial checks run before commit. A failure rolls back the transactional replacement.

Backups contain business data in a signed JSON envelope; signatures detect tampering but do **not encrypt the content**. Store downloads privately, use HTTPS and secure your backup copies. Keys are deliberately absent. Keep the application key/configuration separately under server administrator control. Backups currently restore only into the same business/installation with the same signing key.

## Restore

1. Settings → Backup & restore → choose a backup and enter your current Owner password.
2. Validate and review the table-count preview.
3. Enter the password again, type RESTORE and confirm.
4. Verify invoices, payments and customer totals.
5. If needed, repeat with the automatically created recovery backup.

## Remove samples

Settings → Demo data & reset shows exact demo counts. Non-demo dependencies block deletion. Enter your password and REMOVE DEMO. A recovery backup is created before deletion. The essential Owner account, real data and settings remain.

Full file archives, portable account restore and destructive full-workspace reset are not implemented. They remain release requirements where applicable. Do not edit backup payloads or bypass signatures.
