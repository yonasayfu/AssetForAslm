Amazing—this is enough to design a full clone plan. Here’s the complete, structured deliverable you can build against with Laravel 12 + Inertia + Vue 3.

---

# 0) Product scope (from your screenshots)

A single-tenant asset management system with:
Dashboard (widgets + charts + calendar alerts), Alerts (due/overdue/expiring), Asset lifecycle (create, view, check-out/in, lease/return, move, reserve, dispose), Lists (Assets, Maintenance, Warranties), Maintenance, Warranty, Sites/Locations, Departments, People/Employees, Import/Export, Documents & Images galleries, Audits (cycle counts), Reporting (prebuilt + custom + automated), Setup (company info + taxonomy), Help.

---

# 1) Module → Feature inventory

**Dashboard**

* KPI cards: Number of Assets, Value of Assets, Purchases in Fiscal Year
* Charts: “Asset Value by Category”
* Calendar with filters: Assets Due, Maintenance Due, Warranty Expiring, Lease Expiring
* “Manage Dashboard” page: choose widgets & column count

**Alerts**

* Assets Past Due
* Leases Expiring
* Maintenance Due / Overdue
* Warranties Expiring

**Assets**

* List of Assets (column setup, export to Excel)
* Add/Edit Asset form (Description, Asset Tag ID, Purchase Date, Cost + Currency, Purchased From, Brand, Model, Serial No, Project Code, Asset Condition, Category, Department, Site, Location, Photo)
* Asset View with tabs: Details, Events/History, Photos, Docs, Warranty, Linking (relations), Maintenance, Reserve, Audit, History
* Actions: Check-out, Check-in, Lease, Lease Return, Move, Reserve, Dispose
* Import wizard (template + field limits; 5,000 rows per file)

**Lists**

* List of Assets
* List of Maintenances
* List of Warranties

**Maintenance**

* Maintenance list with import/export; statuses; due/expired columns

**Audits**

* Start/Manage Audits: choose Audit Name, Site, Location; filter by Category; add explicit Asset IDs; “Add to list”

**Setup**

* Company Info (address, timezone, currency, date format, fiscal year, logo)
* Sites (full address), Locations (per site), Categories, Departments

**Advanced**

* Persons/Employees
* (Customers also appears in first batch)

**Reports**

* Automated, Custom, and families: Asset, Audit, Check-Out, Leased Asset, Maintenance, Reservation, Status, Transaction, Other

**Tools**

* Import, Export, Document Gallery, Image Gallery, Audit launcher

**Help/Support**

* About, Contact (map), Terms, Privacy, Videos, Reviews, Changelog

---

# 2) Database schema (normalized, starter)

> Convention: `id` BIGINT PK, `*_id` FKs, `*_at` timestamps, all tables have `created_at/updated_at`, many have `deleted_at` (soft deletes). Add proper indexes.

### Identity & Org

* **users** (auth) — name, email (unique), password, timezone, locale
* **roles**, **permissions**, **model_has_roles**, **role_has_permissions** (Spatie Permission)
* **departments** — name, parent_id
* **sites** — name, description, address_line, suite, city, state, postal_code, country
* **locations** — site_id, name, code, parent_id, notes
* **people** — name, employee_code, title, email, phone, department_id, site_id, location_id, active
* **customers** — name, contact_name, email, phone, address_json (optional)

### Taxonomy

* **categories** — name, parent_id, description
* **conditions** — name (e.g., New, Good, Fair, Poor, Broken)
* **projects** — code, name, funder, start_at, end_at (for “Project code”)

### Assets & artifacts

* **assets** — asset_tag (unique), description, purchase_date, cost_minor (integer), currency, purchased_from, brand, model, serial_no, project_id, condition_id, category_id, department_id, site_id, location_id, status (enum: available|checked_out|leased|reserved|under_repair|disposed|lost|donated|sold|broken), photo_path (nullable)
* **asset_custom_fields** — asset_id, key, value (text)  *or store JSON on `assets` as `custom_json`*
* **documents** — attachable_type, attachable_id, title, path, mime, size, visibility
* **images** — attachable_type, attachable_id, path, caption

### Lifecycle: movements, usage, reservations, leasing

* **checkouts** — asset_id, assignee_type (person|department|customer), assignee_id, due_at, checked_out_at, checked_in_at, condition_out_id, condition_in_id, notes, status (open|closed|overdue)
* **leases** — asset_id, lessee_type (customer|department), lessee_id, start_at, end_at, rate_minor, currency, terms, status (active|returned|overdue|cancelled)
* **reservations** — asset_id, requester_id (user/person), start_at, end_at, status (pending|approved|cancelled|fulfilled)
* **moves** — asset_id, from_location_id, to_location_id, moved_by (user_id), moved_at, reason

### Maintenance & warranty

* **maintenances** — asset_id, code, title, description, type (preventive|corrective), status (open|scheduled|in_progress|completed|cancelled|overdue), opened_by (user_id), assigned_to (user_id or person_id), scheduled_for, started_at, closed_at, cost_parts_minor, cost_labor_minor, currency
* **warranties** — asset_id, provider, description, length_months, start_at, end_at, active (bool), notes

### Audits (stocktakes)

* **audits** — name, site_id, location_id (nullable for site-wide), status (draft|in_progress|review|closed), started_at, closed_at, created_by
* **audit_lines** — audit_id, asset_id, expected_location_id, found_location_id, found (bool), variance_note, scanned_at, scanned_by

### Commerce / purchases (visible on dashboard)

* **purchases** — vendor, po_number, invoice_number, purchased_at, currency, subtotal_minor, tax_minor, total_minor
* **purchase_items** — purchase_id, asset_id (nullable), description, qty, unit_cost_minor

### Cross-cutting

* **alerts** — type (maintenance_due|maintenance_overdue|warranty_expiring|lease_expiring|asset_due), target_type/id, due_at, status (open|sent|dismissed), sent_at, payload_json
* **saved_reports** — owner_id, name, family (assets|maintenance|leases|audits|reservations|status|transactions|custom), definition_json, schedule_cron (nullable), last_run_at
* **activities** (activity log) — causer_id, subject_type, subject_id, event, properties_json, occurred_at

> Add FKs and cascading rules. Use money as integers in minor units; store `currency` per row.

---

# 3) State machines (key workflows)

**Asset.status**

* available → checked_out → available
* available → leased → available
* available → reserved → available
* any → under_repair → available
* any → disposed/donated/sold/lost/broken (terminal; only admins can revert)

**Checkout.status**: open → overdue (by scheduler) → closed (on check-in)

**Lease.status**: active → overdue (by scheduler) → returned | cancelled

**Maintenance.status**: open → scheduled → in_progress → completed | cancelled → (auto overdue if `scheduled_for < now && !completed`)

**Audit.status**: draft → in_progress → review → closed

**Warranty.active**: derived from start/end dates; mark inactive on expiry.

---

# 4) Permissions (roles & key abilities)

* **Admin**: full access
* **Manager**: manage assets & operations, approvals, reports
* **Technician**: create maintenance, run audits, update status
* **Staff**: view assets, request reservations/checkout, upload docs
* **Auditor**: read-only + run audit flows, export reports
* **Read-only**: view & export where allowed

Map to policies:

* AssetPolicy: viewAny, view, create, update, delete, operate(checkout/lease/move/reserve/dispose), export, import
* MaintenancePolicy, AuditPolicy, WarrantyPolicy, PersonPolicy, Site/Location/Category/Department policies, ReportPolicy.

---

# 5) Backend architecture (Laravel 12)

**Core packages**

* spatie/laravel-permission (RBAC)
* spatie/laravel-activitylog (history/events)
* league/flysystem + S3/Local for files; (optional) spatie/laravel-medialibrary
* maatwebsite/excel for import/export
* laravel/scout (optional) for global search
* laravel/horizon (queues), laravel/sanctum (API auth)

**Key services**

* `AlertService`: builds due/expiring sets and writes `alerts`
* `AssetService`: create/update asset + custom fields + media
* `CheckoutService`, `LeaseService`, `ReservationService`, `MoveService`
* `MaintenanceService`, `AuditService`
* `ReportBuilder`: translates saved `definition_json` to queries
* `ImportService`: parsing/mapping/validation + upsert; chunked jobs
* `DashboardService`: KPIs + charts aggregations
* Notifications: `AlertEmail`, `ReportEmail`, `MaintenanceReminder`

**Scheduler (app/Console/Kernel)**

* hourly: mark overdues (checkouts, leases, maintenance)
* daily: generate alerts for next 7/30 days (configurable)
* as scheduled: send saved reports
* nightly: rebuild materialized pivot/stats tables if you add them

**APIs** (for future app/PWA)

* `/api/assets` CRUD + search + operations endpoints
* `/api/maintenances`, `/api/audits`, `/api/reports/run`, `/api/imports`

**Validation & auditing**

* FormRequests + business rules (e.g., cannot check-out disposed asset)
* ActivityLog on every mutation (subject, event, user, properties)

---

# 6) Frontend (Inertia + Vue 3) structure

**Layout**

* `AppLayout.vue` with sidebar sections: Dashboard, Alerts, Assets, Lists, Reports, Tools, Advanced, Setup, Help
* Breadcrumbs + page actions; global Search box

**Reusable components**

* `DataTable.vue` (server-side pagination, sorting, multi-filter chips, column picker, bulk actions)
* `FormSection.vue`, `Modal.vue`, `Drawer.vue`, `DateRangePicker.vue`
* `MoneyInput.vue`, `SelectAsync.vue` (for Site/Location/Category)
* `UploadBox.vue` (images/docs), `StatusChip.vue`, `KpiCard.vue`, `Calendar.vue`
* `Wizard.vue` for operations (Check-out, Lease, Move, Reserve, Dispose)

**Pages (routes)**

* `/dashboard` — KPI, pie by category, calendar widget (chips)
* `/alerts` — grouped; link to record
* `/assets`

  * `/assets` (Index)
  * `/assets/create`, `/assets/:id/edit`
  * `/assets/:id` (tabs: Details | Events | Photos | Docs | Warranty | Maintenance | Reserve | Audit | History)
  * `/assets/:id/checkout`, `/checkin`, `/lease`, `/return`, `/move`, `/reserve`, `/dispose`
* `/lists/assets`, `/lists/maintenances`, `/lists/warranties`
* `/maintenance` (Index + Create/Edit)
* `/audits` (Index/Manage) and `/audits/:id/run`
* `/reports` (families + builder + saved)
* `/tools/import`, `/tools/export`, `/tools/galleries`
* `/advanced/people`, `/advanced/customers`
* `/setup/company`, `/setup/sites`, `/setup/locations`, `/setup/categories`, `/setup/departments`, `/setup/dashboard`

---

# 7) Import/Export spec

**Import Wizard**

* Targets: Assets, People, Sites, Locations, Categories, Departments, Maintenance, Warranties
* Steps: (1) Upload CSV/XLSX → (2) Map columns to fields (auto-match on header) → (3) Validate preview (N errors shown with row numbers) → (4) Queue job → (5) Email summary
* Safety: dry-run mode, “update or create” toggle via Asset Tag ID
* Limits: chunk processing (e.g., 1,000 rows per chunk), 5,000/file UI hint
* Templates: downloadable per target; field limits (lengths/enums)

**Export**

* From any list/search; same column set as current view (column picker respected); CSV or XLSX; optional saved templates.

---

# 8) Reporting & dashboard

**Report families**

* Assets (inventory, by site/location/category/department, by status)
* Check-Out (open/overdue/closed; by assignee; aging)
* Leased Assets (active/overdue/returns)
* Maintenance (due/overdue/completed; costs; MTTR)
* Reservation (upcoming; utilization)
* Audit (variance; found vs expected)
* Warranty (by expiry window)
* Transactions/Status changes (activity log)
* **Custom**: generic filter builder + column selection

**Dashboard widgets (sample)**

* Number of Assets, Value of Assets, Purchases FY
* Available / Checked-out / Leased / Lost-Missing / Broken / Under Repair
* Disposed / Donated / Sold
* Warranty vs Assets (ratio)
* Widgets persist per user in a `user_dashboard_settings` JSON or table.

---

# 9) Field-level details (from the forms you sent)

**Assets**

* Required: description, asset_tag, purchase_date, cost, project_code, asset_condition
* Optional: purchased_from, brand, model, serial_no, site, location, category, department, photo
* Derived: status defaults to `available`
* Custom fields: “Project code” displayed under “Custom fields” group on view page

**Warranty**

* fields: active (bool), asset_tag_id (FK), description, length_months, expires_at, notes

**Maintenance List columns**

* status, expires (due date), asset_tag_id, description, title, maintenance_detail

**People**

* searchable on: name, employee ID, job title, email, phone; columns include site, location, department, notes

**Sites & Locations**

* Sites: address capture; Locations are **subset of Sites** (location belongs to site)

---

# 10) Day-by-day build roadmap (backend + frontend)

> 15 work-days (~3 weeks). Adjust pace as needed.

**Day 1 — Project & auth**

* New Laravel 12 app (Inertia + Vue 3 + Tailwind)
* Add Spatie Permission; seed Admin user/role; base layout & sidebar

**Day 2 — Setup & taxonomy**

* Migrations + CRUD (Sites, Locations, Categories, Departments, Conditions, Projects)
* Vue pages for each (Index + Create/Edit); policy checks

**Day 3 — Assets domain**

* Asset migrations + models (custom fields support), repositories/services
* Asset create/edit (server validation) + list (DataTable v1)

**Day 4 — Asset view & media**

* Tabs structure; Photos & Docs (storage + download controller)
* ActivityLog for create/update

**Day 5 — Import/Export v1**

* Import: Assets (column map + dry-run + queue)
* Export: Assets (respect column picker)

**Day 6 — Movements**

* Move entity + history; “Move” operation wizard; activity entries

**Day 7 — Checkout/Check-in**

* Checkout table + constraints; due logic; check-in; overdue scheduler
* Alerts for asset due (open checkouts nearing due)

**Day 8 — Lease**

* Lease table + flows; return processing; overdue scheduler + alerts

**Day 9 — Reservations**

* Reservations table; approval flow; conflicts; calendar feed (iCal optional)

**Day 10 — Maintenance**

* Ticket CRUD; statuses; scheduling; email notifications; list & search
* Import/export maintenance

**Day 11 — Warranty**

* Warranty CRUD; link to asset; expiry calculator; alerts

**Day 12 — Audits**

* Audit & audit_lines; “build list by site/location/category” + “add asset IDs”
* Run screen (confirm scan → mark found/missing); variance report

**Day 13 — Dashboard & Alerts**

* KPI queries; category pie; calendar alerts (chips); manage widgets page
* Alert service hourly/daily; alert center UI

**Day 14 — Reports**

* Prebuilt families; CSV/XLSX export; “Save report” with filters
* Optional scheduled emails

**Day 15 — Hardening**

* Indexes, N+1 checks, policies review, seed demo data, error states, 404/403 pages
* Backups, logs, .env example, deploy scripts, documentation page

---

# 11) Data validation & UX rules (high-impact)

* `asset_tag` unique; cannot change if there are linked operations unless admin
* Prevent checkout/lease/reserve if status in {disposed, donated, sold, lost, broken}
* Move requires `from_location_id != to_location_id`
* Maintenance cannot close without `completed_at` and optional costs
* Warranty `end_at = start_at + length_months` (if length given)
* Import: detect duplicates by asset_tag; report row/column errors
* Lists: persistent column setup per user; fast multi-facet filters

---

# 12) Nice-to-have (phase 2)

* Barcode/QR generation & print sheets; camera scan (PWA page)
* Meilisearch/Elasticsearch global search
* Bulk actions: mass move, mass assign department, mass status change
* Webhooks for integrations; SSO (OIDC/SAML)

---

# 13) What I can deliver next (just say the word)

* **SQL migrations starter pack** (tables above)
* **Policy stubs + seeder** for roles
* **Route map & Vue page skeletons** (folders, stubs, sample DataTable)
* A **printable System Spec + ERD PDF** (one document you can hand to your team)

If you want me to start with code stubs or the printable spec first, tell me which one you prefer.
