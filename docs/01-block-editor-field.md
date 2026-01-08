# Tài liệu kỹ thuật: Block Editor Field

## Tổng quan

Field `block-editor` là một field type trong Meta Box cho phép người dùng sử dụng WordPress Block Editor (Gutenberg) trong các meta box. Field này sử dụng gói `isolated-block-editor` để tạo một instance độc lập của Block Editor, không ảnh hưởng đến editor chính của WordPress.

## Kiến trúc

Field được triển khai trong class `RWMB_Block_Editor_Field` kế thừa từ `RWMB_Field`, bao gồm các thành phần chính:

1. **PHP Backend** (`inc/fields/block-editor.php`):
   - Xử lý enqueue scripts/styles
   - Normalize field parameters
   - Render HTML output
   - Format giá trị hiển thị

2. **JavaScript Frontend** (`js/block-editor.js`):
   - Khởi tạo isolated block editor

3. **Assets**:
   - `isolated-block-editor.js`: Bundle của isolated block editor package
   - `isolated-block-editor.css`: Styles cho isolated editor
   - `block-editor.css`: Custom styles cho Meta Box integration

## Cách thức hoạt động

### PHP

**Bước 1: Enqueue Scripts và Styles**

```php
public static function admin_enqueue_scripts()
```

- Enqueue các WordPress core scripts: `wp-editor`, `wp-media`, `wp-media-utils`
- Trigger actions: `enqueue_block_editor_assets`, `enqueue_block_assets`
- Đăng ký và enqueue các packages WordPress cần thiết:
  - `wp-element`, `wp-blocks`, `wp-block-editor`
  - `wp-components`, `wp-data`, `wp-compose`
  - `wp-i18n`, `wp-hooks`, `wp-media-utils`
- Đăng ký script `isolated-block-editor` (isolated block editor bundle)
- Đăng ký script `rwmb-block-editor` (Meta Box integration)
- Đăng ký style `isolated-block-editor-core` (core styles cho isolated editor)
- Đăng ký style `isolated-block-editor` (styles cho isolated editor)
- Đăng ký và enqueue các stylesheets

**Bước 2: Normalize Field Parameters**

```php
public static function normalize( $field )
```

- Kế thừa normalize từ parent class
- Parse `allowed_blocks` parameter:
  - Hỗ trợ cả array và string (mỗi block một dòng)

**Bước 3: Render HTML**

```php
public static function html( $meta, $field )
```

- Tạo một textarea để lưu trữ dữ liệu
- Embed settings dưới dạng JSON trong attribute `data-settings`

**Frontend:**

```php
public static function format_single_value( $field, $value, $args, $post_id )
```

- Render blocks bằng function `do_blocks()`

## Cấu hình Field

```php
[
    'type' => 'block-editor',
    'id' => 'my_block_editor',
    'name' => 'Block Editor Content',
    'allowed_blocks' => [
        'core/paragraph',
        'core/heading',
        'core/image',
        // ... các blocks khác
    ],
]
```
