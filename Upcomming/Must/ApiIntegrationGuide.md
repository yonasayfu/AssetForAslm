# API Integration Guide

This boilerplate ships with a Sanctum-protected REST API designed for mobile apps and third-party integrations. Use this guide to understand versioning, authentication, pagination, and error conventions before wiring new endpoints.

## 1. Base URL & Versioning
- All endpoints live under /api/v1/....
- Increment the version when making breaking changes; keep previous versions available until consumers migrate.
- Add new routes in outes/api.php and group them by namespace + prefix.

## 2. Authentication
- Uses Laravel Sanctum personal access tokens.
- POST /api/v1/auth/login accepts email/password and returns a token (default ability *).
- POST /api/v1/auth/logout revokes the current token.
- Tokens are supplied via the Authorization: Bearer <token> header.
- For mobile apps, create one token per device and store the name in device_name.
- To scope tokens, pass an bilities array when calling $user->createToken(..., ['read', 'write']).

## 3. Serialization & Resources
- API controllers return JSON using dedicated Resource classes (see pp/Http/Resources/Api).
- List endpoints wrap results in a resource collection with data, meta, and links payloads matching Laravel's paginator.
- All timestamps are ISO 8601 strings (UTC).

## 4. Pagination
- Query parameters: page=<int>, per_page=<5|10|25|50|100>.
- Response shape:
  `json
  {
    "data": [...],
    "meta": {
      "current_page": 1,
      "per_page": 10,
      "total": 42
    },
    "links": {
      "next": "https://app.test/api/v1/..."
    }
  }
  `
- Use paginate() or simplePaginate() in controllers; never return unbounded collections.

## 5. Error Format
- On validation errors, return HTTP 422 with:
  `json
  {
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."]
    }
  }
  `
- For authentication failures, return HTTP 401 with { "message": "Invalid credentials." }.
- For authorization failures, return HTTP 403 with { "message": "This action is unauthorized." }.

## 6. Rate Limiting
- Default throttle: pi middleware (60 requests/minute).
- Override per route/group with ->middleware('throttle:120,1').

## 7. Reference Artifacts
- **OpenAPI spec** lives at docs/api/openapi.yaml.
- **Postman collection** (optional) can be exported to docs/api/postman_collection.json.
- Update both when adding or changing endpoints.

## 8. Testing
- Add API tests under 	ests/Feature/Api/... using Pest or PHPUnit.
- Use Sanctum's testing helpers: Sanctum::actingAs(, ['*']);.

## 9. Future Enhancements
- Add refresh tokens if sessionless rotation is required.
- Support OAuth 2.0 / JWT if federated auth becomes necessary.
- Wire WebSockets or Laravel Reverb for real-time updates.
