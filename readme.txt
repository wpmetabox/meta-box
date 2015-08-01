=== Meta Box ===
Contributors: rilwis, fitwp, f-j-kaiser, Omnicia, funkedgeek, PerWiklander, ruanmer
Donate link: http://www.deluxeblogtips.com/donate
Tags: meta-box, custom-fields, custom-field, meta, meta-boxes
Requires at least: 3.5
Tested up to: 4.2.3
Stable tag: 4.5.6
License: GPLv2 or later

Meta Box plugin is a complete tool to create meta box and custom fields in WordPress: lightweight, powerful and easy-to-use.

== Description ==

Meta Box plugin provides powerful API to implement custom meta boxes and custom fields in editing pages (add new/edit post) in WordPress. It works with any custom post type and supports lots of field types.

### Features

* Easily register multiple custom meta boxes for posts, pages or custom post types
* Supports a lot of [field types](http://metabox.io/docs/define-fields/)
* Has built-in hooks which allow you to change the appearance and behavior of meta boxes
* Easily integrate with themes

### Documentation

- [Getting Started](http://metabox.io/docs/getting-started/)
- [Register Meta Boxes](http://metabox.io/docs/registering-meta-boxes/)
- [Define Fields](http://metabox.io/docs/define-fields/)
- [Get Meta Value](http://metabox.io/docs/get-meta-value/)

See more documentation [here](http://metabox.io/docs/).

### Premium Extensions

- [Meta Box Group](http://metabox.io/plugins/meta-box-group/): Organize custom fields in groups for better appearance and structure. Groups can be cloned.
- [Meta Box Builder](http://metabox.io/plugins/meta-box-builder/): Drag and Drop to create Meta Boxes and Custom Fields has never been easier.
- [Meta Box Template](http://metabox.io/plugins/meta-box-template/): Define custom meta boxes and custom fields easier with templates.
- [Meta Box Tooltip](http://metabox.io/plugins/meta-box-tooltip/): Display help information for fields using beautiful tooltips.
- [Meta Box Show Hide (Javascript)](http://metabox.io/plugins/meta-box-show-hide-javascript/): Toggle meta boxes by page template, post format, taxonomy (including category) via Javascript.
- [Meta Box Tabs](http://metabox.io/plugins/meta-box-tabs/): Create tabs for meta boxes easily. Support 3 WordPress-native tab styles and tab icon.
- [Meta Box Columns](http://metabox.io/plugins/meta-box-columns/): Display fields more beautiful by putting them into 12-columns grid.
- [Meta Box Include Exclude](http://metabox.io/plugins/meta-box-include-exclude/): Show/hide meta boxes by ID, page template, taxonomy or custom function.

See all premium extensions [here](http://metabox.io/plugins/).

### Plugin Links

- [Project Page](http://metabox.io/meta-box/)
- [Documentation](http://metabox.io/docs/)
- [Report Bugs/Issues](https://github.com/rilwis/meta-box/issues)
- [Premium Extensions](http://metabox.io)

== Installation ==

1. Unzip the download package
1. Upload `meta-box` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

To getting started with the plugin API, please read [this tutorial](http://metabox.io/docs/getting-started/).

== Frequently Asked Questions ==

== Screenshots ==
1. Text Fields
1. Basic Fields
1. Advanced Fields
1. File Image Upload Fields
1. Media Fields
1. Post Taxonomy Fields

== Changelog ==

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
