# Link Field Design

## Summary

Add a dedicated `link` field type to Meta Box that provides a polished, ACF-compatible UX for selecting links. The field uses WordPress's native Insert Link popup (`wpLink`) to let content editors search for internal posts or enter custom URLs, with title and "open in new tab" support — all through a single, familiar interface.

## Motivation

Currently, achieving ACF-like link functionality in Meta Box requires combining multiple fields (a select for post-vs-URL, a post field, a URL field, a text field for title, a checkbox for target) with conditional logic. This is:

- Time-consuming to configure per project
- Produces non-uniform saved data (different shapes for post vs URL)
- Provides a fragmented editing experience

A dedicated link field eliminates this friction entirely.

## Design Decisions

**UI approach:** Reuse WordPress's native Insert Link popup (`wpLink`), identical to how ACF does it. No custom autocomplete, no modal — just wrapping the built-in component that content editors already know.

**Data storage:** Single serialized post meta row per field instance. Static save (title/URL frozen at save time, not dynamically resolved). Includes `post_id` when a post is selected for advanced use cases.

**Clone support:** Full support via Meta Box's existing `RWMB_Clone` mechanism.

## Saved Data Format

Single meta row (or per-clone index), serialized array:

```php
[
    'url'     => 'https://example.com/my-page',  // Always populated
    'title'   => 'My Page Title',                 // Always populated
    'target'  => '_blank',                        // '_blank' or '' (empty = same tab)
    'post_id' => 42,                              // 0 if custom URL
]
```

## Field Configuration

```php
[
    'type'  => 'link',
    'id'    => 'hero_link',
    'name'  => 'Hero Link',
    'clone' => true,  // Optional, defaults to false
]
```

Minimal config. No `post_types` option needed — WordPress's native link popup searches all public post types by default.

## Files

| File | Description |
|------|-------------|
| `inc/fields/link.php` | `RWMB_Link_Field` class |
| `js/link.js` | wpLink integration JS |
| `css/link.css` | Field display styling |

## PHP Class: `RWMB_Link_Field extends RWMB_Field`

### `normalize( $field )`

- Call `parent::normalize( $field )`
- Set `multiple => false` (composite field, not multi-value)
- Return normalized field

### `admin_enqueue_scripts()`

- Enqueue `link.css` with `wp_enqueue_style()`
- Enqueue `link.js` with `wp_enqueue_script()`, depends on `['jquery']`
- Call `wp_enqueue_editor()` to ensure wpLink JS is loaded
- No localized data needed (wpLink is globally available)

### `html( $meta, $field )`

Render a container with:

1. Hidden inputs for each sub-value (using bracket notation):
   - `<input type="hidden" name="field_name[url]" value="...">`
   - `<input type="hidden" name="field_name[title]" value="...">`
   - `<input type="hidden" name="field_name[target]" value="...">`
   - `<input type="hidden" name="field_name[post_id]" value="...">`

2. A visible display area (`.rwmb-link-display`):
   - Empty state: "Select link" button (triggers wpLink)
   - Filled state: clickable link text with "(opens in new tab)" indicator, plus Edit and Remove buttons

3. The container holds `data-field-name` attribute for JS to construct hidden input names when updating values from the wpLink dialog.

HTML structure (empty state):
```html
<div class="rwmb-link" data-field-name="my_field">
    <input type="hidden" name="my_field[url]" value="">
    <input type="hidden" name="my_field[title]" value="">
    <input type="hidden" name="my_field[target]" value="">
    <input type="hidden" name="my_field[post_id]" value="0">
    <div class="rwmb-link-display">
        <a href="#" class="rwmb-link-select button">Select link</a>
    </div>
</div>
```

HTML structure (filled state):
```html
<div class="rwmb-link" data-field-name="my_field">
    <input type="hidden" name="my_field[url]" value="https://example.com/my-page">
    <input type="hidden" name="my_field[title]" value="My Page Title">
    <input type="hidden" name="my_field[target]" value="_blank">
    <input type="hidden" name="my_field[post_id]" value="42">
    <div class="rwmb-link-display">
        <span class="rwmb-link-text">
            <span class="dashicons dashicons-admin-links"></span>
            <a href="https://example.com/my-page" target="_blank">My Page Title</a>
            <span class="rwmb-link-target"> (new tab)</span>
        </span>
        <a href="#" class="rwmb-link-edit">Edit</a> |
        <a href="#" class="rwmb-link-remove">Remove</a>
    </div>
</div>
```

### `value( $new, $old, $post_id, $field )`

- Sanitize: `wp_kses_post()` on title, `esc_url_raw()` on url, `absint()` on post_id, restrict target to `['_blank', '']`
- If all sub-values are empty/zero, return `[]` (delete meta)
- Return sanitized array

### `format_single_value( $field, $value, $args, $post_id )`

Render an `<a>` tag:
```php
$url    = esc_url( $value['url'] );
$title  = esc_html( $value['title'] );
$target = ! empty( $value['target'] ) ? ' target="_blank"' : '';
return "<a href=\"{$url}\"{$target}>{$title}</a>";
```

### `format_value( $field, $value, $args, $post_id )`

Handle clone vs non-clone:
- Non-clone: delegate to `format_single_value()`
- Clone: loop and concatenate `format_single_value()` calls, separated by newlines

## JavaScript: `js/link.js`

jQuery plugin pattern matching existing Meta Box scripts:

```js
( function( $, wpLink ) {
    // On click of "Select link" or "Edit" button:
    // 1. wpLink.open( editorId )
    // 2. In wpLink.getAttrs() callback:
    //    - Read href, title, target
    //    - Determine post_id: if href is internal, try to extract post ID
    //      via wp_link_query or parse the permalink
    //    - Update hidden inputs
    //    - Update display HTML
    // 3. On "Remove" button: clear hidden inputs, reset display to empty state
} )( jQuery, wpLink );
```

Key behaviors:
- Open wpLink modal on "Select link" or "Edit" click
- Pre-fill modal with current values (set wpLink inputs before opening)
- On submit: read `wpLink.getAttrs()`, populate hidden inputs, update display
- On "Remove": clear all inputs and reset to empty state
- Determine `post_id`: use the `post_id` stored in wpLink's internal data if available (the link dialog stores it when a post is selected). Fall back to `0` for custom URLs.

## CSS: `css/link.css`

Minimal styling:
- `.rwmb-link` — container with padding
- `.rwmb-link-display` — inline display, aligned with other fields
- `.rwmb-link-text` — shows the link text and URL with a link icon
- `.rwmb-link-edit`, `.rwmb-link-remove` — small action links
- `.rwmb-link-target` — muted "(new tab)" indicator

## Clone Support

Works via existing `RWMB_Clone` mechanism. Each clone instance gets:
- Indexed hidden inputs: `field_name[0][url]`, `field_name[1][url]`, etc.
- Each clone has its own "Select link" / display / edit / remove buttons
- JS uses the clone's `data-field-name` to construct correct input names

## Front-End Usage

```php
// Raw array
$link = rwmb_get_field( 'hero_link' );
// ['url' => '...', 'title' => '...', 'target' => '_blank', 'post_id' => 42]

// Formatted as <a> tag
rwmb_the_field( 'hero_link' );
// <a href="https://example.com/my-page" target="_blank">My Page Title</a>

// Manual rendering with raw array
$link = rwmb_get_field( 'hero_link' );
if ( $link ) {
    printf(
        '<a href="%s" target="%s">%s</a>',
        esc_url( $link['url'] ),
        esc_attr( $link['target'] ?: '_self' ),
        esc_html( $link['title'] )
    );
}

// Resolve post_id for linked post
$link = rwmb_get_field( 'hero_link' );
if ( $link && $link['post_id'] ) {
    $post = get_post( $link['post_id'] );
    // Access post data directly
}

// Cloned links
$links = rwmb_get_field( 'related_links' );
foreach ( $links as $link ) {
    printf( '<a href="%s" target="%s">%s</a>', ... );
}
```

## Testing

- Register a link field in a test meta box configuration
- Verify "Select link" button opens the native WP link popup
- Verify selecting a post auto-fills URL, title, and post_id
- Verify entering a custom URL saves correctly with post_id = 0
- Verify "open in new tab" checkbox sets target to `_blank`
- Verify Edit button pre-fills the modal with current values
- Verify Remove button clears the field
- Verify clone support (add/remove/sort clones)
- Verify `rwmb_get_field()` returns the array
- Verify `rwmb_the_field()` renders `<a>` tag
- Verify PHPStan (level 9) passes
- Verify PHPCS passes with WordPress coding standards
