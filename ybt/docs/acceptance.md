# PRD acceptance and release gates

Source of truth: [PRD v2.0](PRD.md), supplied by the project Owner. This document tracks implementation; it does not replace or relax the PRD.

**Status: development preview, not ready for production release.** Core workflows run locally. The complete eight-phase product remains in progress. Do not describe this build as satisfying all PRD acceptance criteria.

## Acceptance matrix (PRD section 38)

| # | Criterion | Current evidence / remaining work |
|---|---|---|
| 1 | YBT branding | Implemented across application and documents. |
| 2 | Shared secure login | Implemented; session renewal, generic errors, timeout, password hashing, CSRF and rate limiting. Admin-issued expiring reset links; automated email recovery pending. |
| 3 | Manager permissions | Owner can create/deactivate Managers and set granular operation permissions. |
| 4 | Server authorization | Enforced on implemented operations; HTTP denial and deactivation checks pass. Granular assign/edit-price/selected-settings categories remain incomplete. |
| 5 | Core CRM and billing | Implemented customers, leads/conversion, products, tasks, quotations, standard invoices, payments and expenses. Extended optional/custom CRM fields, configurable stages and reminders UI remain incomplete. |
| 6 | Bills, print and PDF | Standard invoices/quotations print and download using Dompdf. GST-specific classification/templates, credit notes, proforma and invoice template selection pending. Draft editing after initial save is pending. |
| 7 | Payment reconciliation | Server totals, transactions, duplicate controls, balance validation, reversals and void rules. Integration tests pass. Standalone receipt downloads, multiple invoice allocations and refunds pending. |
| 8 | Database dashboard | All metrics and 14 chart views use scoped MariaDB queries. |
| 9 | Date filtering | Implemented dashboard, report and invoice-list query periods; custom ranges validated. Some KPI drill-down links still need exact period/filter propagation. |
| 10 | Appearance preferences | Theme/system mode, density, accent colors, base font size, sidebar width, radius, compact sidebar, live preview. Complete palette/font/row/widget controls and confirmation preferences pending. |
| 11 | Business profile reuse | Name, address, contact and billing instructions reused. Extended optional tax/banking/identity fields and protected logo uploads pending. |
| 12 | Historical snapshots | Business/customer snapshots frozen at issuance; tested against customer updates. |
| 13 | Provider configuration | Three text adapters, multiple saved configurations and custom model IDs. Full capability/parameter negotiation and live provider verification pending. |
| 14 | API key protection | Libsodium authenticated encryption, browser masking, separate key, excluded from operational backups. |
| 15 | Scoped AI search | Four bounded backend context tools; user/business/module/financial permission checks. Natural-language tool selection and full-module search pending. |
| 16 | AI visualizations | Dashboard visualizations are authoritative; chart responses embedded in AI conversation remain pending. |
| 17 | AI mutation approvals | Exact task-create proposals only. Owner approval, expiry, token integrity, permission revalidation, rejection, replay prevention and audit. Other action types and automatic natural-language proposal extraction pending. |
| 18 | AI memory controls | Conversation history enable/disable/clear; scoped users/business. Automatic long-term memory is disabled. Memory editing/export/retention and conversation summaries/context replay pending. |
| 19 | Initial demo | Five-month coherent demo dataset provisioned once through CLI. Sample Manager starts inactive. |
| 20 | Demo removal | Reliable markers, dependency guard, Owner reauthentication, counts preview, recovery backup, preserved Owner/non-demo records. Blocked-dependency and successful cleanup paths tested, including refreshed metrics and preserved real records. |
| 21 | Backup and restore | Signed operational database backup, integrity/schema/business validation, preview, reauthentication, recovery backup, transaction, relationship reconciliation; representative round-trip tested. Complete file/account/AI history backup and portable restore pending. |
| 22 | User documentation | Searchable in-product manual and Windows/XAMPP installation, AI, backup/recovery guides. Additional screenshots/tutorials pending. |
| 23 | Responsive UI | Desktop and mobile checks run in shared Chromium; keyboard labels/focus/reduced motion. Full supported device matrix and independent accessibility audit pending. |
| 24 | Tabler icons | Vendored official Tabler outline SVGs, no emoji interface icons. |
| 25 | Shared services | Centralized auth, permissions, financial calculations, records, reports, AI, backup and components. Explicit migrations and broader service refactoring pending. |
| 26 | Production tests | Local financial/security/HTTP checks run; full security, concurrency, performance, disaster-recovery and deployment gates remain pending. |

## Remaining phase work

1. Finish extended CRM fields, independent assignment and price permissions, draft editing, sorting/filter completeness and notifications.
2. Implement GST invoice rules only with explicit configured tax treatment and independent tax scenario tests; do not add an arbitrary GST toggle.
3. Complete AI natural-language tools, charts, context/history, memory, action types, provider controls and usage accounting. Verify actual supported provider contracts.
4. Finish complete private-file upload/export/backup handling, account recovery sessions, recovery tooling, full reset safeguards and migration procedures.
5. Finish detailed customization, complete dashboard KPIs/tables and exact drill-down filters.
6. Run full PRD functional/security/concurrency/accessibility/device matrix. Test failure recovery, production backup retention and restore on representative larger data.
7. Only after local acceptance: select supported PHP/MariaDB hosting, configure TLS and least privilege, set document root to public/, keep keys/storage outside public access, test deployment and rollback.

## Scope limitations preserved from the PRD

No double-entry ledger, payroll, warehouse inventory, automatic bank sync, automatic GST filing, or gateway payment processing. No profit claim from cashflow data. External notification delivery needs a configured provider.
