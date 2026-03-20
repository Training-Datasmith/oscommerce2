# osCommerce2 Architecture

## Purpose

osCommerce Online Merchant v2.x is the classic branch of osCommerce, offering a traditional PHP storefront and admin panel. This version targets PHP 7.2+ and introduces the `OSC\OM` namespace for new infrastructure code while maintaining backward compatibility with legacy procedural code.

## Directory Structure

```
oscommerce2/
└── catalog/
    ├── admin/                          # Admin panel (procedural pages + class includes)
    │   └── includes/
    │       ├── classes/                # Admin classes (order, shopping_cart, upload…)
    │       └── functions/             # Admin helper functions
    ├── includes/
    │   ├── classes/                    # Core storefront classes (breadcrumb, currencies…)
    │   ├── modules/                    # Content/hook modules
    │   │   └── content/checkout_success/  # Example: post-checkout modules
    │   └── OSC/
    │       └── OM/
    │           ├── Registry.php        # Object registry (new infrastructure)
    │           ├── Is/                 # Validators (email, IP address)
    │           └── Is.php              # Validator facade
    ├── ext/                            # Payment gateway extensions
    └── install/                        # Installation wizard
```

## Key Design Decisions

- **Hybrid architecture**: Legacy procedural code (functions, `include`-based page controllers) coexists with the new `OSC\OM` namespace infrastructure.
- **Registry**: `OSC\OM\Registry` stores shared objects (DB, session managers) keyed by string. Unlike osCommerce OM, only objects can be registered.
- **Content modules**: Modules under `includes/modules/content/` implement `execute()`, `install()`, `remove()`, `check()`, and `keys()`. They are enabled/disabled via `configuration` table entries.
- **`extract($GLOBALS, EXTR_SKIP)` pattern**: Template modules use `extract($GLOBALS, EXTR_SKIP)` to bring globals into scope before `include`-ing a template. This is a legacy anti-pattern — explicit `$var = $GLOBALS['key']` is preferred.
- **Database**: Uses the `tep_db_*` family of functions (legacy) and `Registry::get('Db')` (modern path).

## Extension Points

- Add a content module: create `includes/modules/content/{group}/{module}.php` implementing the module interface.
- Add a payment extension: create `ext/modules/payment/{name}/` with the gateway integration files.

## Dependency Flow

```
catalog/index.php (or page.php)
  → application_top.php  (DB, session, language, modules bootstrap)
  → Page-specific logic
  → Template output
  → application_bottom.php
```
