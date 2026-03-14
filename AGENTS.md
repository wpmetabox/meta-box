# Meta Box

This is a WordPress plugin for creating custom meta boxes and custom fields. The codebase includes PHP (WordPress plugin), JavaScript/ReactJS, and CSS/SCSS.

## Project Structure

```
meta-box/
├── inc/                  # PHP core files (legacy)
│   ├── fields/           # Field type implementations
│   ├── helpers/          # Helper functions
│   ├── storages/         # Storage backends
│   └── walkers/          # Walker classes
├── src/                  # PHP PSR-4 autoloaded classes
│   ├── Dashboard/        # Dashboard functionality
│   ├── Integrations/     # Third-party integrations
│   └── Updater/          # Auto-updater
├── js/                   # JavaScript files
├── css/                  # CSS files
├── tests/                # Tests (PHPUnit + integration tests)
│   └── phpunit/          # PHPUnit unit tests
├── package.json          # NPM scripts
└── composer.json         # PHP dependencies
```

## Build / Lint / Test Commands

### PHP Tests

Run all tests:
```bash
composer test
```

Run a single test file:
```bash
./vendor/bin/phpunit tests/phpunit/StdTest.php
```

Run a single test method:
```bash
./vendor/bin/phpunit tests/phpunit/StdTest.php --filter testTextStd
```

### PHP Code Sniffer (Lint)

```bash
# Install first if needed
composer install

# Run PHPCS
composer phpcs

# Auto-fix issues where possible
composer phpcbf
```

### PHPStan (Static Analysis)

```bash
composer phpstan
```

Note: PHPStan runs at level 9 and only analyzes the `inc/` directory.

### JavaScript Build

```bash
# Install dependencies
pnpm install

# Build JS/CSS
pnpm run build

# Watch mode for development
pnpm run start
```

## Code Style Guidelines

### PHP

- Follows **WordPress Coding Standards** (see `phpcs.xml`)
- Uses **PSR-4** autoloading via Composer (`src/` directory)
- Uses short array syntax `[]` (not `array()`)
- Minimum PHP version: **7.1**
- Minimum WordPress version: **6.5**
- Text domain: `meta-box`

### Naming Conventions

- **Classes**: Snake_Case for legacy code in `inc/` directory and PascalCase for new code in `src/` directory (e.g., `RWMB_Field`, `MetaBox\Core`)
- **Methods**: snake_case (e.g., `get_meta()`, `normalize_field()`)
- **Variables**: snake_case (e.g., `$field`, `$post_id`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `RWMB_VER`)
- **Files**: PascalCase for classes, kebab-case otherwise
- **Hooks**: snake_case (e.g., `rwmb_meta_box_config`)
- **Filters**: snake_case (e.g., `rwmb_meta_box_config`)

### Code Patterns

- Use static methods for field classes (e.g., `RWMB_Field::show()`)
- Use `self::call()` for calling field-specific methods dynamically
- Always escape output with `esc_attr()`, `esc_html()`, `esc_url()`, etc.
- Use WordPress internationalization functions: `__( 'Text', 'meta-box' )`
- Use strict type declarations where possible

### Imports

- In `inc/`: Classes are loaded via autoloader, no explicit imports
- In `src/`: Use `use` statements for PSR-4 classes

### Error Handling

- Use WordPress error handling patterns (`WP_Error`)
- Return early with proper error checking
- Use `wp_die()` for critical errors
- Log errors using `error_log()` or WordPress debugging

### Comments

- Document classes and complex methods with DocBlocks
- Inline comments explain *why*, not *what*
- PHPCS is configured to not require file/class docblocks (see `phpcs.xml`)

## Integration Tests

Integration tests in `tests/` directory are standalone PHP files that test plugin functionality within WordPress. They are not PHPUnit tests but can be run manually:

1. Ensure WordPress is installed at `../wp/` (parent directory)
2. Activate Meta Box plugin in WordPress
3. Access the test file directly in browser or via WP CLI

## Common Development Tasks

### Creating a New Field

1. Create field class in `inc/fields/` extending `RWMB_Field`
2. Add field normalization in `RWMB_Field::normalize()`
3. Add field to the autoloader if needed
4. Add tests in `tests/phpunit/`

### Adding JavaScript

- JavaScript entry points are in `js/block-editor/src/`
- Build output goes to `js/block-editor/build/`
- Use `@wordpress/scripts` for React-based blocks
