# Users API Test Playbook

This guide teaches you how to exercise the Sanctum-protected **Users API** from beginner basics through advanced CRUD validation. It uses [Thunder Client](https://www.thunderclient.com/) inside VS Code, but every step also includes the equivalent `curl` commands for terminal usage.

---

## 1. Prerequisites

| Requirement | Notes |
| --- | --- |
| Laravel app running | Start the stack: `php artisan serve` (or `composer dev`). |
| Queue worker | `php artisan queue:listen` (if the API triggers jobs). |
| Frontend assets | `npm run dev` when testing UI side effects. |
| Base URL | `http://127.0.0.1:8000/api/v1`. |
| Seeded data | `php artisan migrate --seed` creates an admin user and baseline permissions. |
| Thunder Client | VS Code extension. |
| Authentication | Sanctum personal access token retrieved via `/auth/login`. |

---

## 2. Thunder Client Primer

1. **Create a collection** (optional but recommended) to store requests.  
2. Configure **default headers** (`Accept: application/json`) in the request or collection.  
3. Use the **Auth** tab with type **Bearer** to store your token once you have it.  
4. Thunder Client supports **Environments**. Define `base_url=http://127.0.0.1:8000/api/v1` so you can use `{{base_url}}` in requests.

> Tip: You can duplicate any request tab once it is configured. This saves time when switching endpoints.

---

## 3. Authenticate First

All subsequent requests require a Sanctum bearer token.

### Thunder Client (Login)

| Setting | Value |
| --- | --- |
| Method | `POST` |
| URL | `{{base_url}}/auth/login` |
| Headers | `Accept: application/json`, `Content-Type: application/json` |
| Body (JSON) | ```json
{ 
  "email": "admin@example.com",
  "password": "password",
  "device_name": "thunder-client"
}
``` |

Click **Send**. Copy the `token` from the JSON response. In the same request tab, open **Auth -> Bearer** and paste the token. Every duplicated tab will reuse it.

### curl
```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@example.com","password":"password","device_name":"cli"}'
```

Save the `token` value to an environment variable for later use:
```bash
TOKEN="your-token-here"
```

---

## 4. Users API Quick Reference

| Endpoint | Method | Purpose | Needs Permission |
| --- | --- | --- | --- |
| `/users` | GET | List users (supports `per_page`, `page`, `search`) | `users.manage` |
| `/users/{id}` | GET | Show a user | `users.manage` |
| `/users` | POST | Create a user | `users.manage` |
| `/users/{id}` | PUT | Replace an entire user record | `users.manage` |
| `/users/{id}` | PATCH | Partially update a user | `users.manage` |
| `/users/{id}` | DELETE | Remove a user (self-delete blocked) | `users.manage` |
| `/auth/logout` | POST | Revoke the token | any authenticated user |

> **PUT vs PATCH**
> - **PUT** expects a full replacement payload. Any missing attribute is interpreted as `null` or default. Use this when the client can send the complete state (for example, editing a full profile form).
> - **PATCH** is for partial updates. Send only the fields that need to change. This keeps other attributes untouched.

The Users API accepts these key attributes:

| Field | Type | Required? | Notes |
| --- | --- | --- | --- |
| `name` | string | yes | |
| `email` | string | yes | Must be unique. |
| `password` | string | yes (POST) | Optional on PUT/PATCH; include `password_confirmation`. |
| `account_status` | string | yes | One of `pending`, `active`, `suspended`. |
| `account_type` | string | yes | One of `internal`, `external`. |
| `roles` | array | optional | Array of role names. |
| `permissions` | array | optional | Array of permission names. |
| `staff_id` | int or null | optional | Link to an existing staff record. |

---

## 5. Thunder Client Walkthrough (CRUD)

Everything below assumes you already duplicated the login request and set the Auth tab to Bearer with your token.

### 5.1 List Users (GET `/users`)

| Setting | Value |
| --- | --- |
| Method | `GET` |
| URL | `{{base_url}}/users?per_page=10&search=` |
| Headers | `Accept: application/json` |
| Auth | Bearer token from login |

Click **Send**. Expected result: a paginated JSON payload with `data`, `links`, and `meta`. Confirm you see `data[0].email` and `roles` arrays.

### 5.2 Show a User (GET `/users/{id}`)

| Setting | Value |
| --- | --- |
| Method | `GET` |
| URL | `{{base_url}}/users/1` |

Send the request. If user `1` exists you will receive the full record, including `roles`, `permissions`, and linked `staff` details when available.

### 5.3 Create a User (POST `/users`)

| Setting | Value |
| --- | --- |
| Method | `POST` |
| URL | `{{base_url}}/users` |
| Headers | `Accept: application/json`, `Content-Type: application/json` |
| Body | ```json
{
  "name": "QA Rookie",
  "email": "qa.rookie@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "account_status": "active",
  "account_type": "internal",
  "roles": ["Manager"],
  "permissions": ["staff.view"],
  "staff_id": null
}
``` |

After you send the request Thunder Client should display **201 Created** with the newly created resource. Verify that the user appears in the list request too.

### 5.4 Update a User Completely (PUT `/users/{id}`)

Use PUT when you can provide **every mutable field**. If you omit a field, it may reset to `null`.

| Setting | Value |
| --- | --- |
| Method | `PUT` |
| URL | `{{base_url}}/users/{{user_id}}` |
| Body | ```json
{
  "name": "QA Rookie",
  "email": "qa.rookie@example.com",
  "password": "",
  "password_confirmation": "",
  "account_status": "suspended",
  "account_type": "internal",
  "roles": ["Manager"],
  "permissions": ["staff.view", "staff.update"],
  "staff_id": null
}
``` |

Notes:
- Keep `password` empty if you do not want to change it.  
- PUT still requires `password_confirmation` even when empty.  
- The response body (200 OK) returns the new state so you can verify the change.

### 5.5 Update a User Partially (PATCH `/users/{id}`)

PATCH changes only what you include. For example, flip the status and append a role without touching other fields.

| Setting | Value |
| --- | --- |
| Method | `PATCH` |
| URL | `{{base_url}}/users/{{user_id}}` |
| Body | ```json
{
  "account_status": "active",
  "roles": ["Manager", "Auditor"]
}
``` |

After the request returns 200 OK, run a GET `/users/{{user_id}}` to check that only `account_status` and `roles` changed while everything else stayed the same.

### 5.6 Delete a User (DELETE `/users/{id}`)

| Setting | Value |
| --- | --- |
| Method | `DELETE` |
| URL | `{{base_url}}/users/{{user_id}}` |

Expected: `204 No Content`. If you attempt to delete your own account, you receive a `422` with message `"You cannot delete your own account."`

### 5.7 Logout (POST `/auth/logout`)

| Setting | Value |
| --- | --- |
| Method | `POST` |
| URL | `{{base_url}}/auth/logout` |

Success returns `{ "message": "Logged out" }`. Any subsequent request without re-authenticating should return `401 Unauthorized`.

---

## 6. curl Cheat Sheet

Replace `$TOKEN` with your bearer token. Keep JSON payloads in single quotes to avoid shell escaping issues.

```bash
# List users
curl "$BASE_URL/users?per_page=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Create
curl -X POST "$BASE_URL/users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"API Bot",
    "email":"api.bot@example.com",
    "password":"secret123",
    "password_confirmation":"secret123",
    "account_status":"active",
    "account_type":"internal"
  }'

# PUT update
curl -X PUT "$BASE_URL/users/5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"API Bot",
    "email":"api.bot@example.com",
    "password":"",
    "password_confirmation":"",
    "account_status":"suspended",
    "account_type":"internal"
  }'

# PATCH update
curl -X PATCH "$BASE_URL/users/5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{ "account_status":"active" }'

# Delete
curl -X DELETE "$BASE_URL/users/5" \
  -H "Authorization: Bearer $TOKEN"

# Logout
curl -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 7. Automated Regression (Advanced)

Use the existing Pest suite to ensure nothing regresses after code changes:

```bash
./vendor/bin/pest tests/Feature/Api/UsersTest.php
```

This file covers listing, creating, updating (PUT), partial updating (PATCH), deleting, and self-delete protection. Run the entire API suite when you add new endpoints:

```bash
./vendor/bin/pest tests/Feature/Api
```

Pest uses SQLite in-memory by default, so migrations must support it (avoid database-specific SQL like `NOW()`).

---

## 8. Troubleshooting

| Symptom | Likely Cause | Fix |
| --- | --- | --- |
| `401 Unauthorized` | Missing/expired bearer token | Re-run `/auth/login` and set the token again. |
| `403 Forbidden` | User lacks `users.manage` | Grant the permission (seed data adds it to Admin) or impersonate an admin. |
| `422 Unprocessable Entity` | Validation error | Check the `errors` array returned in the response. Common issues: duplicate email, missing password confirmation. |
| `500` during role assignment | Permissions use a different guard | Ensure permissions exist for the `web` guard (seeders create them). |
| Thunder Client still sends old data | Cached tab | Click the **Code** view to double-check the JSON you are sending; duplicate a fresh tab if needed. |

### Debug Tools
- Inspect Laravel logs at `storage/logs/laravel.log`.  
- Use `php artisan tinker` to inspect database state.  
- Re-run `php artisan migrate:fresh --seed` if test data becomes messy.  

---

## 9. Expanding Beyond Users

Once you are comfortable with users, repeat the same pattern for:

| Module | Endpoints | Required Permission |
| --- | --- | --- |
| Roles | `/roles`, `/roles/{id}` | `roles.manage` or `users.manage` |
| Staff | `/staff`, `/staff/{id}` | `staff.view`, `staff.create`, `staff.update`, `staff.delete` |
| Notifications | `/notifications` | any authenticated user |

Each controller follows the same Sanctum guard, so the authentication and testing approach remains consistent.

---

## 10. Checklist Before Sign-off

- [ ] Login works and bearer token stored.  
- [ ] CRUD requests return expected HTTP codes (200, 201, 204).  
- [ ] PUT vs PATCH behavior verified.  
- [ ] Role/permission assignments respected.  
- [ ] Automated tests pass locally.  
- [ ] Laravel logs are clean (no unexpected warnings or errors).  

With this playbook you can confidently validate the Users API end-to-end, whether you are new to HTTP clients or building advanced automated checks.
