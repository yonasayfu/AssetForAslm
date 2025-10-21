# Geraye to Asset Boilerplate Feature Tracker

## Recommended Delivery Approach
- **Incremental rebuild (preferred)**: Re-create each reusable Geraye feature inside `AssetManagement`, using Geraye as a reference but writing fresh code. This keeps the project clean, reinforces your Laravel skills, and ensures we only bring across intentional pieces.
- **Direct copy/paste (not recommended)**: Faster initially but risks dragging healthcare-specific logic, old bugs, and mismatched dependencies.
- **Boilerplate snapshot plan**: Complete Phases 0 through 6 in `AssetManagement`, then clone or zip that state as the reusable boilerplate. Implement messaging and task delegation in the clone before returning to Phase 7 for the asset-specific roadmap.

> We will follow the incremental rebuild approach unless you direct otherwise. Each section below mirrors the order we will implement features so you can track progress and understand the reasoning.

## How to Use This Checklist
- Tick items as they move from planning to in-progress to done. Add owner names or dates as needed.
- Cross-reference the Geraye docs listed per task to pull exact patterns or components.
- When new scope emerges, add a bullet in the relevant phase so this file remains the single source of truth.
- Capture a local Git commit after each completed task or logical batch; remote configuration can wait until the boilerplate snapshot is finished.

---

### Phase 0 – Preparation and Environment
- [x] Confirm Composer and NPM dependencies needed for shared features (Spatie Permission, Laravel Excel, Tailwind plugins, Inertia helpers).  
  _Source_: `gerayehealthcare_2/composer.json`, `package.json`
- [x] Align `.env.example`, queue, mail, and cache configs with Geraye best practices.  
  _Source_: `Development_Guides.md`, `STORAGE.md`
- [x] Document canonical coding conventions (service layer pattern, repository naming, testing strategy).  
  _Source_: `ARCHITECTURE.md`, `Development_Guides.md`

### Phase 1 – Application Shell and Styling
- [x] Port dual-layout concept (auth vs dashboard) with glass-themed header and sidebar.  
  _Source_: layout files, `UI_and_Templates.md`, `MyNewAppsidebar.md`
- [x] Migrate liquid glass CSS utilities and Tailwind config tweaks.  
  _Source_: Geraye `resources/css/app.css`
- [x] Rebuild shared Vue components used across modules (`GlassCard`, `GlassButton`, modal, tabs, breadcrumbs, empty-state).  
  _Source_: `UI_and_Templates.md`, component library in Geraye
- [x] Install notification toast component and confirmation modal patterns.  
  _Source_: Geraye `Toast.vue`, `NotificationBell.vue`

### Phase 2 – Identity, Staff, and RBAC
- [x] Scaffold `Staff` module (model, migration, CRUD, Inertia pages, validation, policies).  
  _Source_: Geraye Staff module
- [x] Recreate user management screens (user CRUD, password reset flows, profile settings).  
  _Source_: `ACCOUNT_POLICY_AND_FLOWS.md`
- [x] Integrate Spatie Permission with seed roles tailored to the Asset project (`Admin`, `Manager`, `Technician`, `Staff`, `Auditor`, `ReadOnly`).  
  _Source_: `RBAC_AND_UI_CONSISTENCY_GUIDE.md`, `RBAC_ROLE_ACCESS_MATRIX.md`
- [x] Apply middleware plus frontend guards (route middleware, `PermissionGuard` component).  
  _Source_: Geraye middleware, `PermissionGuard.vue`
- [x] Add audit logging for user and staff actions.  
  _Source_: activity log traits in Geraye

### Phase 3 – Search, Filters, and Navigation UX
- [x] Implement `useTableFilters` composable and reusable filter components for list pages.  
  _Source_: `SEARCH.md`
- [x] Build global search modal overlay with asset-aware providers (Users, Staff, Assets placeholder).  
  _Source_: `GlobalSearch.vue`
- [x] Ensure paginated lists share consistent columns, empty states, and bulk action hooks.  
  _Source_: Geraye list views in `resources/js/Pages/*/Index.vue`

### Phase 4 – Export, Print, and File Handling
- [x] Port `ExportableTrait`, export jobs/queues, and download center UI.  
  _Source_: Geraye export services, `PRINT_*` docs
- [x] Recreate unified print layout (Blade plus Vue) with per-module configuration.  
  _Source_: `PrintableReport.vue`, `pdf-layout.blade.php`
- [x] Implement document and image upload components with storage policies and preview modals.  
  _Source_: `STORAGE.md`, document gallery components
- [x] Restore central storage structure rules (per-module folders, retention schedule).  
  _Source_: `STORAGE.md`

### Phase 5 - Notifications and Alerting
- [x] Adapt notification center (database notifications, unread badge, real-time updates).  
  _Source_: `GERAYE-ORGANIZED.md`, notification components
- [ ] Configure email notifications (alerts, password resets) and document testing steps.  
  _Source_: `EMAIL_TESTING_GUIDE.md`
- [ ] Outline messaging and task requirements for the future boilerplate clone; defer implementation until after the Phase 6 snapshot.  
  _Source_: `TASK_DELEGATION_ENHANCEMENTS.md`, `MESSAGING_SYSTEM_DOCUMENTATION.md`

### Phase 6 – Dashboards, Alerts, and Schedulers
- [ ] Rebuild dashboard widget system (KPI cards, charts, calendar alerts) using asset KPIs.  
  _Source_: Geraye dashboard components, `MyNewProjectRoadmap.md`
- [ ] Implement alert job pipeline (maintenance due, warranty expiring, overdue checkout).  
  _Source_: Geraye alert schedulers
- [ ] Create alert center UI mirroring Geraye alert pages.  
  _Source_: alerts module components
- [ ] Add background job monitoring (Horizon, queues) and admin dashboards.  
  _Source_: `Development_Guides.md`

### Boilerplate Collaboration Add-on (after Phase 6 snapshot; optional)
- [ ] Duplicate or zip the project once Phase 6 is complete to preserve a clean boilerplate base.  
- [ ] In the clone, repurpose task assignment and to-do flows for generic collaboration needs.  
  _Source_: `TASK_DELEGATION_ENHANCEMENTS.md`
- [ ] Integrate in-app messaging services with neutral naming (for example, Conversations, Threads).  
  _Source_: `MESSAGING_SYSTEM_DOCUMENTATION.md`
- [ ] Confirm notification routing for collaboration events (mentions, task updates, message alerts).  
- [ ] Document configuration toggles so collaboration features can be enabled or disabled in future projects.  
- [ ] Archive the boilerplate release (zip or git tag) before resuming Asset Management Phase 7 work.

### Phase 7 – Domain Modules (Asset Roadmap Execution)
- [ ] Taxonomy CRUD (Sites, Locations, Departments, Categories, Conditions, Projects).  
  _Source_: `MyNewProjectRoadmap.md` day-by-day plan
- [ ] Assets module (CRUD, detail tabs, activity timeline).  
- [ ] Asset lifecycle operations (Move, Checkout/In, Lease, Reserve, Dispose).  
- [ ] Maintenance and Warranty modules with notifications.  
- [ ] Audits, reports, import/export enhancements.  
- [ ] Final polish: dashboard by role, access analytics, deployment prep.

### Phase 8 – API and Mobile Readiness
- [ ] Expose REST API aligned with Geraye mobile conventions (Sanctum auth, pagination resources).  
  _Source_: `MOBILE_API.md`, `TECHNICAL_IMPLEMENTATION_GUIDE_2025.md`
- [ ] Prepare API documentation or Postman collections.  
- [ ] Plan future Flutter integration milestones (no code yet).  

### Continuous Documentation and QA
- [ ] Update `Upcomming/ROADMAP.md` as phases close; link to commits or PRs.  
- [ ] Keep `TASKS.md` in sync with checklist state.  
- [ ] Add unit and feature tests as components are rebuilt; mirror Geraye coverage.  
- [ ] Record learned differences or improvements versus Geraye in `GerayeFeatureMigrationPlan.md`.

---

## Open Decisions and Needs
- Confirm MVP requirement for messaging and tasks before entering the collaboration add-on phase.
- Choose default database (PostgreSQL vs MySQL) and adjust migrations accordingly.
- Decide if Horizon and queues should ship enabled by default in the boilerplate.
- Align on branding copy for login and dashboard while keeping layout identical.

Update this section as new blockers or questions emerge so the team can resolve them quickly.

