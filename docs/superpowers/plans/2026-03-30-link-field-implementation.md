# Link Field Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- []`) syntax for tracking.

**Goal:** Add a dedicated `link` field type to Meta Box that wraps WordPress's native Insert Link popup (`wpLink`), providing ACF-compatible UX for selecting internal posts or entering custom URLs with title and target support.

**Architecture:** A new `RWMB_Link_Field` class extending `RWMB_Field`. Renders hidden inputs for url/title/target/post_id plus a visible display area with select/edit/remove buttons. JS wraps the WordPress `wpLink` API to open the native link dialog and populate hidden inputs. Saves as a single serialized post meta array. Front-end output via existing `rwmb_the_field()` / `rwmb_get_field()` helpers.

**Tech Stack:** PHP (WordPress plugin), jQuery, wpLink API, CSS

---

## Files

| File | Responsibility |
|------|----------------|
| `inc/fields/link.php` | `RWMB_Link_Field` — field class (normalize, html, value, format) |
| `js/link.js` | jQuery script — open wpLink, read results, populate hidden inputs |
| `css/link.css` | Styling for empty/filled display states |
| `tests/phpunit/LinkTest.php` | PHPUnit tests for value() and format_single_value() |

---

### Task 1: Create the PHP field class

**Files:**
- Create: `inc/fields/link.php`

- [ ] **Step 1: Create the file with class skeleton**

Create `inc/fields/link.php`:

```php
<?php
defined( 'ABSPATH' ) || die;

/**
 * The link field.
 */
class RWMB_Link_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-link', RWMB_CSS_URL . 'link.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-link', 'path', RWMB_CSS_DIR . 'link.css' );
		wp_enqueue_script( 'rwmb-link', RWMB_JS_URL . 'link.js', ['jquery'], RWMB_VER, true );
		wp_enqueue_editor();
	}

	public static function html( $meta, $field ) {
		$meta = wp_parse_args( $meta, [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		] );

		$name = $field['field_name'];

		$output  = '<div class="rwmb-link" data-field-name="' . esc_attr( $name ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[url]" value="' . esc_attr( $meta['url'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[title]" value="' . esc_attr( $meta['title'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[target]" value="' . esc_attr( $meta['target'] ) . '">';
		$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[post_id]" value="' . esc_attr( $meta['post_id'] ) . '">';

		if ( $meta['url'] ) {
			$output .= '<div class="rwmb-link-display">';
			$output .= '<span class="rwmb-link-text">';
			$output .= '<span class="dashicons dashicons-admin-links"></span> ';
			$output .= '<a href="' . esc_url( $meta['url'] ) . '" target="_blank">' . esc_html( $meta['title'] ) . '</a>';
			if ( '_blank' === $meta['target'] ) {
				$output .= ' <span class="rwmb-link-target">' . esc_html__( '(new tab)', 'meta-box' ) . '</span>';
			}
			$output .= '</span> ';
			$output .= '<a href="#" class="rwmb-link-edit">' . esc_html__( 'Edit', 'meta-box' ) . '</a>';
			$output .= ' | ';
			$output .= '<a href="#" class="rwmb-link-remove">' . esc_html__( 'Remove', 'meta-box' ) . '</a>';
			$output .= '</div>';
		} else {
			$output .= '<div class="rwmb-link-display">';
			$output .= '<a href="#" class="rwmb-link-select button">' . esc_html__( 'Select link', 'meta-box' ) . '</a>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	public static function normalize( $field ) {
		$field             = parent::normalize( $field );
		$field['multiple'] = false;
		return $field;
	}

	public static function value( $new, $old, $post_id, $field ) {
		$new = wp_parse_args( $new, [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		] );

		$new['url']     = esc_url_raw( $new['url'] );
		$new['title']   = wp_kses_post( $new['title'] );
		$new['target']  = in_array( $new['target'], ['_blank', ''], true ) ? $new['target'] : '';
		$new['post_id'] = absint( $new['post_id'] );

		$all_empty = empty( $new['url'] ) && empty( $new['title'] ) && empty( $new['post_id'] );
		return $all_empty ? [] : $new;
	}

	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( empty( $value['url'] ) ) {
			return '';
		}
		$url    = esc_url( $value['url'] );
		$title  = esc_html( $value['title'] );
		$target = ! empty( $value['target'] ) ? ' target="' . esc_attr( $value['target'] ) . '"' : '';
		return '<a href="' . $url . '"' . $target . '>' . $title . '</a>';
	}

	public static function format_value( $field, $value, $args, $post_id ) {
		if ( ! $field['clone'] ) {
			return self::format_single_value( $field, $value, $args, $post_id );
		}

		$output = '';
		foreach ( $value as $subvalue ) {
			$output .= self::format_single_value( $field, $subvalue, $args, $post_id ) . "\n";
		}
		return $output;
	}
}
```

- [ ] **Step 2: Verify autoloader picks up the field**

The autoloader at `inc/autoloader.php` maps `RWMB_{TitleCase}_Field` to `inc/fields/{lowercase-hyphenated}.php`. So `RWMB_Link_Field` → `inc/fields/link.php`. No changes needed.

Verify by checking: does the autoloader have the prefix/suffix pattern? (Yes — `RWMB_` prefix, `_Field` suffix, `inc/fields/` directory.)

---

### Task 2: Create the JavaScript

**Files:**
- Create: `js/link.js`

- [ ] **Step 1: Write `js/link.js`**

```js
( function( $, wpLink ) {
	'use strict';

	$( document ).on( 'click', '.rwmb-link-select, .rwmb-link-edit', function( e ) {
		e.preventDefault();

		var $link  = $( this ).closest( '.rwmb-link' ),
			$url   = $link.find( 'input[name$="[url]"]' ),
			$title = $link.find( 'input[name$="[title]"]' ),
			$target = $link.find( 'input[name$="[target]"]' );

		wpLink.open( 'rwmb-link-wp-editor', $url.val(), $target.val() === '_blank' ? '1' : '', $title.val() );

		// Close handler to capture link.
		$( '#wp-link-submit' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', function() {
			var attrs = wpLink.getAttrs();
			if ( ! attrs.href ) {
				return;
			}

			$url.val( attrs.href );
			$title.val( $( '#wp-link-text' ).val() || attrs.href );
			$target.val( attrs.target === '_blank' ? '_blank' : '' );

			// Try to extract post_id from wpLink internal data.
			var postData = $( '#wp-link-wrap' ).data( 'selectedPost' );
			$link.find( 'input[name$="[post_id]"]' ).val( postData && postData.ID ? postData.ID : 0 );

			// Update display.
			var name = $link.data( 'field-name' ),
				titleVal = $title.val(),
				targetVal = $target.val(),
				display = '';

			display += '<span class="rwmb-link-text">';
			display += '<span class="dashicons dashicons-admin-links"></span> ';
			display += '<a href="' + attrs.href + '" target="_blank">' + titleVal + '</a>';
			if ( targetVal === '_blank' ) {
				display += ' <span class="rwmb-link-target">(new tab)</span>';
			}
			display += '</span> ';
			display += '<a href="#" class="rwmb-link-edit">Edit</a> | ';
			display += '<a href="#" class="rwmb-link-remove">Remove</a>';

			$link.find( '.rwmb-link-display' ).html( display );
			wpLink.close();
		} );

		$( '#wp-link-cancel' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', function() {
			wpLink.close();
		} );
	} );

	$( document ).on( 'click', '.rwmb-link-remove', function( e ) {
		e.preventDefault();

		var $link = $( this ).closest( '.rwmb-link' );
		$link.find( 'input[name$="[url]"]' ).val( '' );
		$link.find( 'input[name$="[title]"]' ).val( '' );
		$link.find( 'input[name$="[target]"]' ).val( '' );
		$link.find( 'input[name$="[post_id]"]' ).val( 0 );
		$link.find( '.rwmb-link-display' ).html( '<a href="#" class="rwmb-link-select button">Select link</a>' );
	} );

} )( jQuery, wpLink );
```

- [ ] **Step 2: Verify wpLink API**

Research the WordPress wpLink API:
- `wpLink.open( editorId, url, target, title )` — opens the modal pre-filled
- `wpLink.getAttrs()` — returns `{ href, target, title }` after submission
- `wpLink.close()` — closes the modal

The `editorId` parameter is used internally by wpLink — we pass a unique ID so it doesn't conflict with the post editor's link dialog.

- [ ] **Step 3: Test post_id extraction**

The wpLink modal internally tracks selected post data. The approach is:
- When a post is selected in the link search, wpLink stores it in `$('#wp-link-wrap').data('selectedPost')`
- If that's not available, fall back to `0` (custom URL)
- This may need adjustment during implementation if the internal API differs across WP versions

---

### Task 3: Create the CSS

**Files:**
- Create: `css/link.css`

- [ ] **Step 1: Write `css/link.css`**

```css
.rwmb-link {
	padding: 5px 0;
}

.rwmb-link-display {
	display: flex;
	align-items: center;
	gap: 8px;
}

.rwmb-link-text {
	flex: 1;
}

.rwmb-link-text .dashicons {
	color: #999;
	vertical-align: middle;
}

.rwmb-link-text a {
	text-decoration: underline;
}

.rwmb-link-target {
	color: #999;
	font-style: italic;
}

.rwmb-link-edit,
.rwmb-link-remove {
	text-decoration: none;
}

.rwmb-link-select {
	text-decoration: none;
}
```

---

### Task 4: Write PHPUnit tests

**Files:**
- Create: `tests/phpunit/LinkTest.php`

- [ ] **Step 1: Create test file**

Create `tests/phpunit/LinkTest.php`:

```php
<?php
class LinkTest extends WP_UnitTestCase {
	public function testNormalize() {
		$field = RWMB_Link_Field::normalize( [
			'type' => 'link',
			'id'   => 'my_link',
			'name' => 'My Link',
		] );

		$this->assertFalse( $field['multiple'] );
		$this->assertEquals( 'link', $field['type'] );
	}

	public function testValueSanitizes() {
		$new = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '_blank',
			'post_id' => '42',
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( 'https://example.com/test', $result['url'] );
		$this->assertEquals( 'Test Link', $result['title'] );
		$this->assertEquals( '_blank', $result['target'] );
		$this->assertEquals( 42, $result['post_id'] );
	}

	public function testValueReturnsEmptyForEmptyInputs() {
		$new = [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( [], $result );
	}

	public function testValueRejectsInvalidTarget() {
		$new = [
			'url'     => 'https://example.com',
			'title'   => 'Link',
			'target'  => 'invalid',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( '', $result['target'] );
	}

	public function testFormatSingleValue() {
		$value = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '_blank',
			'post_id' => 42,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '<a href="https://example.com/test" target="_blank">Test Link</a>', $result );
	}

	public function testFormatSingleValueNoTarget() {
		$value = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '<a href="https://example.com/test">Test Link</a>', $result );
	}

	public function testFormatSingleValueEmptyUrl() {
		$value = [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '', $result );
	}
}
```

- [ ] **Step 2: Run tests**

Run: `./vendor/bin/phpunit tests/phpunit/LinkTest.php`

Expected: All tests pass.

---

### Task 5: Verify lint and static analysis

- [ ] **Step 1: Run PHPCS**

Run: `composer phpcs`

Expected: No errors on the new files. Fix any coding standard issues (indentation, spacing, docblocks).

- [ ] **Step 2: Run PHPStan**

Run: `composer phpstan`

Expected: No new errors. (PHPStan only analyzes `inc/` directory.)

---

### Task 6: Manual testing

- [ ] **Step 1: Test in WordPress admin**

1. Register a link field in a test meta box:
   ```php
   add_filter( 'rwmb_meta_boxes', function( $meta_boxes ) {
       $meta_boxes[] = [
           'title'  => 'Test Link Field',
           'fields' => [
               [
                   'type' => 'link',
                   'id'   => 'test_link',
                   'name' => 'Test Link',
               ],
               [
                   'type'  => 'link',
                   'id'    => 'test_link_cloned',
                   'name'  => 'Test Link (Cloned)',
                   'clone' => true,
               ],
           ],
       ];
       return $meta_boxes;
   } );
   ```

2. Edit a post and verify:
   - "Select link" button appears for empty fields
   - Clicking opens the native WP link popup
   - Selecting a post fills URL, title, and post_id
   - Entering a custom URL works
   - "Open in new tab" checkbox sets target to `_blank`
   - Edit button pre-fills the modal
   - Remove button clears the field
   - Clone add/remove works correctly

3. Save the post and verify:
   - Values persist after save
   - Values display correctly on reload

- [ ] **Step 2: Test front-end helpers**

In theme template:
```php
$link = rwmb_get_field( 'test_link' );
var_dump( $link ); // Should show array with url, title, target, post_id

rwmb_the_field( 'test_link' ); // Should render <a> tag
```

Verify correct output for both post-linked and custom URL cases.

---

### Task 7: Final commit

- [ ] **Step 1: Commit all files**

```bash
git add inc/fields/link.php js/link.js css/link.css tests/phpunit/LinkTest.php
git commit -m "feat: add link field type with native wpLink integration"
```
