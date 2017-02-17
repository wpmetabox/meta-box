=== Meta Box ===
Contributors: metabox, rilwis, fitwp, f-j-kaiser, funkatronic, PerWiklander, ruanmer, Omnicia
Donate link: http://paypal.me/anhtnt
Tags: meta-box, custom fields, custom field, meta, meta-boxes, admin, advanced, custom, edit, field, file, image, magic fields, matrix, more fields, Post, repeater, simple fields, text, textarea, type, cms, fields post
Requires at least: 4.1
Tested up to: 4.7.2
Stable tag: 4.10.3
License: GPLv2 or later

Meta Box plugin is a powerful, professional developer toolkit to create custom meta boxes and custom fields for WordPress.

== Description ==

**Meta Box plugin is a powerful, professional toolkit for developers to create and handle everything related to custom meta boxes and custom fields for WordPress.**

The plugin provides a **wide range of field types** and **a lot of options to for each field type**, which gives you unlimited possibility to control and customize the custom fields.

With the extensions, you can easily build meta boxes not only for custom post types (default), but also for **settings page, user meta, term meta**. You can also display the fields the way you want with columns, tabs or groups.

The plugin is built mostly for developers with a little coding, but if you prefer GUI for faster creating custom post types, meta boxes and custom fields, the plugin has extensions for that: [MB Custom Post Type](https://wordpress.org/plugins/mb-custom-post-type/) and [Meta Box Builder](https://metabox.io/plugins/meta-box-builder/).

### Features

* Create custom meta boxes for posts, pages or any custom post type.
* Create custom [settings pages or theme option page](https://metabox.io/plugins/mb-settings-page/).
* Create custom meta boxes for [user profile pages](https://metabox.io/plugins/mb-user-meta/).
* Create custom meta boxes for [taxonomy terms](https://metabox.io/plugins/mb-term-meta/).
* Supports 40+ built-in [field types](https://metabox.io/docs/define-fields/) for all your needs (text, textarea, wysiwyg/editor, image, file, post, select, checkbox, radio buttons, date time picker, taxonomy, user, oembed and more to come!). You can also [create your own field type](https://metabox.io/docs/create-field-type/) easily.
* Support cloning (repeatable) fields for most field types, including WYSIWYG/editor field. Also support [repeatable field groups](https://metabox.io/plugins/meta-box-group/).
* Powerful [actions and filters](https://metabox.io/docs-category/reference/) that developers can build or change the appearance and behavior in the plugin.
* Uses the [native WordPress meta data storage](https://metabox.io/docs/how-post-meta-is-saved-in-the-database/) and functions for ease of use and fast processing.
* [Easily integrate with themes and plugins](https://metabox.io/docs/include-meta-box-plugin-themes/).
* [Works with Composer](https://metabox.io/docs/using-meta-box-composer/) if you want to include the plugin in your project.
* Compatible with WPML multilingual plugin (officially supported by WPML team).

### Documentation

- [Getting Started](https://metabox.io/docs/getting-started/)
- [Register Meta Boxes](https://metabox.io/docs/registering-meta-boxes/)
- [Define Fields](https://metabox.io/docs/define-fields/)
- [Get Meta Value](https://metabox.io/docs/get-meta-value/)

See more documentation [here](https://metabox.io/docs/).

### Extensions

- [MB User Meta](https://metabox.io/plugins/mb-user-meta/): Add custom fields to user profile (user meta) quickly with simple syntax.
- [Meta Box Geolocation](https://metabox.io/plugins/meta-box-geolocation/): Automatically and instantly populate location data with the power of Google Maps Geolocation API.
- [MB Admin Columns](https://metabox.io/plugins/mb-admin-columns/): Display custom fields in table columns in admin screens for All Posts (types).
- [MB Term Meta](https://metabox.io/plugins/mb-term-meta/): Add meta data to categories, tags or any custom taxonomy with simple syntax.
- [MB Settings Page](https://metabox.io/plugins/mb-settings-page/): Create settings pages for themes, plugins or websites with beautiful syntax.
- [MB Custom Post Type](https://wordpress.org/plugins/mb-custom-post-type/): Create and manage custom post types and taxonomies easily in WordPress with an easy-to-use interface.
- [Meta Box Yoast SEO](https://wordpress.org/plugins/meta-box-yoast-seo/): Add content of custom fields to Yoast SEO Content Analysis to have better/correct SEO score.
- [Meta Box Text Limiter](https://wordpress.org/plugins/meta-box-text-limiter/): Limit the number of characters or words entered for text and textarea fields.
- [Meta Box Conditional Logic](https://metabox.io/plugins/meta-box-conditional-logic/): Add visibility dependency for custom meta boxes and custom fields in WordPress.
- [Meta Box Group](https://metabox.io/plugins/meta-box-group/): Create repeatable groups of custom fields for better appearance and structure.
- [Meta Box Builder](https://metabox.io/plugins/meta-box-builder/): Create custom meta boxes and custom fields in WordPress using the drag-and-drop interface.
- [Meta Box Template](https://metabox.io/plugins/meta-box-template/): Define custom meta boxes and custom fields easier with templates.
- [Meta Box Tooltip](https://metabox.io/plugins/meta-box-tooltip/): Display help information for fields using beautiful tooltips.
- [Meta Box Show Hide (Javascript)](https://metabox.io/plugins/meta-box-show-hide-javascript/): Toggle meta boxes by page template, post format, taxonomy (including category) via Javascript.
- [Meta Box Tabs](https://metabox.io/plugins/meta-box-tabs/): Create tabs for meta boxes easily. Support 3 WordPress-native tab styles and tab icon.
- [Meta Box Columns](https://metabox.io/plugins/meta-box-columns/): Display fields more beautiful by putting them into 12-columns grid.
- [Meta Box Include Exclude](https://metabox.io/plugins/meta-box-include-exclude/): Show/hide meta boxes by ID, page template, taxonomy or custom function.

See all extensions [here](https://metabox.io/plugins/).

### Plugin Links

- [Project Page](https://metabox.io)
- [Documentation](https://metabox.io/docs/)
- [Report Bugs/Issues](https://github.com/rilwis/meta-box/issues)
- [Premium Extensions](https://metabox.io)

== Installation ==

1. Unzip the download package
1. Upload `meta-box` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

To getting started with the plugin API, please read [this tutorial](https://metabox.io/docs/getting-started/).

== Frequently Asked Questions ==

== Screenshots ==
1. Text Fields
1. Basic Fields
1. Advanced Fields
1. File Image Upload Fields
1. Media Fields
1. Post Taxonomy Fields

== Changelog ==

= 4.10.3 =
* Fix: `force_delete` causes files to be deleted even when set to `false`.
* Fix: `max_file_uploads` not working.

= 4.10.2 =
* Improvement: Add `max_file_size` to upload fields (`File_Upload`, `Image_Upload`, `Plupload_Image`).
* Improvement: Add support for attributes for file input.
* Improvement: Update Polish translation.
* Improvement: Add translation support for Key and Value strings (@prop saqibsarwar).
* Fix: Shorter width of email field vs. other fields (@prop saqibsarwar).
* Fix: Fix cloneable datetime field with timestamp=true.
* Fix: Remove margin bottom in select_advanced options.
* Fix: Showing the correct selected value for select_advanced field when the option value contains '&' character.
* Fix: Fix default values not working with taxonomy and taxonomy_advanced fields.

= 4.10.1 =
* Fix: Fix `image_upload` and `plupload_image` field when select images to upload.

= 4.10 =
* Improvement: Add `video` field type which allows users to upload or select video from the Media Library. See [demo](https://github.com/rilwis/meta-box/blob/master/demo/video.php).
* Improvement: Update Turkish. Prop Emre Tuna <tunaemre@windowslive.com>.
* Improvement: Use WP 4.6 date picker localization instead of JS localized file to reduce plugin size.
* Improvement: Refactor the media fields for better performance. Add `change` event for item list when add, remove or reset.
* Fix: `taxonomy_advanced` field now can be cloned.
* Fix: Make localize_script works with WP 4.1.

= 4.9.8 =
* Fix: Quick fix for enqueueing validation script

= 4.9.7 =
* Improvement: Re-add change event on media fields to make Conditional Logic extension works with media fields.
* Improvement: Add `rwmb_choice_label`, `rwmv_{$field_type}_choice_label` and `rwmb_{field_id}_choice_label` filters for post, user, taxonomy fields, allowing users to customize the labels of choice fields.
* Improvement: Change coding styles to follow WordPress Coding Standards.
* Various improvements to reduce duplicated code.
* Fix: Map field now works in the frontend.
* Fix: `std` now works for taxonomy fields.

= 4.9.6 =
* Fix: Wrong CSS selector when cloning wysiwyg field
* Fix: Remove preview for oembed field when cloning
* Fix: 'std' for taxonomy field now works

= 4.9.5 =
* Fix: Quick fix for wrong field wrapper class which causes color field to render incorrectly

= 4.9.4 =
* Fix: Quick fix for cloning bug

= 4.9.3 =
* Fix: Quick fix saving datetime field

= 4.9.2 =
* Fix: Quick fix validation

= 4.9.1 =
* Fix: Quick fix for `rwmb_meta()` to be better backward compatible

= 4.9 =
* Improvement: Update Chinese language. Add Chinese Taiwan.
* Improvement: Add support for Google Maps API key. Default API key is added, however users should replace it with their own key.
* Improvement: Add additional methods for jQuery validation module which makes it understand HTML5 "pattern" attribute.
* Improvement: Fully WPML compatibility (supported by WPML team)
* Improvement: Add placeholders for `key_value` field
* Fix: Toggle remove clone buttons for nested groups.
* Fix: Error on date field, not save
* Fix: Add fix for date/datetime when both inline and timestamp used
* Fix: Set default language for date/time picker.
* Fix: rwmb_meta for images returns incorrect width/height
* Fix: PHP warning when uploading files in Settings Pages extension.
* Fix: Blank space in the footer when using plupload_image.
* Fix: Cloning wysiwyg when deleting the 1st clone

= 4.8.7 =
* Improvement: Refactor the code to reduce the complexity in the fields' inheritance
* Improvement: All HTML 5 input types (week, month, etc.) are supported
* Improvement: Optimize the_value function, use recursive call to reduce nested loop. Sub-fields need to define format_single_value only.
* Improvement: Use 1 single localization file for jQuery date picker for simplicity
* Improvement: Add support for custom marker for map field (param `marker_icon`) in rwmb_meta function
* Improvement: Add `limit` option for media fields in `rwmb_meta` function when retrieving meta value.
* Improvement: Add `rwmb_option_label` filter for choice fields (user, post, taxonomy) so users can choose which object field is used as label
* Improvement: Use `WP_User_Query` for user field which supports more options for querying
* Improvement: Optimize code for oembed, also use esc_html__ for better security
* Improvement: Compatibility with Meta Box Geolocation
* Fix: Fix first option is auto selected in select_advanced field.
* Fix: Fix clone issue for color in Meta Box Group extension.
* Fix: Fix clone issue for image advanced in Meta Box Group extension.
* Fix: Fix not parsing $args to array in helper functions.

= 4.8.6 =
* Improvement: Edit link on media items now opens edit modal
* Improvement: Refresh map when sorting meta boxes.
* Improvement: Wrap checkbox's description into a <label> to make it clickable to activate/deactivate the checkbox.
* Improvement: Remove Spanish language (ES) as it's already translated on translate.wordpress.org
* Improvement: Add support for saving zoom in map
* Improvement: Prevent output localized strings twice.
* Improvement: Add fallback for autoload in PHP 5.2 in case it's disabled.
* Improvement: No need to json_encode for custom attributes. User can pass an array to custom attribute
* Improvement: Add style for `select2` library to match WordPress admin style
* Improvement: Adds min width to select. @prop ahmadawais
* Improvement: Added `max_status` option for media type fields. `true` to show status, `false` to hide
* Improvement: Add attachment meta data to file info
* Fix: Validation for non-Meta Box fields
* Fix: advanced_image field after reload page F5 in Firefox
* Fix: Cannot read property 'getFullYear' of null
* Fix: Empty date converting to 0
* Fix: Add missing class for image_select field which prevents setting input's name when cloning.
* Fix: Fix bug with blank maps on the front end
* Fix: Fix bug with cloning media fields
* Fix: Remove empty values in clones and reset index.
* Fix: Reset of cloned select fields
* Fix: select_advanced with multiple=true adds empty selected option
* Fix: No empty option for simple select field
* Fix: Empty datetime field with timestamp => true returns January 1, 1970
* Fix: For color picker when using with Columns extension
* Fix: Fix bug with taxonomy advanced returns all taxonomy items for posts with no meta saved
* Fix: Fix bug with taxonomy advanced not saving value when field isn't multiple
* Fix: Make radio inline again
* Fix: Wrong meta value when using helper function outside the loop
* Fix: Validation now works for hidden elements in tabs

= 4.8.5 =
* Improvement: Add localization for Select2 library
* Improvement: Range preview output added
* Improvement: Add Persian translation and nag fix
* Fix: Map has no refresh in collapsed meta boxes
* Fix: Fix incorrect URL if the plugin is symlinked.
* Fix: Added fix for saved order in object-choice

= 4.8.4 =
* Improvement: Refactor code for plupload_image. Introduces file_upload and image_upload field which acts the same as plupload_image but for files and images.
* Improvement: Do not show "Embed is not available" if fields don't have any value
* Improvement: Refactor date/time related fields. 'timestamp' now works for date field as well.
* Improvement: Add 'inline' mode for date/datetime fields.
* Improvement: Add option 'select_all_none' for select/select2 with default = false
* Fix: users now can register 2 meta boxes with same field IDs for different post types.
* Fix: width of embeded video if $content_width is too large.
* Fix: autoloader now works more safely.
* Fix: post field doesn't show correct post link
* Fix: select field must call field's get_value to get field's value as 'select' is used in many non-inherited classes
* Fix: Allows old syntax for `query_args.post_types` for post/user/taxonomy fields
* Fix: Do not reset value for hidden field when clone
* Fix: Missing Insert into Post button for thickbox_image field
* Fix: Date picker cut off by TinyMCE
* Fix: CSS for multi months in date picker

= 4.8.3 =
* Improvement: WYSIWYG field now can be cloned. Sorting clone hasn't worked yet.
* Fix: 'std' value not working if there is 'divider' or 'heading' field withough 'id'
* Fix: helper function not working in AJAX or admin.
* Fix: getting plugin's path on Windows system.
* Fix: warning get_value of taxonomy field
* Fix: guarantee file ids are in an array

= 4.8.2 =
* Fix: re-add code for backward compatibility for helper function
* Fix:  undefined 'class' attribute for button
* Improvement: speedup the helper function

= 4.8.1 =

* Fix: select multiple value with post, user and taxonomy
* Fix: bug in oembed field
* Fix: fix JS/CSS compatibility with WooCommerce
* Fix: do not force field ID to lowercase, which can potentially breaks existing fields or fields with ID of CAPS characters.

= 4.8.0 =

* Improvement: rewrite the way the plugin loads file, which allows developers to include the plugin into themes/plugins simply by include the main file. The new loading mechanism also uses autoloading feature which prevents loading files twice and saves memory.
* Improvement: rewrite `user`, `post`, `taxonomy` fields using the same codebase as they're native WordPress objects and sharing similar options. Also changes the syntax of query parameters for these fields (old syntax still works). Please see docs for details.
* Improvement: add `srcset` in the returned value of helper function for image fields
* Improvement: better sanitize value for `url` field
* Improvement: prevent issues with dashes in field types
* Improvement: remove redundant value in checkbox
* Improvement: update CSS for date, time fields
* Improvement: select2 now updated to 4.0.1
* Improvement: optimize code for `file_advanced` and `image_advanced` fields which now submit array of values when saving instead of single CSV value
* Improvement: add `collapse` option to `checkbox_list` and `checkbox_tree` in `user`, `taxonomy`, `post` fields which prevents plugin save parent values.
* Improvement: secure password field so it is no longer saved in plain text. To check if a password matches the hash, please use `wp_check_password`.
* Improvement: change the output of `color` field in the helper function. Now it shows the color instead of hex value.
* Improvement: add `color:change` and `color:clear` JavaScript event for detecting changes in `color` field.
* Improvement: refactor code for better structure and security
* Fix: rewrite the JavaScript for cloning which causes bugs for date field.
* Fix: fix missing attributes if value is '0' or 0.
* Fix: add missing `class` attribute for fields
* Fix: do not auto populate color field with '#'
* Fix: wrong callback for fix page template


= 4.7.3 =

* Improvement: add `change` event for `file_advanced` and `image_advanced` fields.
* Improvement: add support for boolean attributes.
* Improvement: add support for boolean attributes.
* Improvement: add Russian language.
* Improvement: changed `wp_get_post_terms` to `get_the_terms` to use WordPress cache.
* Improvement: refactored code to make textarea, select use attributes.
* Improvement: `fieldset_text` now cloneable. Also removed `rows` option for this field.
* Improvement: refactored `has_been_saved()` function.

= 4.7.2 =

* Fix: notice undefined index in date, time fields.

= 4.7.1 =

* Fix: remove default `maxlength = 30` for text fields.

= 4.7 =

* Improvement: add `attributes` for all input fields (text, number, email, ...) so users can add any custom attributes for them. Also added default attributes `required`, `disabled`, `readonly`, `maxlength` and `pattern` for those fields as well. These attributes will be merged into the `attributes`.
* Improvement: add `js_options` for color field which allows users to define custom color palettes and other attributes for color picker. See the options in [Iris page](http://automattic.github.io/Iris/).
* Fix: fix for file and image uploaded via `file_advanced` and `image_advanced` not showing up.

= 4.6 =

* Improvement: the plugin translation is now handled in translate.wordpress.org. While the plugin keeps old translation as backward compatibility, it's recommended to translate everything in translate.wordpress.org. Language packs will be automatically updated by WordPress.
* Improvement: rewrite code for `file_advanced` and `image_advanced`, which share the same code base. These fields are now clonable and not autosave (you have to save post to save files)! Props @funkatronic.
* Improvement: restyle clone icon, sort clone icon and add clone button for better UI. The new UI now is compatible with `color` and `date` fields
* Improvement: separate validation module into 1 class, for better code structure
* Improvement: add `pattern` attribute for `url` field
* Improvement: improve code quality
* Fix: missing "checked" when clone radio
* Fix: language file name for Dutch
* Fix: oembed not render preview if provider is added via `wp_embed_register_handler`

= 4.5.7 =
* Fix: Always set std as value for hidden field
* Fix: `rwmb_meta` now can display rich content from `oembed` field
* Fix: Wrong format for `datetime` field
* Fix: Check and reset clone index when add/remove/sort clones
* Improvement: Optionally display ID attribute for heading and divider
* Improvement: Adding new style to date field to match WordPress style
* Improvement: Change saving hooks to specific post types to prevent saving images to wrong post

= 4.5.6 =
* Fix: Warning for timestamp for datetime field.
* Fix: z-index for color picker.
* Fix: Marker title in map

= 4.5.5 =
* Fix: CSS alignment for sort clone icon for field type `group` (require Meta Box Group extension)
* Fix: rwmbSelect is not defined

= 4.5.4 =
* Improvement: Add "Select All|None" for `select`, `select_advanced`, `post` fields
* Improvement: Add `max_clone` parameter which limits number of clones
* Improvement: Add `sort_clone` parameter which allows users to sort (drag and drop) cloned inputs
* Improvement: Add Polish language. Thank Michael
* Fix: Prevent warning when post type doesn't exist (`post` field)

= 4.5.3 =
* Improvement: Use `wp_json_encode` instead of `json_encode`. Thank Sam Ford.
* Fix: Escape value for cloneable fields
* Fix: Notice for missing parameters for `rwmb_meta` field for `map`


= 4.5.2 =
* Improvement: Add Persian (Farsi) language. Thank Ahmad Azimi.
* Improvement: Update Spanish translation. Thank David Perez.
* Fix: Cloning text fields
* Fix: rwmb_meta works incorrectly for image fields if multiple=false

= 4.5.1 =
* Improvement: Add ability to use multiple post types for `post` field
* Fix: Duplicated description for `checkbox` field
* Fix: Blank gallery for image fields

= 4.5 =
* Improvement: Separate `esc_meta` method
* Improvement: Add ability to use URL to retrieve options for autocomplete field
* Improvement: Add `rwmb_get_field` and `rwmb_the_field` functions to get and display field values in the frontend
* Improvement: Add field type `custom_html` to display any HTML in the meta box
* Improvement: Add field type `key_value` which allows users to add any number of key-value pairs
* Improvement: Use single JS file to display Google Maps in the frontend. No more inline Javascript.
* Improvement: Code refactor

= 4.4.3 =
* Fix: Incorrect path to loader image for `plupload_image`
* Fix: Missing placeholder for `post` field when `field_type` = `select`
* Improvement: No errors showing if invalid value is returned from `rwmb_meta_boxes` filter
* Improvement: Add filter for add/remove clone buttons text
* Improvement: Add French translation

= 4.4.2 =
* Fix: Values of text_list field not showing correctly
* Fix: Time picker field cannot select hour > 22, time > 58
* Fix: Notice error when showing fields which don't have ID
* Fix: Don't return non-existing files or images via rwmb_meta function
* Fix: CSS alignment for taxonomy tree
* Fix: Placeholder not working for "select" taxonomy
* Improvement: Update timepicker to latest version
* Improvement: Improve output markup for checkbox field

= 4.4.1 =
* Fix: wrong text domain
* Fix: `select_advanced` field not cloning
* Fix: cloned emails are not saved
* Improvement: Use `post_types` instead of `pages`, accept string for single post type as well. Fallback to `pages` for previous versions.

= 4.4.0 =
* New: 'autocomplete' field.
* Improvement: field id is now optional (heading, divider)
* Improvement: heading now supports 'description'
* Improvement: update select2 library to version 3.5.2
* Improvement: coding standards

= 4.3.11 =
* Bug fix: use field id instead of field_name for wysiwyg field
* Improvement: allow to sort files
* Improvement: use 'meta-box' text domain instead of 'rwmb'
* Improvement: coding standards

= 4.3.10 =
* Bug fix: upload & reorder for image fields
* Bug fix: not saving meta caused by page template issue
* Bug fix: filter names for helper and shortcode callback functions
* Bug fix: loads correct locale JS files for jQueryUI date/time picker

= 4.3.9 =
* Bug fix: `text-list` field type
* Improvement: better coding styles
* Improvement: wysiwyg field is now clonable
* Improvement: launch geolocation autocomplete when address field is cloned
* Improvement: better cloning for radio, checkbox
* Improvement: add more hooks
* Improvement: allow child fields to add their own add/remove clone buttons.
* Improvement: remove 'clone-group'. Too complicated and not user-friendly.

= 4.3.8 =
* Bug fix: compatibility with PHP 5.2

= 4.3.7 =
* Bug fix: use WP_Query instead of `query_posts` to be compatible with WPML
* Bug fix: `get_called_class` function in PHP < 5.3
* Bug fix: clone now works for `slider` field
* Bug fix: fix cloning URL field
* Bug fix: hidden drop area if no max_file_uploads defined
* Improvement: added composer.json
* Improvement: add Chinese language
* Improvement: better check for duplication when save post
* Improvement: new `image_select` file, which is "radio image", e.g. select a radio value by selecting image
* Improvement: new `file_input` field, which allows to upload files or enter file URL
* Improvement: separate core code for meta box and fields
* Improvement: allow to add more map options in helper function
* Improvement: allow to pass more arguments to "get_terms" function when getting meta value with "rwmb_meta"

= 4.3.6 =
* Bug fix: fatal error in PHP 5.2 (continue)
* Improvement: allow register meta boxes via filter

= 4.3.5 =
* Bug fix: fatal error in PHP 5.2
* Bug fix: save empty values of clonable fields

= 4.3.4 =
* Bug fix: not show upload button after delete image when reach max_file_upload. #347
* Bug fix: autocomplete for map which conflicts with tags (terms) autocomplete
* Bug fix: random image order when reorder
* Bug fix: undefined index, notices in WordPress 3.6, notice error for oembed field
* Improvement: add default location for map field (via `std` param as usual)
* Improvement: add `placeholder` for text fields (url, email, etc.)
* Improvement: add `multiple` param for helper function to get value of multiple fields
* Improvement: `width` & `height` for map in helper function now requires units (allow to set %)
* Drop support for WordPress 3.3 (wysiwyg) and < 3.5 (for file & image field which uses new json functions)

= 4.3.3 =
* Bug fix: cannot clear all terms in taxonomy field
* Bug fix: potential problem with autosave
* Bug fix: cannot save zero string value "0"
* Improvement: add Turkish language
* Improvement: add taxonomy_advanced field, which saves term IDs as comma separated value in custom field

= 4.3.2 =
* Bug fix: allow to have more than 1 map on a page
* Bug fix: use HTTPS for Google Maps to work both in HTTP & HTTPS
* Bug fix: allow to clear all terms in taxonomy field
* Bug fix: "std" value for select fields is no longer "placeholder"
* Improvement: add "placeholder" param for select fields
* Improvement: add to helper function ability to show Google Maps in the front end. Check documentation for usage.
* Improvement: add spaces between radio inputs
* Improvement: add more params to "rwmb_meta" filter
* Improvement: using CSS animation for delete image

= 4.3.1 =
* Bug fix: fatal error if ASP open tag is allowed in php.ini

= 4.3 =
* Bug fix: show full size image after upload if thumbnail is not available
* Bug fix: new added file not shown
* Bug fix: issue with color field disappearing
* Bug fix: `max_file_upload` now works for normal `file` & `image` as well
* Bug fix: problem with uploading with the advanced fields
* Bug fix: file & image advanced not saving
* Bug fix: `select_advanced` cloning issue
* Bug fix: `plupload_image` ordering
* Improvement: add `divider`, `heading`, `button`, `range`, `oembed`, `email`, `post` fields
* Improvement: translation for file & image fields
* Improvement: add option `default_hidden` to hide meta box by default
* Improvement: allow to have multiple maps on the same page
* Improvement: file and image advanced now use Underscore.js
* Improvement: `slider` filed now has `prefix` and `suffix` for text labels and `js_options` for more JS options
* Improvement: WYSIWYS can bypass the `wpautop` using `raw` parameter
* Improvement: `color` field now supports new color picker in WP 3.5
* Improvement: add `ID` to results returned by `rwmb_meta` when getting meta value of file & image
* Improvement: auto use localized version for date & time fields
* Improvement: add `timestamp` option to save the datetime as unix timestamp internally
* Improvement: add `autosave` option for meta box
* Improvement: add `force_delete` option for file and image field
* And lots of changes and improvements


= 4.2.4 =
* Bug fix: path to Select2 JS and CSS. [Link](http://wordpress.org/support/topic/missing-files-5)
* Bug fix: `taxonomy.js` loading
* Bug fix: saving in quick mode edit
* Improvement: add `before` and `after` attributes to fields that can be used to display custom text
* Improvement: add Arabic and Spanish languages
* Improvement: add `rwmb*_before_save_post` and `rwmb*_before_save_post` actions before and after save post
* Improvement: add autocomplete for geo location in `map` field, add fancy animation to drop marker
* Improvemnet: add `url` field


= 4.2.3 =
* Bug fix: clone date field. [Link](http://www.deluxeblogtips.com/forums/viewtopic.php?id=299)

= 4.2.2 =
* Bug fix: `time` field doesn't work. [Link](http://wordpress.org/support/topic/time-field-js-wont-run-without-datetime)
* Bug fix: wrong JS call for `datetime`. [Link](http://wordpress.org/support/topic/421-datetime)
* Improvement: file and images now not deleted from library, *unless* use `force_delete` option
* Improvement: add `select_advanced` field, which uses [select2](http://ivaynberg.github.com/select2/) for better UX. Thanks @funkedgeek

= 4.2.1 =
* Bug fix: not save wysiwyg field in full screen mode. [Link](http://www.deluxeblogtips.com/forums/viewtopic.php?id=161)
* Bug fix: default value for select/checkbox_list. [Link](http://www.deluxeblogtips.com/forums/viewtopic.php?id=174)
* Bug fix: duplicated append test to `date` picker
* Bug fix: incorrect enqueue styles, issue #166
* Improvement: initial new field type `map`

= 4.2 =
* Bug fix: save only last element of `select` field with `multiple` values. [Link](http://wordpress.org/support/topic/plugin-meta-box-multiple-declaration-for-select-fields-no-longer-working?replies=5#post-3254534)
* Improvement: add `js_options` attribute for `date`, `datetime`, `time` fields to adjust jQuery date/datetime picker options. See `demo/demo.php` for usage
* Improvement: add `options` attribute for `wysiwyg`. You now can pass arguments same as for `wp_editor` function
* Improvement: clone feature now works with `checkbox_list` and `select` with `multiple` values
* Improvement: add `rwmb-{$field_type}-wrapper` class to field markup
* Improvement: Add [rwmb_meta meta_key="..."] shortcode. Attributes are the same as `rwmb_meta` function.
* Code refactored

= 4.1.11 =
* Bug fix: helper function for getting `taxonomy` field type
* Bug fix: `multiple` attribute for `select` field type

= 4.1.10 =
* Allow helper functions can be used in admin area
* Allow cloned fields to have a uniquely indexed `name` attribute
* Add Swedish translation
* Allow hidden field has its own value
* Taxonomy field now supported by `rwmb_meta` function
* Improvement in code format and field normalizing

= 4.1.9 =
* Add helper function to retrieve meta values
* Add basic validation (JS based)
* Fix image reorder bug
* Fix `select_tree` option for taxonomy field
* Fix not showing loading image for 1st image using plupload

= 4.1.8 =
* Add missed JS file for thickbox image

= 4.1.7 =
* Quick fix for thickbox image

= 4.1.6 =
* Quick fix for checkbox list and multiple/clonable fields

= 4.1.5 =
* Taxonomy field is now in core
* Add demo for including meta boxes for specific posts based on IDs or page templates
* Meta box ID is now optional
* Add `thickbox_image` field for uploading image with WP style
* Fix `guid` for uploaded images

= 4.1.4 =
* Fix taxonomy field

= 4.1.3 =
* Support max_file_uploads for plupload_image
* Better enqueue styles & scripts
* Store images in correct order after re-order
* Fix cloning color, date, time, datetime fields

= 4.1.2 =
* Improve taxonomy field
* Add filter to wp_editor
* Add more options for time field
* Improve plupload_image field
* Fix translation, use string for textdomain

= 4.1.1 =
* Fix translation
* Change jQueryUI theme to 'smoothness'
* Add more demos in the `demo` folder

= 4.1 =
* Added jQuery UI slider field
* Added new Plupload file uploader
* Added new checkbox list
* Fix empty jQuery UI div seen in FF in admin footer area
* Fix style for 'side' meta box

= 4.0.2 =
* Reformat code to make more readable
* Fix bugs of checkbox field and date field

= 4.0.1 =
* Change format_response() to ajax_response() and use WP_Ajax_Response class to control the ajax response
* Use wp_editor() built-in with WP 3.3 (with fallback)

= 4.0 =
* strongly refactor code
* create/check better nonce for each meta box
* use local JS/CSS libs instead of remote files for better control if conflict occurs
* separate field functions (enqueue scripts and styles, add actions, show, save) into separated classes
* use filters to let user change HTML of fields
* use filters to validate/change field values instead of validation class
* don't use Ajax on image upload as it's buggy and complicated. Revert to default upload

= 3.2.2 =
* fix WYSIWYG field for custom post type without 'editor' support. Thanks Jamie, Eugene and Selin Online. (http =//disq.us/2hzgsk)
* change some helper function to static as they're shared between objects

= 3.2.1 =
* fix code for getting script's url in Windows
* make meta box id is optional

= 3.2 =
* move js and css codes to separated files (rewrite js code for fields, too)
* allow to add multiple images to image meta field with selection, modified from "Fast Insert Image" plugin
* remove 'style' attibutes for fields as all CSS rules now can be put in the 'meta=box.css' file. All fields now has the class 'rw=$type', and table cells have class 'rwmb=label' and 'rwmb=field'
* allow to use file uploader for images as well, regarding http =//disq.us/1k2lwf
* when delete uploaded images, they're not deleted from the server (in case you insert them from the media, not the uploader). Also remove hook to delete all attachments when delete post. Regarding http =//disq.us/1nppyi
* change hook for adding meta box to 'add_meta_boxes', according Codex. Required WP 3.0+
* fix image uploading when custom post type doesn't support "editor"
* fix show many alerts when delete files, regarding http =//disq.us/1lolgb
* fix js comma missing bug when implement multiple fields with same type
* fix order of uploaded images, thank Onur
* fix deleting new uploaded image
* fix bug when save meta value = zero (0), regarding http =//disq.us/1tg008
* some minor changes such as = add 'id' attribute to fields, show uploaded images as thumbnail, add script to header of post.php and post=new.php only

= 3.1 =
* use thickbox for image uploading, allow user edit title, caption or crop, rotate image (credit to Stewart Duffy, idea from Jaace http =//disq.us/1bu64d)
* allow to reorder uploaded images (credit to Kai)
* save attach ID instead of url (credit to Stewart Duffy)
* escape fields value (credit to Stewart Duffy)
* add 'style' attribute to fields, allow user quick style fields (like height, width, etc.) (credit to Anders Larsson http =//disq.us/1eg4kp)
* wrap ajax callbacks into the class
* fix jquery UI conflict (for time picker, color picker, contextual help)
* fix notice error for checking post type

= 3.0.1 =
* save uploaded images and files' urls in meta fields
* fix date picker bug to not show saved value (http =//disq.us/1cg6mx)
* fix check_admin_referer for non=supported post types (http =//goo.gl/B6cah)
* refactor code for showing fields

= 3.0 =
* separate functions for checking, displaying and saving each type of field; allow developers easily extend the class
* add 'checkbox_list' (credit to Jan Fabry http =//goo.gl/9sDAx), 'color', 'date', 'time' types. The 'taxonomy' type is added as an example of extending class (credit to Manny Fresh http =//goo.gl/goGfm)
* show uploaded files as well as allow to add/delete attached files
* delete attached files when post is deleted (credit to Kai http =//goo.gl/9gfvd)
* validation function MUST return the value instead of true, false
* change the way of definition 'radio', 'select' field type to make it more simpler, allow multiple selection of select box
* improved some codes, fix code to not show warnings when in debugging mode

= 2.4.1 =
* fix bug of not receiving value for select box

= 2.4 =
* (image upload features are credit to Kai http =//twitter.com/ungestaltbar)
* change image upload using meta fields to using default WP gallery
* add delete button for images, using ajax
* allow to upload multiple images
* add validation for meta fields

= 2.3 =
* add wysiwyg editor type, improve check for upload fields, change context and priority attributes to optional

= 2.2 =
* add enctype to post form (fix upload bug), thanks to http =//goo.gl/PWWNf

= 2.1 =
* add file upload, image upload support

= 2.0 =
* oop code, support multiple post types, multiple meta boxes

= 1.0 =
* procedural code

== Upgrade Notice ==
