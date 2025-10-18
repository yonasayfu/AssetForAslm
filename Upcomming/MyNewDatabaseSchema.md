Perfect — now that you want to go **step-by-step starting from the database schema**, we’ll build it in a clean, structured way so it matches your Laravel 12 + Inertia Vue 3 project setup.

Below is the **complete schema foundation (Phase 1–3)** — everything you need before moving to the code level.

---

# 🧱 DATABASE SCHEMA (FOUNDATION LEVEL)

We’ll divide this into layers:

1. **Organization & Reference Tables**
2. **Core Asset Management**
3. **Operational Modules**
4. **Support & Audit Trails**

---

## 1️⃣ ORGANIZATION & REFERENCE TABLES

These define your hierarchy, structure, and static lookups.

### 🗂️ `departments`

| Column      | Type                        | Notes                               |
| ----------- | --------------------------- | ----------------------------------- |
| id          | BIGINT (PK)                 | Auto Increment                      |
| name        | VARCHAR(100)                | e.g., “Finance”, “IT”, “Operations” |
| description | TEXT                        | Optional                            |
| parent_id   | BIGINT (FK: departments.id) | For sub-departments                 |
| created_at  | TIMESTAMP                   |                                     |
| updated_at  | TIMESTAMP                   |                                     |

---

### 🌍 `sites`

| Column      | Type         | Notes                       |
| ----------- | ------------ | --------------------------- |
| id          | BIGINT (PK)  |                             |
| name        | VARCHAR(150) | e.g., “HQ”, “Zambia Office” |
| description | TEXT         | Optional                    |
| address     | VARCHAR(255) |                             |
| suite       | VARCHAR(100) | e.g., “Block C, 2nd floor”  |
| city        | VARCHAR(100) |                             |
| state       | VARCHAR(100) |                             |
| postal_code | VARCHAR(20)  |                             |
| country     | VARCHAR(100) |                             |
| created_at  | TIMESTAMP    |                             |
| updated_at  | TIMESTAMP    |                             |

---

### 🏢 `locations`

| Column      | Type                  | Notes                  |
| ----------- | --------------------- | ---------------------- |
| id          | BIGINT (PK)           |                        |
| site_id     | BIGINT (FK: sites.id) |                        |
| name        | VARCHAR(150)          | e.g., “Storage Room 1” |
| description | TEXT                  |                        |
| created_at  | TIMESTAMP             |                        |
| updated_at  | TIMESTAMP             |                        |

---

### 🧾 `categories`

| Column      | Type                       | Notes                                     |
| ----------- | -------------------------- | ----------------------------------------- |
| id          | BIGINT (PK)                |                                           |
| name        | VARCHAR(100)               | e.g., “IT Equipment”, “Medical Equipment” |
| description | TEXT                       |                                           |
| parent_id   | BIGINT (FK: categories.id) | Optional hierarchy                        |
| created_at  | TIMESTAMP                  |                                           |
| updated_at  | TIMESTAMP                  |                                           |

---

### 👥 `people`

| Column        | Type                        | Notes              |
| ------------- | --------------------------- | ------------------ |
| id            | BIGINT (PK)                 |                    |
| name          | VARCHAR(120)                |                    |
| employee_id   | VARCHAR(50)                 |                    |
| title         | VARCHAR(100)                | e.g., “IT Officer” |
| phone         | VARCHAR(50)                 |                    |
| email         | VARCHAR(100)                |                    |
| department_id | BIGINT (FK: departments.id) |                    |
| site_id       | BIGINT (FK: sites.id)       |                    |
| location_id   | BIGINT (FK: locations.id)   |                    |
| notes         | TEXT                        |                    |
| created_at    | TIMESTAMP                   |                    |
| updated_at    | TIMESTAMP                   |                    |

---

## 2️⃣ CORE ASSET MANAGEMENT

This is the heart of your system — everything connects to this.

### 💻 `assets`

| Column          | Type                                                                                       | Notes                     |
| --------------- | ------------------------------------------------------------------------------------------ | ------------------------- |
| id              | BIGINT (PK)                                                                                |                           |
| asset_tag       | VARCHAR(50)                                                                                | Unique, e.g., “ASLM00125” |
| description     | TEXT                                                                                       | Required                  |
| purchase_date   | DATE                                                                                       |                           |
| cost            | DECIMAL(15,2)                                                                              |                           |
| currency        | VARCHAR(10)                                                                                | e.g., “USD”               |
| purchased_from  | VARCHAR(150)                                                                               | Vendor name               |
| brand           | VARCHAR(100)                                                                               |                           |
| model           | VARCHAR(100)                                                                               |                           |
| serial_no       | VARCHAR(150)                                                                               |                           |
| project_code    | VARCHAR(150)                                                                               | From project list         |
| asset_condition | ENUM(‘New’,‘Good’,‘Fair’,‘Poor’,‘Broken’)                                                  |                           |
| site_id         | BIGINT (FK: sites.id)                                                                      |                           |
| location_id     | BIGINT (FK: locations.id)                                                                  |                           |
| category_id     | BIGINT (FK: categories.id)                                                                 |                           |
| department_id   | BIGINT (FK: departments.id)                                                                |                           |
| assigned_to     | BIGINT (FK: people.id)                                                                     | Nullable                  |
| status          | ENUM(‘Available’,‘Checked Out’,‘Under Repair’,‘Leased’,‘Disposed’,‘Lost’,‘Donated’,‘Sold’) |                           |
| photo           | VARCHAR(255)                                                                               | File path                 |
| created_by      | BIGINT (FK: users.id)                                                                      |                           |
| created_at      | TIMESTAMP                                                                                  |                           |
| updated_at      | TIMESTAMP                                                                                  |                           |

---

### 🧾 `asset_photos`

| Column     | Type                   | Notes        |
| ---------- | ---------------------- | ------------ |
| id         | BIGINT (PK)            |              |
| asset_id   | BIGINT (FK: assets.id) |              |
| path       | VARCHAR(255)           | Storage path |
| caption    | VARCHAR(150)           |              |
| created_at | TIMESTAMP              |              |

---

### 📂 `asset_documents`

| Column      | Type                   | Notes                    |
| ----------- | ---------------------- | ------------------------ |
| id          | BIGINT (PK)            |                          |
| asset_id    | BIGINT (FK: assets.id) |                          |
| title       | VARCHAR(150)           | e.g., “Purchase Invoice” |
| file_path   | VARCHAR(255)           |                          |
| mime_type   | VARCHAR(100)           |                          |
| uploaded_by | BIGINT (FK: users.id)  |                          |
| created_at  | TIMESTAMP              |                          |

---

## 3️⃣ OPERATIONAL MODULES

These handle asset lifecycle, audits, maintenance, and warranties.

---

### 🔧 `maintenances`

| Column           | Type                                             | Notes                   |
| ---------------- | ------------------------------------------------ | ----------------------- |
| id               | BIGINT (PK)                                      |                         |
| asset_id         | BIGINT (FK: assets.id)                           |                         |
| title            | VARCHAR(150)                                     | e.g., “Routine Service” |
| description      | TEXT                                             |                         |
| maintenance_type | ENUM(‘Preventive’,‘Corrective’)                  |                         |
| scheduled_for    | DATE                                             |                         |
| completed_at     | DATE                                             |                         |
| status           | ENUM(‘Open’,‘Scheduled’,‘Completed’,‘Cancelled’) |                         |
| cost             | DECIMAL(15,2)                                    |                         |
| vendor           | VARCHAR(150)                                     |                         |
| created_at       | TIMESTAMP                                        |                         |

---

### 🪪 `warranties`

| Column        | Type                   | Notes                                |
| ------------- | ---------------------- | ------------------------------------ |
| id            | BIGINT (PK)            |                                      |
| asset_id      | BIGINT (FK: assets.id) |                                      |
| description   | VARCHAR(150)           | e.g., “3-year manufacturer warranty” |
| provider      | VARCHAR(150)           |                                      |
| length_months | INT                    |                                      |
| start_date    | DATE                   |                                      |
| expiry_date   | DATE                   | Auto-calculated                      |
| active        | BOOLEAN                |                                      |
| notes         | TEXT                   |                                      |
| created_at    | TIMESTAMP              |                                      |

---

### 🧾 `audits`

| Column       | Type                                | Notes |
| ------------ | ----------------------------------- | ----- |
| id           | BIGINT (PK)                         |       |
| name         | VARCHAR(150)                        |       |
| site_id      | BIGINT (FK: sites.id)               |       |
| location_id  | BIGINT (FK: locations.id)           |       |
| status       | ENUM(‘Draft’,‘Ongoing’,‘Completed’) |       |
| started_at   | TIMESTAMP                           |       |
| completed_at | TIMESTAMP                           |       |
| created_at   | TIMESTAMP                           |       |

---

### 📋 `audit_assets`

| Column     | Type                   | Notes |
| ---------- | ---------------------- | ----- |
| id         | BIGINT (PK)            |       |
| audit_id   | BIGINT (FK: audits.id) |       |
| asset_id   | BIGINT (FK: assets.id) |       |
| found      | BOOLEAN                |       |
| notes      | TEXT                   |       |
| checked_at | TIMESTAMP              |       |

---

## 4️⃣ SUPPORT & ADMIN

These support your operations — logging, alerts, company setup.

---

### 🏢 `companies`

| Column               | Type         | Notes                  |
| -------------------- | ------------ | ---------------------- |
| id                   | BIGINT (PK)  |                        |
| name                 | VARCHAR(150) |                        |
| address              | VARCHAR(255) |                        |
| city                 | VARCHAR(100) |                        |
| state                | VARCHAR(100) |                        |
| postal_code          | VARCHAR(20)  |                        |
| country              | VARCHAR(100) |                        |
| timezone             | VARCHAR(100) | e.g., “Africa/Nairobi” |
| currency             | VARCHAR(10)  |                        |
| date_format          | VARCHAR(20)  | e.g., “dd/MM/yyyy”     |
| financial_year_start | DATE         | e.g., “2025-04-01”     |
| logo                 | VARCHAR(255) |                        |
| created_at           | TIMESTAMP    |                        |

---

### 🔔 `alerts`

| Column   | Type                                                    | Notes |
| -------- | ------------------------------------------------------- | ----- |
| id       | BIGINT (PK)                                             |       |
| type     | ENUM(‘Maintenance Due’,‘Warranty Expiring’,‘Audit Due’) |       |
| asset_id | BIGINT (FK: assets.id)                                  |       |
| due_date | DATE                                                    |       |
| sent     | BOOLEAN                                                 |       |
| sent_at  | TIMESTAMP                                               |       |

---

### 🧾 `activity_logs`

| Column     | Type                  | Notes                      |
| ---------- | --------------------- | -------------------------- |
| id         | BIGINT (PK)           |                            |
| user_id    | BIGINT (FK: users.id) |                            |
| model_type | VARCHAR(150)          | e.g., “Asset”              |
| model_id   | BIGINT                |                            |
| action     | VARCHAR(100)          | e.g., “Created”, “Updated” |
| properties | JSON                  | Change snapshot            |
| created_at | TIMESTAMP             |                            |

---

## ✅ RELATIONSHIPS SUMMARY

| Relationship                      | Type             |
| --------------------------------- | ---------------- |
| Company → Sites                   | 1:N              |
| Site → Locations                  | 1:N              |
| Department → People               | 1:N              |
| Category → Assets                 | 1:N              |
| Site → Assets                     | 1:N              |
| Location → Assets                 | 1:N              |
| Department → Assets               | 1:N              |
| Asset → Warranties                | 1:N              |
| Asset → Maintenances              | 1:N              |
| Asset → Audits (via audit_assets) | M:N              |
| User → Assets                     | 1:N (created_by) |

---

## 🔗 PHASE 1 OUTPUT SUMMARY

When you implement this schema:

* You’ll have **core CRUD** for Assets, Departments, Sites, Locations, People, and Categories.
* You can **import/export** via Excel (Asset Tag-based uniqueness).
* You can begin building **Audit**, **Maintenance**, and **Warranty** modules right after this.

---

Would you like me to show you **the Laravel migration structure next** (each table as `Schema::create`) or would you prefer first the **ERD visual diagram** version to confirm structure before moving into migrations?
