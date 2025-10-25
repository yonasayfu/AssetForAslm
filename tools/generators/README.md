# Module Generator

The `make:module` artisan command scaffolds a clean-architecture skeleton (controller, service, DTO, request, resource) with sensible defaults.

```bash
php artisan make:module Inventory --force
```

Generated files:

```
app/
├── Domain/Inventory/DTOs/InventoryData.php
├── Domain/Inventory/InventoryService.php
├── Http/Controllers/InventoryController.php
├── Http/Requests/Inventory/InventoryRequest.php
└── Http/Resources/Inventory/InventoryResource.php
```

Stubs live in `stubs/module/*.stub`. Tweak them to match your preferred patterns (add tests, binding contracts, etc.).

Notes:
- Passing `--force` overwrites existing files.
- Extend the command if modules need additional artefacts (policy, migration, view) by dropping new stub templates in `stubs/module`.
