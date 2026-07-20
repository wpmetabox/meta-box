=== Meta Box ===
Contributors: elightup, metabox, rilwis, f-j-kaiser, funkatronic, PerWiklander, ruanmer, tanng
Donate link: https://metabox.io/pricing/
Tags: custom fields, custom post types, post type, custom taxonomies, meta box
Requires at least: 6.7
Requires PHP: 7.4
Tested up to: 7.0.1
Stable tag: 5.13.1
License: GPLv2 or later

Meta Box plugin is a powerful, professional developer toolkit to create custom meta boxes and custom fields for your custom post types in WordPress.

== Description ==

You want to add events, team members, testimonials, or listings to your WordPress site. But the standard editor only handles posts and pages. You need something more, but you don't want to overcomplicate things or slow down your site.

**Meta Box** gives you everything you need to build dynamic WordPress sites - with over **40+ field types** for adding custom fields to posts, pages, custom post types, taxonomies, settings pages, users, and comments. It's **lightweight** (no bloat), **flexible** (works with any theme or plugin), and **fast** (uses native WordPress storage). No lock-in, no extra baggage.

### Any type of custom fields, anywhere

Add custom fields to any part of WordPress:

- **Posts & Pages** - Add extra fields to regular content
- **Custom post types** - Use with our free [CPT UI plugin](https://metabox.io/plugins/custom-post-type/) to create custom post types and taxonomies
- **Taxonomies** - Categories, tags, and custom taxonomies via [MB Term Meta](https://metabox.io/plugins/mb-term-meta/)
- **Settings pages** - Theme/plugin options via [MB Settings Page](https://metabox.io/plugins/mb-settings-page/)
- **Users** - Profile fields via [MB User Meta](https://metabox.io/plugins/mb-user-meta/)
- **Comments** - Comment fields via [MB Comment Meta](https://metabox.io/plugins/mb-comment-meta/)

### 40+ field types (and you can add your own)

Meta Box ships with **40+ built-in field types**: text, textarea, WYSIWYG, image, file upload, post select, checkbox, radio, date/time picker, taxonomy, user, oEmbed, and more. You can also [create custom field types](https://docs.metabox.io/custom-field-type/). Most fields support **cloning** and **repeatable groups**.

### Developer-friendly by design

- **Lightweight API** - Won't bloat your site
- **Modular** - Add only what you need
- **Native storage** - Uses WordPress meta tables by default for speed and compatibility, and can be extended to [custom tables](https://metabox.io/plugins/mb-custom-table/) for advanced setups
- **Composer support** - Integrates with modern PHP workflows
- **Hooks** - Extensive [actions](https://docs.metabox.io/category/actions/) and [filters](https://docs.metabox.io/category/filters/) for customization
- **Integration-ready** - Works with any theme or plugin

### Get more with Meta Box

[**Meta Box Lite**](https://metabox.io/lite/) is the UI version of Meta Box that helps you manage everything visually - custom fields, post types, taxonomies, and more - without touching code.

[**Meta Box AIO**](https://metabox.io/pricing/) is an all-in-one plugin that bundles all free and premium extensions, giving you everything from conditional logic to frontend submissions, custom tables to Gutenberg blocks - all in one package.

### Documentation

Full [documentation](https://docs.metabox.io) and [tutorials](https://docs.metabox.io/tutorials/) are available to get you started:

- [Introduction](https://docs.metabox.io/introduction/)
- [Custom post types](https://docs.metabox.io/custom-post-types/)
- [Custom fields](https://docs.metabox.io/custom-fields/)
- [Field settings](https://docs.metabox.io/field-settings/)
- [Displaying fields](https://docs.metabox.io/displaying-fields-with-code/)

### You might also like

If you like this plugin, you might also like our other WordPress products:

- [Slim SEO](https://wpslimseo.com) - A fast, lightweight and full-featured SEO plugin for WordPress with minimal configuration.
- [Falcon](https://wpfalcon.pro) - A lightweight companion for making WordPress faster, cleaner, and more secure.
- [GretaThemes](https://gretathemes.com) - Free and premium WordPress themes that clean, simple and just work.
- [Auto Listings](https://wpautolistings.com) - A car sale and dealership plugin for WordPress.

== Installation ==

To install Meta Box:

1. Visit **Plugins > Add New** inside your WordPress dashboard
1. Search for **Meta Box**
1. Click the **Install Now** button to install the plugin
1. Click the **Activate** button to activate the plugin

[Get started](https://docs.metabox.io/introduction/).

== Frequently Asked Questions ==

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the Meta Box – WordPress Custom Fields Framework plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/9e5fbeb8-4b92-420d-9aa3-2de53ed433fe). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Screenshots ==
1. Text Fields
1. Basic Fields
1. Advanced Fields
1. File Image Upload Fields
1. Media Fields
1. Post Taxonomy Fields

== Changelog ==

= 5.13.1 - 2026-07-14 =

- Fix missing authorization check in `ajax_delete_file` for enhanced security
- Allow HTML in switch on/off and button group labels (e.g., Dashicons)

= 5.13.0 - 2026-07-06 =

**Highlights:**

This release introduces **Abilities**, enabling you to manage custom post types and taxonomies, as well as create, retrieve, update, and delete posts and terms.

See our [blog post](https://metabox.io/introducing-abilities/) for an overview or the [documentation](https://docs.metabox.io/abilities/) for usage details.

**Other changes:**

- Fix unable to scroll in full screen mode for the `block_editor` field (#1689)
- Use `WP_Query`'s `search_columns` instead of custom `search_by_title` filter
- Fix autocomplete dropdown z-index in map/osm field inside MB Blocks

= 5.12.1 - 2026-06-10 =

- Update style to match WordPress 7
- Fix wp_style_add_data issue for OSM field
- Fix: prevent invalid JSON in media field data attributes
- Fix: escape $object->label with esc_html() in input-list walker

= 5.12.0 - 2026-04-22 =

- New field type `link` that allows you to add a link with native WordPress experience (similar to ACF)
- Auto add child blocks for allowed blocks for the `block_editor` field

= 5.11.4 - 2026-03-30 =

- Add an internal hook for enqueuing custom blocks' assets for `block_editor` field

= 5.11.3 - 2026-03-24 =

- Add `toolbar_position` option for the block editor field, which accepts value `top` (default) or `contextual`. This option is for where to display editing toolbar for blocks.
- Fix saving an empty paragraph in the block editor field
- Fix validation error persisting after removing duplicate blocks
- Fix extra empty clone saved when calling `set_post_data()` during validation

= 5.11.2 - 2026-03-05 =

**Improvements for the block editor field:**

- Add breadcrumbs
- Fix compatibility with Block Visibility plugin
- Fix not loading 3rd-party blocks
- Improve the CSS

**Other changes:**

- Fix save time format for the datetime field
- Fix icon field dropdown broken display when SVG contains double quotes
- Fix path traversal in `ajax_delete_file` for security
- Fix timestamp should not be set for the time picker field

= 5.11.1 - 2026-02-02 =

**Improvements for the block editor field:**

- Add block inspector sidebar
- Add structure panel to show the list view of blocks
- Add fullscreen mode
- Add `height` settings (default is `300px`) and allow resizing the editor

**Fixes for the block editor field:**

- Fix cannot upload images for the image block
- Fix blank site editor when using the block editor field
- Fix custom rich text formats not working
- Fix conflicts with `image_advanced` and `file_advanced` fields

**Other changes:**

- Fix cannot create new terms with required date/time fields

= 5.11.0 - 2026-01-15 =

- Add new field type: `block_editor`. See more details on our [blog post](https://metabox.io/block-editor-field-type/) and [documentation](https://docs.metabox.io/fields/block-editor/).

= 5.10.19 - 2025-11-24 =

- Fix the `use` statement with non-compound name has no effect

= 5.10.18 - 2025-11-24 =

- Fix deprecation message for `datetime` field

= 5.10.17 - 2025-11-07 =

- WPML integration: fix error when filtering value for helper functions when no fields are found.

= 5.10.16 - 2025-11-05 =

- WPML integration: filter helper functions to get the translated IDs for `post` field
- Fix cloning `post` field not clearing the value

= 5.10.15 - 2025-10-06 =

- Add `marker_draggable` option for `map`/`osm` fields to disable changing the pin on the map.

= 5.10.14 - 2025-09-15 =

- Update dependencies

= 5.10.13 - 2025-08-14 =

- Fix `get_current_screen()` error for term meta

= 5.10.12 - 2025-08-13 =

- Fix Open Street Maps field not showing (sometimes) with conditional logic
- Enqueue assets for the iframed editor, to make all fields are rendered properly in the iframed editor

= 5.10.11 - 2025-07-15 =

Fix validation for blocks

= 5.10.10 - 2025-05-21 =
- Fix datetime field returns null
- Fix single image field not working with Polylang Pro
- Fix reveal password icon not working

= 5.10.9 - 2025-05-08 =
- Add button to toggle password (#1630)
- Add gesture handling support for OSM field (#1631)
- Datetime & select2: use user's locale instead of site's locale

= 5.10.8 - 2025-03-14 =
- Redesign the dashboard

= 5.10.7 - 2025-02-25 =
- Fix: max clone with clone empty start

= 5.10.6 - 2025-01-11 =
- Fix validation for dash ids
- Fix datetime fields not showing inline picker inside groups
- Fix `label_description` not working for `fieldset_text`
- Fix field label div still show when no field name but with `label_description`
- Remove `image_select` field's JS, styling with CSS only
- Add gap for key value inputs

= 5.10.5 - 2024-12-16 =
- Make validation for add new terms work
- Improve styling of meta boxes on the sidebar in the block editor
- Improve style of input with prepend and append
- Fix maps/osm fields and geolocation not working with subfields in groups
- Fix select advanced becomes normal select with cloneable setting
- Fix validation

= 5.10.4 - 2024-11-20 =
- OSM/Map fields: add support for select field type
- Add `$url` to `rwmb_oembed_not_available_string` filter
- Small CSS improvements for file input, background & button group
- Fix not returning value for helper functions for images saving in a custom folder and using a custom table

= 5.10.3 - 2024-10-30 =
- fix: std after saving
- fix: field set text save empty values
- fix: subfield's id

= 5.10.2 - 2024-09-26 =
- Fix issue with conditional logic
- Fix error when removing default taxonomy meta box in the front end or with the block editor
- Fix missing .hidden on the front end for `checkbox_tree`
- Fix required attribute for `select_tree`

= 5.10.1 - 2024-09-07 =
- Fix issue with `clone_empty_start` (validation, now showing data for the 1st clone, broken `text_list` field, etc.)

[See full changelog here](https://metabox.io/changelog/).

== Upgrade Notice ==
