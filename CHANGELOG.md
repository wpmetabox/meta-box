### 5.10.10 - 2025-05-21

- Fix datetime field returns null
- Fix single image field not working with Polylang Pro
- Fix reveal password icon not working

### 5.10.9 - 2025-05-08

- Add button to toggle password (#1630)
- Add gesture handling support for OSM field (#1631)
- Datetime & select2: use user's locale instead of site's locale

### 5.10.8 - 2025-03-14

Redesign the dashboard

### 5.10.7 - 2025-02-25

Fix: max clone with clone empty start

### 5.10.6 - 2025-01-11

- Fix validation for dash ids
- Fix datetime fields not showing inline picker inside groups
- Fix `label_description` not working for `fieldset_text`
- Fix field label div still show when no field name but with `label_description`
- Remove `image_select` field's JS, styling with CSS only
- Add gap for key value inputs

### 5.10.5 - 2024-12-16

- Make validation for add new terms work
- Improve styling of meta boxes on the sidebar in the block editor
- Improve style of input with prepend and append
- Fix maps/osm fields and geolocation not working with subfields in groups
- Fix select advanced becomes normal select with cloneable setting
- Fix validation

### 5.10.4 - 2024-11-20

- OSM/Map fields: add support for select field type
- Add `$url` to `rwmb_oembed_not_available_string` filter
- Small CSS improvements for file input, background & button group
- Fix not returning value for helper functions for images saving in a custom folder and using a custom table

### 5.10.3 - 2024-10-30

- fix: std after saving
- fix: field set text save empty values
- fix: subfield's id

### 5.10.2 - 2024-09-26

- Fix issue with conditional logic
- Fix error when removing default taxonomy meta box in the front end or with the block editor
- Fix missing .hidden on the front end for `checkbox_tree`
- Fix required attribute for `select_tree`

### 5.10.1 - 2024-09-07

Fix issue with `clone_empty_start` (validation, now showing data for the 1st clone, broken `text_list` field, etc.)

### 5.10.0 – 2024-08-20

This version introduces new parameter for field: `clone_empty_start` that makes cloneable groups **not** showing inputs at first. When users want to enter data, they’ll need to click the “+ Add new” button. This feature updates the UI and makes it cleaner. See more details on our [blog post](https://metabox.io/clone-empty-start/). Other changes:

- Replace `sprintf` with string concatenation in `RWMB_Field::show` to fix issues when `$field['before']` or `$field['after']` contains special characters (`%`). Props Daniel Haim.
- Fix mismatch filter params for `rwmb_meta` when no fields are found.

### 5.9.11 – 2024-07-17

- Make validation work for blocks
- Fix JavaScript validation error in site editor
- Set default minute step = 5 for datetime/time pickers
- Security fix for ajax getting posts

### 5.9.10 – 2024-07-02

- Fix modal not updating URL (when add new)
- Security fix for ajax get posts/users

### 5.9.9 – 2024-06-20

- Fix show hide checkbox tree
- Fix default value not display as selected time
- Fix datetime field not removing value when set inline & timestamp = true

### 5.9.8 – 2024-05-08

- Fix activation error on ajax request since WordPress 6.5

### 5.9.7 – 2024-04-18

- Revert fix for MB Conditional Logic

### 5.9.6 – 2024-04-17

- Add progress bar for field `file_upload`
- Force returned value of sanitize color to string
- Fix jumping layout for MB Conditional Logic
- Fix errors when using cloneable map/osm fields

### 5.9.5 – 2024-03-26

- Add `save_format` settings to `time` field
- Field icon SVG not displaying

### 5.9.4 - 2024-02-27

- Fix security issue when users set object id in the helper functions where they don't have permission to view (such as private posts)

### 5.9.3 - 2024-02-02

**Highlights:** Fix security issue of the output shortcode \`\[rwmb\_meta\]\` not escaping. Users can disable escaping (to revert the previous behavior) by adding this snippet:

add\_filter( 'rwmb\_meta\_shortcode\_secure', '\_\_return\_false' );
// or
add\_filter( 'rwmb\_meta\_shortcode\_secure\_{$field\_id}', '\_\_return\_false' );

Other changes:

- Fix compatibility with PHP 8.3
- Fix not showing more than 10 saved users or terms

### 5.9.2 – 2024-01-22

- Validation: fix PHP warning when fields has non-consecutive keys
- Icon field: fix custom icon not working
- Update jQuery Validation to 1.20.0. Props Maarten.
- Prepare css to be inlined. Props Maarten.

### 5.9.1 – 2023-12-25

- Fix preview posts not working in the block editor in WP 6.4.
- Icon field: allow to set relative path/URL for settings
- Icon field: add support for scanning CSS file (`icon_css` setting) and parsing CSS class
- Autocomplete field: fix not saving first value if the value is 0 (integer).

### 5.9.0 – 2023-11-22

**Highlights:** Add new `icon` field type, which supports Font Awesome Free, Font Awesome Pro and custom icon set. Can be used with icon font with CSS file or with SVGs. See the [plugin docs](https://docs.metabox.io/fields/icon/) for how to use it. The `icon` field type will be added to the new version of MB Builder soon, which will allow you to configure its settings with UI.

### 5.8.2 - 2023-10-13

- Google Maps & OSM: ask for current user location for the map if there is no saved location or no default location is set (#1537)
- Fix media templates missing in blocks (#1536)

### 5.8.1 – 2023-09-20

- Fix missing validation files

### 5.8.0 – 2023-09-20

**Highlights:** This version improves validation module, makes it work for file’s MIME types and in groups. More specifically, validation now works in the following cases:

- Cloneable fields
- file and image fields, both non-cloneable & cloneable.
- Subfields in groups, including file and image. For required subfields, asterisks are also added properly.

It works well with MB Builder and with code. An improvement for registering validation rules with code is that **you only need to set field ID in all cases**. Previously, you had to set like `_file_{field_id}[]` for files, or `field_id[]` for taxonomy fields. Other changes:

- Output media templates only on edit pages
- Remove non-existing icon files in jQueryUI CSS
- Hide right area when creating new term (#1528)
- Fix validation i18n URL
- Fix image advanced not working in Customizer
- Fix wrong position of dropdown for select advanced field (#1530)

### 5.7.5 – 2023-08-10

- Improve security (#1518). Thanks Patchstack for helping us.
- Add jQuery validation i18n (#1525)
- Fix media button not show in WordPress 6.3 when Elementor is activated
- Fix OSM not display properly
- Update leaflet to 1.9.4
- Update jQuery Validation to 1.19.5
- Make field class filterable with filter `rwmb_field_class`, accept 2 parameters `$class` and `$field_type`.

### 5.7.4 – 2023-07-18

- Datetime: set the same timezone the same as in WordPress when ‘save\_format’ is ‘c’ or ‘r’ (#1515)
- Fix WYSIWYG not working in custom block (#1513)
- Fix deleting file in Media Library breaks validation
- Fix checkbox, radio field spacing

### 5.7.3 – 2023-06-23

- Fix visual tab not editable in WYSIWYG field
- Fix adding new term with checkbox tree display duplicates
- Use modern PHP 8 string functions available in WordPress’s 5.9 compat

### 5.7.2 – 2023-06-05

- Fix taxonomy field remove default meta box applied for all post types
- Fix the first option of a select is not selected when cloning a parent group with `clone_default` = `true`
- Fix error when deleting image in the media lib and on the frontend with `file_advanced`
- Fix datetime’s `save_format` not working with formats `c` and `r`
- Improve license check

### 5.7.1 – 2023-05-24

- Fix saved clone values not showing
- Fix alignment for inputs

### 5.7.0 – 2023-05-23

**Highlights:** Add `add_new` option (bool) for `post`, `taxonomy`, `taxonomy_advanced` and `user` fields, allowing users to add new posts, terms or users without leaving the edit screen. The add new action is done in a popup in the current edit screen, where you can set all the necessary data for the new item. This option improves the existing option for `taxonomy`, and now works for `post` and `user` fields as well. It also works with cloneable fields and all field types (`select_advanced`, `select`, `checkbox_list`, `radio` and even `select_tree` and `checkbox_tree`). Other changes:

- Add `rwmb_ajax_get_*` hook for filtering ajax results for getting posts, terms and users
- Register “Meta Box” block category for other extensions to use
- Update style for input list, select tree and switch label
- Fix not setting default value for relationships
- Fix meta box style in the media modal
- Fix missing underscore dependency for `select_advanced`
- Fix conflict with Beaver Builder

### 5.6.18 – 2023-03-21

- Fix select dropdown overflowing in the Gutenberg sidebar
- Fix not clearing color

### 5.6.17 – 2023-02-27

- Use icon for file types like PDF in the file fields. Credit Eric Celeste.
- Add `mb_field_id` in query variable args for `post` field for developers to detect this kind of query. Credit Eric Celeste.
- Fix CSS for marker position for OSM field on the front end
- Fix color picker mode HSL not working
- Fix custom fields for media modal not working with custom tables
- Fix sanitizing date timestamp before Jan 01 1970

### 5.6.16 – 2023-01-29

- Fix multiple `file` fields in cloneable groups not cloning properly
- Fix custom fields not showing up in media modal
- Fix warning when image select field not having options
- Fix autoload file not found when the whole WordPress site is managed by Composer
- Fix `taxonomy_advanced` not displaying selected values in sub groups
- Fix CSS for files in settings pages with `no-box` style
- Add a type-safe check for meta box settings

### 5.6.15 – 2022-12-21

- Remove empty post types from meta box settings
- Fix multiple type file fields in cloneable groups not cloning properly
- Fix color for date month/year dropdown
- Fix error message when deleting images in the Customizer with MB Settings Page

### 5.6.14 – 2022-12-13

- Improve style for date picker
- Update jQueryUI to 1.13.2
- Fix inline date field not localized
- Fix visibility for object field’s query() method, which is called in MB Views and MB Builder
- Fix $meta is not countable in object field’s query method
- Remove return type for is\_save() to be compatible with old version of MB Term Meta
- Start to use PSR-4 and Composer

### 5.6.13 – 2022-12-08

- Fix name for adding form enctype to match with MB Term Meta extension
- Fix return type for RWMB\_Helpers\_Array::map()
- Fix required param type for RWMB\_Helpers\_Field::get\_class()

### 5.6.12 – 2022-12-06

- Fix error getting license key

### 5.6.11 – 2022-12-06

- Fix compatibility with other extensions

### 5.6.10 – 2022-12-06

- Fix padding for images in custom blocks
- Fix sidebar::query not compatible with object\_choice::query
- Fix compatibility with custom models in MB Custom Table. Closes #1466.
- Modernize code: use short array, add type hints, remove comments

### 5.6.9 – 2022-12-05

- Improve accessibility for form controls, especially when using on the front end
- Use all admin themes for switch and button group
- Add filter ‘rwmb\_validation\_message\_string’ for validation message
- Display field label description even if no label
- Fix not displaying the language according to user preference
- Fix not setting post parent for the uploaded images on the front end for `image_upload`fields
- Fix warning when using `file_info` helper function
- Modernize code for PHP 7

### 5.6.8 – 2022-11-11

- Fix PHP8 warning in image field file info
- Fix wrong comment for translation
- Bump PHP version requirement to 7.0

### 5.6.7 – 2022-09-16

- Fix file\_upload not working with required validation
- Fix wrong text domain
- Fix button group option to display horizontally not save in the builder

### 5.6.6 – 2022-08-05

- Fix meta box not showing for settings page under Media
- Fix upload to the custom folder does not display the image
- Fix field taxonomy not creating new term if required = true

### 5.6.5 – 2022-07-14

- Fix select advanced don’t escape HTML characters

### 5.6.4 – 2022-05-05

- Fix when field taxonomy return WP\_Error
- Fix field image\_upload not working with tab
- Fix wysiwyg not working for attachment in the media modal
- Improve license check

### 5.6.3 – 2022-04-18

- Improve Google Maps search, allowing to search by place names
- Fix incorrect the label ID for subfield in groups
- Fix validation not working when a cloneable group collapse
- Improve license key check

### 5.6.2 – 2022-04-01

- Fix map not showing in block preview
- Fix deleting images in cloneable groups
- Fix PHP notice for file\_upload field
- Expose the uploader for file\_upload/image\_upload so developers can work on that. For example: disable the submit button when uploading files.

### 5.6.1 – 2022-03-08

- Fix compatibility for PHP < 7.3

### 5.6.0 – 2022-03-01

- Field `background` and `file_input`: support showing image thumbnail
- Add `link` param in helper functions for `taxonomy`, `post`, `user` fields to show `view`, `edit` link or plain text (`false`)
- Add support for float values for range field
- Add `minlength` as a common props for fields
- Remove FILTER\_SANITIZE\_STRING to compatible with PHP 8
- Fix PHP notice when run rwmb\_the\_value() for taxonomy field with no values

### 5.5.1 – 2021-12-15

- Fix warning for post field caused by the search by post title

### 5.5.0 – 2021-12-14

- Add `min_clone` parameter to set the minimum number of clones. Props @baden03.
- Post field: find by title only
- MB Builder compatibility: parse choice options in real-time
- Prevent inputs overflow the container

### 5.4.8 – 2021-10-20

- Respect `cols` attribute of `textarea` field to set the width of the input (without `cols`, textarea is 100% width)
- Fix padding for seamless style in Gutenberg
- Fix divider not showing in Gutenberg
- Remove unnecessary escape meta value

### 5.4.7 – 2021-09-16

- Fix deleting files and images inside groups.
- Fix maxlength and pattern not working if not set inside attributes
- Fix not switching tabs for wysiwyg editors
- Fix unit for checkbox width
- Fix remove clone button on top of inputs
- Fix style for checked checkboxes on desktops
- Hide hidden field with custom class, not .hidden

### 5.4.6 – 2021-07-08

- Remove debug code

### 5.4.5 – 2021-07-08

- Fix styling issue for heading field and side meta boxes

### 5.4.4 – 2021-07-06

- Improve usability for time picker on mobile by adding +/- buttons
- Make all input, select, textarea 100% width
- Export clone functions to the global “rwmb”

### 5.4.3 – 2021-06-30

- Fix trailing comma in function call for PHP < 7.3

### 5.4.2 – 2021-06-29

- Improve style for media fields to reuse style/HTML markup.
- Make input, select, input group, select2, textarea full width on the side context.
- Improve style for button group when buttons don’t have same width.
- Set better default options for date time pickers.
- Allow to output HTML in input prepend/append (ex. icon).
- Add filter `rwmb_dismiss_dashboard_widget` to dismiss dashboard widget.

### 5.4.1 – 2021-06-01

- Improve style for prepend, append text
- Improve style for select2 on mobiles
- Make select\_tree extend select\_advanced and respect select\_advanced options

### 5.4.0 – 2021-05-08

- Shortcode: add `render_shortcodes` attribute (default true) to allow render inner shortcodes.
- File fields: allow to change uploaded file name when uploading to custom folder via `unique_filename_callback` setting
- Dashboard: add more video tutorials
- Image fields: fix actions (edit, delete) not visible on mobile
- Choice fields: fix not saving value if they contain quotes
- Datetime fields: fix not saving timestamp via REST API

### 5.3.10 – 2021-04-24

- Disable autocomplete for date/datetime fields
- Input list field: Fix label not working if contains HTML
- Fix multiple OSM on the same page
- Add auto update for solutions
- Fix various bugs for the wysiwyg editor field (mostly in blocks) and allows to pass tinyMCE/quicktags settings to the editor

### 5.3.9 – 2021-03-10

- Fix taxonomy\_advanced doesn’t load options in attachment with media\_modal set to true.
- Fix `rwmb_{$field_id}_choice_label` not working for checkbox\_list field type
- Fix clone\_default not working for switch if set std = true
- Update jQueryUI styles to 1.12.1

### 5.3.8 – 2021-01-28

- Fix value not available when loaded in `map` and `osm` fields.

### 5.3.7 – 2021-01-11

- Fix editor not editable in Gutenberg
- Fix content in the visual tab of editors not saving
- Make required validation work for color, map, osm, switch, text-list fields
- Add dismiss action for dashboard news

### 5.3.6 – 2020-12-29

- Fix validation not working for image-select, image and wysiwyg fields
- Fix clone\_default not working for switch
- Fix saving select field value when defining each option as an array
- Fix wysiwyg not editable in WP 5.6

### 5.3.5 – 2020-11-30

- Update color picker library to v3.0.0 and fix color picker with opacity not working in the Customizer (used with MB Settings Page).
- Cache update requests
- Show (No title) in object fields if they don’t have title

### 5.3.4 – 2020-09-23

- Add default title
- Update autoloader
- Bypass the validation when previewing in Gutenberg
- Add MB Views to the updater
- Update color picker script to latest version 2.1.4
- Fix missing labels for color field (wp 5.5.1)
- Fix preview is not generated
- Fix seamless style in WordPress 5.5
- Fix style for file\_input field (description is inline with input field)

### 5.3.3 - 2020-07-21

- Hide Go Pro link for premium users
- Update intro and image for the new Online Generator

### 5.3.2 - 2020-07-03

- Fix validation not working for media fields
- Add “add\_to” option for media fields to allow adding new images to the beginning/end of the list
- Improve style for input & select on the sidebar
- Improve style for mobiles

### 5.3.1 - 2020-06-03

- Fix validation not working for multiple forms (front end)
- Fix PHP warning: Creating default object from empty value
- Fix cloning, sorting wysiwyg field when users disable visual editor
- Change color of switch based on admin color scheme

### 5.3.0 - 2020-05-11

- Add `rwmb_set_meta` function to set meta value.
- Add Gutenberg compatibility for validation.
- Fix wrong label output for switch when it's off.

### 5.2.10 - 2020-04-17

- Hotfix for getting meta value for checkbox list.

### 5.2.9 – 2020-04-17

- Fix cloning default value not working for some fields.

### 5.2.8 - 2020-04-06

- Add option open info window in Google Maps when loaded
- Add `alpha_channel` & update style to background field
- Add support for custom Ajax parameters for object fields.
- Fix validation rules still applied for hidden fields
- Fix `image_upload` field select files not working on iPhone
- Fix fatal error with Frontend Submission & Elementor
- Fix ‘zoom’ parameter not working for OSM field on the front end
- Remove languages folder. Load languages from translate.wordpress.org only

### 5.2.7 - 2020-02-07

- Fix warning in image field for metadata\['sizes'\].
- Allow to quick define text fields with "name" attribute only.

### 5.2.6 – 2020-02-03

- Fix wrong tag when deploying

### 5.2.5 - 2020-02-03

- Fix CSS in about page and add MB Core to list of premium plugins
- Fix edit icon not showing popup for image fields
- Fix OpenStreetMap not loading properly in tabs
- Replace date() with gmdate()
- Update style for input prepend/append for WordPress >= 5.3
- Add custom trigger after validation for Tabs/Settings extension to show error fields
- Add URL to all sizes in the returned value of helper functions for images

### 5.2.4 – 2019-12-11

- Add hook `rwmb_field_registered` after a field is registered.
- Add (\*) to required fields
- Remove required attribute for license input box.
- Don't redirect when bulk activate with other plugins.
- Fix style for `select`, `select_advanced` fields in WordPress 5.3.
- Fix getting object fields for settings pages

### 5.2.3 – 2019-11-01

- Set clone=false if max\_clone=1
- Shows only images in the selection popup for image field
- Hide license key
- Fixed parsed terms in taxonomy advanced for MB Blocks
- Don’t show date picker for readonly fields
- Fix warning when output empty background field value
- Fix empty meta value when save\_field=false

### 5.2.2 – 2019-10-09

- Fix sanitizing empty post field
- Fix post thumbnail not saving in MB Frontend Submission
- Fix undefined index for `image_select` field when using helper functions with no value.
- Fix JQMIGRATE: ‘ready’ event is deprecated
- Add styling for date picker, autocomplete, select2 to show in the Customizer (for MB Settings Page)

### 5.2.1 – 2019-09-26

- Fix object fields show only selected items when field type is not select advanced
- Fix background field not saving position, attachment & size
- Fix undefined variable in media modal
- Fix non-unique input name for a non-cloneable file in a cloneable group

### 5.2.0 – 2019-09-18

- Add ajax support for object fields.
- Add custom CSS class for meta box wrapper div.
- Improve file upload, making it works in groups.
- Optimize performance for cloning wysiwyg field.
- Bypass updates for embed extensions via TGMPA.
- Fix PHP warning when using clone with date formatting.
- Fix file upload input not visible when clone a file field with uploaded files = max\_file\_uploads.

### 5.1.2 – 2019-08-29

- Fix adding >= 2 blocks containing a wysiwyg field not rendering
- Fix CSS for wyswigy field in Gutenberg
- Do not show upgrade message in the Dashboard for premium users
- Fix media field is blank
- Fix cannot access to license page in Multisite
- Fire `change` and/or `mb_change` events when fields change to update custom blocks in real-time (requires MB Blocks extension)

### 5.1.1 - 2019-08-23

- Fix sanitizing number always return 0 if it's blank
- Fix sanitizing URL
- Set default field `type` to `text`, make it optional and help you write less code
- File/image fields: do not show add new file link if `max_file_uploads` = 1

### 5.1.0 - 2019-08-19

- Fatal error with `RWMB_About::redirect()`, props @DevIntact.
- Ensure change event fires when editors change. This fix is for MB Blocks extension where updating a `wysiwyg` field doesn’t trigger `change` event.
- Fix `rwmb_{$field_id}_choice_label` not working for cloneable fields.
- Add a missing dependency (`underscore`) for date picker JavaScript to make the field work in the front end.
- Fix un-indexed notice for `key_value` field.
- Align uploaded videos for `video` field.
- Update notification system
- Improve sanitization for fields.

### 5.0.1 - 2019-07-25

- Fix license notification always show

### 5.0.0 - 2019-07-24

- New minimum PHP version is now 5.3.
- Rewrite all JavaScript to prepare for Gutenberg blocks
- Allow to create meta box with no fields.
- Add the updater for auto update of premium extensions
- Add support for `user`, `settings_pages` in `rwmb_get_object_fields`
- Fix warning for cloneable single image.

### 4.18.4 - 2019-07-06

- Fix error for the front end forms that have a taxonomy field.
- Set 'remove\_default' param for taxonomy field to `false`.
- Fix color picker field is hidden by the post editor at `after_title` position.

### 4.18.3 - 2019-07-01

- Make the OpenStreetMap field display fully when page loads
- Fix validation not working with `wysiwyg`
- Fix media fields not working if a value is deleted
- Fix `image_advanced` bug for Gutenberg
- Fix cloning group copies previous `image_advanced`
- Fix select tree breaks scroll in Gutenberg
- Add `remove_default` param for taxonomy field, allowing to remove default WordPress taxonomy meta box.

### 4.18.2 - 2019-06-02

- Remove initial ajax requests to get attachments for media fields that helps increase the performance for the edit page.
- Fix autocomplete for Geolocation extension

### 4.18.1 - 2019-05-21

- Improve UX for Google Maps, Open Street Map fields:
    - Alert users if no address is found in Open Street Maps and Google Map fields.
    - Remove "Find Button".
    - Auto adjust the map when address fields change. Work only for multiple address fields. Single address field already have autocomplete.
- Remove code for autoloader for PHP 5.2.
- Improve cache for post/user queries to make sure `post`, `user` fields always see fresh data.
- Add missing jQuery autocomplete for Open Street Map field when using on the front end.

### 4.18.0 - 2019-05-07

- Allows to add new term for `taxonomy` and `taxonomy_advanced` fields. Enable this feature by adding `'add_new' => true` to the field settings. Please note that it's not working inside group and not working for cloneable fields.
- Fixed `slider` UI not working when `range = false`.
- Fixed alignment for uploaded files when using with `image` field.
- Fixed `marker_icon` option not working for OpenStreetMap.
- Fixed shortcode not working with the current post in a non-main loop if `object_id` is not specified.
- Do not show edit link for file, image fields if users don't have proper capability.

### 4.17.3 - 2019-04-09

- Fixed `file_upload`, `image_upload` not working.

### 4.17.2 - 2019-04-02

- Fixed `required` attribute for `file` prevents updating posts when the field already has value.
- Fixed couldn't add images if `max_file_uploads` is not set (`image_advanced`).

### 4.17.1 - 2019-04-01

- Fixed JavaScript error for `slider` field when creating a new post.
- Fixed images of the `image_advanced` cleared when changing image the post content in Gutenberg.
- `text_list`: Do not save if all inputs has no value.

### 4.17.0 - 2019-03-18

- Added `range` support for `slider` field for storing 2 values.
- Added `attribute` to `[rwmb_meta]` shortcode to get only one attribute from the value (such as URL of the image or term slug).
- Added `prepend` and `append` attributes for inputs like Bootstrap's input group.
- Refactored the code.
- Changed shortcode attributes to use `id` (instead of `meta_key`), `object_id` (instead of `post_id`).
- Fixed empty date field with save\_format causes error.
- Fixed wrong position of the asterisk when the field is required and has label description.
- Fixed indirect modification of `meta_box->$fields`.
- Fixed `required` attribute not working for `file`, `image` fields.
- Fixed warning in the about page.
- Fixed box-sizing issue for settings page.

### 4.16.3 - 2019-02-02

- Fixed non-authorized users can delete files via ajax.

### 4.16.2 - 2019-02-01

- Fixed a security issue with upload file to custom folders.
- Fixed empty values are saved for `taxonomy_advanced` field.

### 4.16.1 - 2019-01-30

- Hot fix for missing arguments in `file_info` function.

### 4.16.0 - 2019-01-30

- New feature: allow users to upload files to custom folders in `file` field.
- New feature: allow users to show date time in a format and save in a different format.
- Turned `select_tree` into beautiful dropdown with select2.
- Added larger margin for label in side column.
- Improved autoloader. Props @David Matějka.
- Improved performance by caching queries for post, user fields. Taxonomy and taxonomy advanced are cached by WordPress.
- Fixed select elements with long titles from relationship boxes break out of meta box. Props @Doug
- Fixed OSM not working if there are more than 1 map.
- Fixed quote style changed for Czech and other languages that use other quote style.
- Fixed cannot save posts when `select_tree` has `required` attribute.
- Fixed reorder images delete images when `force_delete` is set to `true`.

### 4.15.9 - 2018-12-12

- Fix compatibility issue with Yoast SEO in WordPress 5.0 or Gutenberg plugin is installed, and ClassicPress editor is active.

### 4.15.8 - 2018-12-10

- Fixed output of datetime field when timestampt=true
- Fixed WYSIWYG field blank when inside a sortable, cloneable group.
- Fixed updating cloneable field with values contain quotes.
- Fixed wysiwyg field not saving in Gutenberg / WordPress 5.0.

### 4.15.7 - 2018-11-09

- Fixed `taxonomy` field not showing correct saved value.
- Fixed cloning `taxonomy_advanced` fields when set `field_type=select_tree` or set `clone_as_multiple=true`.
- Changed global variable name for the loader to prevent conflict with other plugins.

### 4.15.6 - 2018-10-26

- Fixed selecting files in Microsoft Edge for `file_upload` and similar fields.
- Fixed returning value for cloneable `single_image` field.
- Fixed `zoom` level is not working for `map` on the front end.
- Fixed missing missing gettext function and comment for Dashboard page.
- Restore missing `choice_label` filter for choice fields.
- Fixed OSM map not refreshing in tabs or after moving meta boxes.
- Added a helper function `rwmb_get_object_fields` to get list of fields for an object.
- Updated `select2` library to 4.0.5

### 4.15.5 - 2018-09-08

- Fixed saving `0` for switch/checkbox fields when they're off.
- Fixed JS error while reordering image\_advanced field images. #1265.
- Fixed incompatibility with Custom Sidebars. #1269.
- Added prefix `rwmb-` to `collapse` and `inline` CSS classes. Used for input list and button group fields.

### 4.15.4 - 2018-08-24

- Fixed syntax error for sanitize switch field.

### 4.15.3 - 2018-08-24

### Fixed

- Fixed `is_saved()` is wrong for cloneable `text_list` field.
- Removed quotes for output of helper function for `background` field.
- Saved value `0` for `switch` field when it's off.
- Make validation works with `radio`/`checkbox_list`.
- Fixed compatibility with PHP < 5.4 for `oembed` field.
- Added missing style for OpenStreetMap (`osm`) field, just like Google Maps (`map`) field.
- Fixed clone `wysiwyg` not active Visual mode by default.

### Changed

- Prevent fields `post`, `taxonomy`, `user` from making un-needed queries.
- Optimized query for `user` field.
- Removed extra query for other meta type than post when using helper functions.

### 4.15.2 - 2018-08-03

- Fixed z-index for `switch` and `color` fields.
- Fixed select All / None for checkbox list not working in groups.
- Fixed select\_tree type not working.

### 4.15.1 - 2018-07-23

- Fixed rendering HTML in button groups.

### 4.15.0 - 2018-07-20

### Added

- Added new `osm` field for Open Street Map. Google Maps requires developers to enter credit card details to keep using their free service, which is not always possible (and comfortable). The new `osm` field uses open source data from Open Street Map with the Leafleft library to render maps. It has the same settings as the Google Maps `map` field, including autocomplete feature, `language` and `region`.
- Added new boolean field setting `save_field` for disabling the fields from saving to the database. Developers should handle the "save" action themselves.
- Added `closed` parameter for meta box that collapses meta box on page load.
- Allows users to change the "Embeded HTML not available" for oembed field or hide it with CSS:
    - Added `not_available_string` setting to oembed field.
    - Added `rwmb_not_available_string` filter for oembed field.
    - Wrap "Not available" string in a div.

### Changed

- Object fields: optimized the "clone" functionality. No extra queries for clones.

### Fixed

- Fixed map field not working in cloneable groups.
- Fixed warning when installed via WP-CLI. Props Arnaud Hallais.

### 4.14.11 - 2018-06-08

- Fixed styles for autocomplete address in map field in the frontend.

### 4.14.10 - 2018-06-05

- Fixed missing styles for `autocomplete` in the frontend.
- Added the correct image size in returned value of 'srcset' for image field.
- Fixed `select_advanced` field doesn't keep the same width when cloning.
- Improved the layout for `text_list` field.
- Added MB User Profile and MB Beaver Builder Integration. Removed ads for premium users.
- Created a shared admin menu for extensions to add to. Also added hooks for extensions to add tabs to the About page.

### 4.14.9 - 2018-05-17

- Fixed short array syntax (PHP 5.4) in taxonomy field.

### 4.14.8 - 2018-05-16

- Activation via TGMPA causes 'headers already sent' error.

### 4.14.7 - 2018-05-14

- Updated the About page.

### 4.14.6 - 2018-05-12

- Added `clone_as_multiple` option which allows to store clone values as multiple rows in the database. Props @radeno.
- Preserve the order of selected options for taxonomy, post, user and select\_advanced that uses `select2`.
- Fixed "Select All | None" not working for taxonomy/user/post fields.
- Quick fix for MB Frontend Submission.

### Removed

- Removed some languages that are available on translate.wordpress.org

### 4.14.5 - 2018-04-05

- Fixed wrong syntax in the new filter that breaks the plugin to save values.

### 4.14.4 - 2018-04-05

- Fixed for MB Frontend Submission not be able to edit submitted posts.
- Added filter `rwmb_*_field` that allows developer to modify field settings before saving field value into the database. Credit @Twinpictures @baden03.

### 4.14.3 - 2018-04-04

- Fixed `taxonomy` field not returning correct value if post ID is not set.
- Fixed placeholder not shown for post/taxonomy/user fields by default.

### 4.14.2 - 2018-03-27

- Fixed helper function for `taxonomy_advanced` returns incorrect value when no terms are selected. Fixed #1224
- Do not save fields without id. Fix for custom table extension.

### 4.14.1 - 2018-03-24

- Added `after_save_field` action.
- Fixed field taxonomy not saving for multiple taxonomies.
- Fixed cloning taxonomy\_advanced field

### 4.14.0 - 2018-03-05

- Added compatibility for Gutenberg. The plugin is now fully compatible with Gutenberg, except the field types `file`, `image` and `wysiwyg`. These are Gutenberg bugs and hopefully they're fixed soon.
- Added support for `image_size` for `image` field that sets image size in the backend.
- Added support for `language` in `map` field.
- Fixed file input field doesn't hide the Remove button when cloning.
- Fixed cloning not clearing "selected" class for buttons in the button groups.
- Fixed step validation for `number` field.
- Remove `thickbox_image` field. WordPress doesn't support it anymore. Use `image_advanced` instead.

### 4.13.4 - 2018-02-28

- Fixed output of text-list field.
- Fixed cloning radio field clears the checked status of existing field.

### 4.13.3 - 2018-02-07

- Fixed drag and drop parent groups into child groups.
- Removed type hint for adding new contexts. Caused fatal error for Tribulant Newsletter plugin.

### 4.13.2 - 2018-01-31

- Fixed meta box not working in media modal.
- Fixed "add new" button not appearing for nested groups with "max\_clone" (for MB Group extension).
- Fixed sortable nested group being able to be moved outside of its parents (for MB Group extension).
- Fixed video field: broken CSS in admin and helper function not working.
- Changed function name from `rwmb_get_field_data` to `rwmb_get_field_settings`.

### 4.13.1 - 2018-01-08

- Added helper function `render_map` for map field, which can be used with groups to show Google maps.
- Fixed duplication of "Add media" button when cloning group contains `image_advanced` field.

### 4.13.0 - 2017-12-28

- Added settings `'style' => 'seamless'` to meta boxes which removes the wrapper for meta boxes, makes fields displayed seamlessly.
- Added new field type `switch` with iOS / square styles.
- Added new field type `background` that lets you define all background attributes easier.
- Added new field type `button_group` that lets you define group of buttons, similar to a toolbar.
- Added new field type `single_image` that lets you select only a single image.
- Added Italian translation. Props @flocca.
- Fixed post parent value is not selected after saving (`post` field).

### 4.12.6 - 2017-12-04

- Added welcome page. Displayed only once when first install the plugin. Won't display if included via TGM or directly in themes/plugins.
- Added "sidebar" field type.
- Added "format" option for shortcode and `rwmb_the_value` for datetime field.
- Fixed compatibility with old versions of WPML.
- Fixed image field not saving values after updating posts.
- Fixed validation message overlapping text for radio field.

### 4.12.5 - 2017-11-17

- Fixed "Select All/None" doesn't work with post field (checkbox\_list type).
- Fixed color picker field for WordPress 4.9.
- Fixed map field doesn't work inside groups.
- Fixed media fields don't reset value when cloning the container groups.
- File field now uses hidden input to handle field values. Remove ajax action for reordering files and simply file action for deleting files. Also make it works with post thumbnail removal in the frontend.

### 4.12.4 - 2017-10-18

- Fixed taxonomy advanced select tree doesn't work.
- Fixed error when value of taxonomy field is empty.
- Fixed helper functions don't work with taxonomy\_advanced field
- Increased priority for registering meta boxes to 20 to support custom port types registered using the default priority.
- Fixed condition so that the fields based on posts/taxonomies are correctly translated.

### 4.12.3 - 2017-08-22

- Reset media status when reset media field.
- Added support for clone default value. Requires 'clone\_default' => true.
- Optimized JS performance when cloning.
- Updated French translation
- Brought date picker in front of admin bar.
- Fixed margin of media list when no images are selected.
- Fixed trigger reselect image popup when reorder images.
- Fixed autosave not working.

### 4.12.2 - 2017-07-12

- Added new `media:reset` event for media fields to clear the selection.
- Improve meta box registry that can handle various types of meta boxes. Used for term meta, user meta, settings pages.
- Improve the way set current object id for meta box and add fields to the registry.

### 4.12.1 - 2017-07-05

- Helper function doesn't work. #1144.

### 4.12 - 2017-07-04

- Completed the storage abstraction. All the actions add/get/update/delete post meta now use the storage methods. Make it easy to extend for other extensions for term/user meta and settings pages.
- Added `autofocus`, `autocomplete` HTML5 attribute to inputs.
- Added `alpha_channel` to `color` field. Set it to `true` to allow picking colors with opacity.
- Click on the image will open a popup for re-select image. Works for `image_advanced` and `image_upload` (`plupload_image`) fields.
- Auto display oembed media when pasting the URL, without click "Preview" button (and it's removed).
- Better styles for media fields. Use the loading icon from WordPress.
- Fix cloning an editor inside a group in WordPress 4.8. Caused by updated version of TinyMCE using Promise.
- Modals created by media fields now exclude already selected media. This was a previous feature, but it had caused problems with uploading.
- Fixed Google map doesn't use custom style

### 4.11.3

- Make sure all cloned fields have unique ID, even inside multi-level groups. Now WYSIWYG editor works better with cloned groups.
- Fixed wrong name attributes when clone key-value field.
- Add missing $field parameter for "add\_clone\_button\_text" filter.

### 4.11.2

- Introducing storage interface, which now can be extended for term/user/settings page under the same codebase. With this improvement, helper functions now works for term/user/settings page (require premium extensions).
- Fixed cloning wysiwyg field when tinyMCE mode is turned off (only show quick tags).
- Fixed image\_upload & file\_upload field doesn't add attachment to post
- Fixed text\_list fields not saving correctly when edit not last field.

### 4.11.1

- Added button for "Check/Uncheck all options" in input list field when type is `checkbox_list`. Props @mrbrazzi.
- Select multiple images now does not require to press "Shift".
- Change button field to actual button element.
- Fix scripts and styles dependencies
- Fix bug for select tree when parent not set
- Add sanitize post type in case users use CamelCase in post type name
- Increase z-index of datepicker to prevent overlap with top menu bar
- Make compatible with MB Admin Columns and MB Frontend Submission extensions
- Update Persian translation. Credit Morteza Gholami

### 4.11

- Code architecture update:
    - Add `object_id` property to the meta box object. Used to connect the meta box to the post object. Not heavily used yet.
    - Add RWMB\_Meta\_Box\_Registry and RWMB\_Field\_Registry to store all registered meta boxes and fields. Used to future access these objects. Use internally only. 3rd-party code should not use it as it's still experimental and can change in the future.
    - Deprecated RWMB\_Core::get\_meta\_boxes() due to the meta box registry above. This function was made for internally use only. 3rd-party code should not rely on it.
    - Add magic method \_\_get to the meta box object to quick access to meta box configuration.
- UI update:
    - Make the field label bold (similar to WordPress settings page).
    - Increase margin between fields and change color for remove clone button (also reduce minus size)
    - Remove style for checkbox (default is good).
    - Improve styles for checkbox/radio list
    - A little smaller padding for select box. Also remove default size for select\[multiple\]
    - Add a little space between the map canvas and the "Find Address" button
- Media fields:
    - Media field update: Hidden field for media fields now no longer disabled. If media field js not loaded properly, code will default to hidden field and process accordingly. Issue #1088.
    - Better and simpler way to handle HTML file upload using `media_handle_upload` function.
    - Rewrite JS for "file", simpler and modular. Also fix bug when add/remove items that doesn't toggle the Add more link correctly.
    - Improve JS code for media field, using MediaList instead of item views (Backbone JS code).
    - Add support for image sizes in image\_advanced and image\_upload. Default is thumbnail. Fixes #425.
- Clone:
    - Add new parameter `add_button` for the add new clone button text. Better than use filter.
    - Fix position for remove clone button in RTL languages
    - Update margin between clones and set clone placeholder = outer height of the cloned element.
- Scripts and styles:
    - Check condition for enqueueing scripts & styles only in the admin.
    - Update the time picker library to the latest version (1.6.3) to supports "select" control for time picker.
    - Better dependencies for date picker CSS, autocomplete field.
- Other improvements:
    - Remove the static helper class because of a bad OOP code.
    - Fix get plugin URL symlink when plugin is put in a theme and symlinked. Props @tutv95.
    - Add support for "region" in the map field to limit autocomplete from wrong entries (to help preventing from entering wrong address or country).

### 4.10.4

- Improvement: Add support for validate user edit and term edit forms (requires MB Term Meta or MB User Meta extension).
- Improvement: Add new parameter `label_description` for fields to display a description below field label (name).
- Improvement: Add support for edit custom fields in the media modal. Requires `post_type` set to `attachment` and `media_modal` set to true.
- Improvement: For WPML users: Add support to hide fields in "Do not translate" mode and disable editing fields in "Copy" mode.
- Fix: Users can't select same images for 2 different `image_advanced` fields.
- Fix: `max_status` doesn't work.

### 4.10.3

- Fix: `force_delete` causes files to be deleted even when set to `false`.
- Fix: `max_file_uploads` not working.

### 4.10.2

- Improvement: Add `max_file_size` to upload fields (`File_Upload`, `Image_Upload`, `Plupload_Image`).
- Improvement: Add support for attributes for file input.
- Improvement: Update Polish translation.
- Improvement: Add translation support for Key and Value strings (@prop saqibsarwar).
- Fix: Shorter width of email field vs. other fields (@prop saqibsarwar).
- Fix: Fix cloneable datetime field with timestamp=true.
- Fix: Remove margin bottom in select\_advanced options.
- Fix: Showing the correct selected value for select\_advanced field when the option value contains '&' character.
- Fix: Fix default values not working with taxonomy and taxonomy\_advanced fields.

### 4.10.1

- Fix: Fix `image_upload` and `plupload_image` field when select images to upload.

### 4.10

- Improvement: Add `video` field type which allows users to upload or select video from the Media Library.
- Improvement: Update Turkish. Prop Emre Tuna tunaemre@windowslive.com.
- Improvement: Use WP 4.6 date picker localization instead of JS localized file to reduce plugin size.
- Improvement: Refactor the media fields for better performance. Add `change` event for item list when add, remove or reset.
- Fix: `taxonomy_advanced` field now can be cloned.
- Fix: Make localize\_script works with WP 4.1.

### 4.9.8

- Fix: Quick fix for enqueueing validation script

### 4.9.7

- Improvement: Re-add change event on media fields to make Conditional Logic extension works with media fields.
- Improvement: Add `rwmb_choice_label`, `rwmv_{$field_type}_choice_label` and `rwmb_{field_id}_choice_label` filters for post, user, taxonomy fields, allowing users to customize the labels of choice fields.
- Improvement: Change coding styles to follow WordPress Coding Standards.
- Various improvements to reduce duplicated code.
- Fix: Map field now works in the frontend.
- Fix: `std` now works for taxonomy fields.

### 4.9.6

- Fix: Wrong CSS selector when cloning wysiwyg field
- Fix: Remove preview for oembed field when cloning
- Fix: 'std' for taxonomy field now works

### 4.9.5

- Fix: Quick fix for wrong field wrapper class which causes color field to render incorrectly

### 4.9.4

- Fix: Quick fix for cloning bug

### 4.9.3

- Fix: Quick fix saving datetime field

### 4.9.2

- Fix: Quick fix validation

### 4.9.1

- Fix: Quick fix for `rwmb_meta()` to be better backward compatible

### 4.9

- Improvement: Update Chinese language. Add Chinese Taiwan.
- Improvement: Add support for Google Maps API key. Default API key is added, however users should replace it with their own key.
- Improvement: Add additional methods for jQuery validation module which makes it understand HTML5 "pattern" attribute.
- Improvement: Fully WPML compatibility (supported by WPML team)
- Improvement: Add placeholders for `key_value` field
- Fix: Toggle remove clone buttons for nested groups.
- Fix: Error on date field, not save
- Fix: Add fix for date/datetime when both inline and timestamp used
- Fix: Set default language for date/time picker.
- Fix: rwmb\_meta for images returns incorrect width/height
- Fix: PHP warning when uploading files in Settings Pages extension.
- Fix: Blank space in the footer when using plupload\_image.
- Fix: Cloning wysiwyg when deleting the 1st clone

### 4.8.7

- Improvement: Refactor the code to reduce the complexity in the fields' inheritance
- Improvement: All HTML 5 input types (week, month, etc.) are supported
- Improvement: Optimize the\_value function, use recursive call to reduce nested loop. Sub-fields need to define format\_single\_value only.
- Improvement: Use 1 single localization file for jQuery date picker for simplicity
- Improvement: Add support for custom marker for map field (param `marker_icon`) in rwmb\_meta function
- Improvement: Add `limit` option for media fields in `rwmb_meta` function when retrieving meta value.
- Improvement: Add `rwmb_option_label` filter for choice fields (user, post, taxonomy) so users can choose which object field is used as label
- Improvement: Use `WP_User_Query` for user field which supports more options for querying
- Improvement: Optimize code for oembed, also use esc\_html\_\_ for better security
- Improvement: Compatibility with MB Geolocation
- Fix: Fix first option is auto selected in select\_advanced field.
- Fix: Fix clone issue for color in MB Group extension.
- Fix: Fix clone issue for image advanced in MB Group extension.
- Fix: Fix not parsing $args to array in helper functions.

### 4.8.6

- Improvement: Edit link on media items now opens edit modal
- Improvement: Refresh map when sorting meta boxes.
- Improvement: Wrap checkbox's description into a to make it clickable to activate/deactivate the checkbox.
- Improvement: Remove Spanish language (ES) as it's already translated on translate.wordpress.org
- Improvement: Add support for saving zoom in map
- Improvement: Prevent output localized strings twice.
- Improvement: Add fallback for autoload in PHP 5.2 in case it's disabled.
- Improvement: No need to json\_encode for custom attributes. User can pass an array to custom attribute
- Improvement: Add style for `select2` library to match WordPress admin style
- Improvement: Adds min width to select. @prop ahmadawais
- Improvement: Added `max_status` option for media type fields. `true` to show status, `false` to hide
- Improvement: Add attachment meta data to file info
- Fix: Validation for non-Meta Box fields
- Fix: advanced\_image field after reload page F5 in Firefox
- Fix: Cannot read property 'getFullYear' of null
- Fix: Empty date converting to 0
- Fix: Add missing class for image\_select field which prevents setting input's name when cloning.
- Fix: Fix bug with blank maps on the front end
- Fix: Fix bug with cloning media fields
- Fix: Remove empty values in clones and reset index.
- Fix: Reset of cloned select fields
- Fix: select\_advanced with multiple=true adds empty selected option
- Fix: No empty option for simple select field
- Fix: Empty datetime field with timestamp => true returns January 1, 1970
- Fix: For color picker when using with Columns extension
- Fix: Fix bug with taxonomy advanced returns all taxonomy items for posts with no meta saved
- Fix: Fix bug with taxonomy advanced not saving value when field isn't multiple
- Fix: Make radio inline again
- Fix: Wrong meta value when using helper function outside the loop
- Fix: Validation now works for hidden elements in tabs

### 4.8.5

- Improvement: Add localization for Select2 library
- Improvement: Range preview output added
- Improvement: Add Persian translation and nag fix
- Fix: Map has no refresh in collapsed meta boxes
- Fix: Fix incorrect URL if the plugin is symlinked.
- Fix: Added fix for saved order in object-choice

### 4.8.4

- Improvement: Refactor code for plupload\_image. Introduces file\_upload and image\_upload field which acts the same as plupload\_image but for files and images.
- Improvement: Do not show "Embed is not available" if fields don't have any value
- Improvement: Refactor date/time related fields. 'timestamp' now works for date field as well.
- Improvement: Add 'inline' mode for date/datetime fields.
- Improvement: Add option 'select\_all\_none' for select/select2 with default = false
- Fix: users now can register 2 meta boxes with same field IDs for different post types.
- Fix: width of embeded video if $content\_width is too large.
- Fix: autoloader now works more safely.
- Fix: post field doesn't show correct post link
- Fix: select field must call field's get\_value to get field's value as 'select' is used in many non-inherited classes
- Fix: Allows old syntax for `query_args.post_types` for post/user/taxonomy fields
- Fix: Do not reset value for hidden field when clone
- Fix: Missing Insert into Post button for thickbox\_image field
- Fix: Date picker cut off by TinyMCE
- Fix: CSS for multi months in date picker

### 4.8.3

- Improvement: WYSIWYG field now can be cloned. Sorting clone hasn't worked yet.
- Fix: 'std' value not working if there is 'divider' or 'heading' field withough 'id'
- Fix: helper function not working in AJAX or admin.
- Fix: getting plugin's path on Windows system.
- Fix: warning get\_value of taxonomy field
- Fix: guarantee file ids are in an array

### 4.8.2

- Fix: re-add code for backward compatibility for helper function
- Fix: undefined 'class' attribute for button
- Improvement: speedup the helper function

### 4.8.1

- Fix: select multiple value with post, user and taxonomy
- Fix: bug in oembed field
- Fix: fix JS/CSS compatibility with WooCommerce
- Fix: do not force field ID to lowercase, which can potentially breaks existing fields or fields with ID of CAPS characters.

### 4.8.0

- Improvement: rewrite the way the plugin loads file, which allows developers to include the plugin into themes/plugins simply by include the main file. The new loading mechanism also uses autoloading feature which prevents loading files twice and saves memory.
- Improvement: rewrite `user`, `post`, `taxonomy` fields using the same codebase as they're native WordPress objects and sharing similar options. Also changes the syntax of query parameters for these fields (old syntax still works). Please see docs for details.
- Improvement: add `srcset` in the returned value of helper function for image fields
- Improvement: better sanitize value for `url` field
- Improvement: prevent issues with dashes in field types
- Improvement: remove redundant value in checkbox
- Improvement: update CSS for date, time fields
- Improvement: select2 now updated to 4.0.1
- Improvement: optimize code for `file_advanced` and `image_advanced` fields which now submit array of values when saving instead of single CSV value
- Improvement: add `collapse` option to `checkbox_list` and `checkbox_tree` in `user`, `taxonomy`, `post` fields which prevents plugin save parent values.
- Improvement: secure password field so it is no longer saved in plain text. To check if a password matches the hash, please use `wp_check_password`.
- Improvement: change the output of `color` field in the helper function. Now it shows the color instead of hex value.
- Improvement: add `color:change` and `color:clear` JavaScript event for detecting changes in `color` field.
- Improvement: refactor code for better structure and security
- Fix: rewrite the JavaScript for cloning which causes bugs for date field.
- Fix: fix missing attributes if value is '0' or 0.
- Fix: add missing `class` attribute for fields
- Fix: do not auto populate color field with '#'
- Fix: wrong callback for fix page template

### 4.7.3

- Improvement: add `change` event for `file_advanced` and `image_advanced` fields.
- Improvement: add support for boolean attributes.
- Improvement: add support for boolean attributes.
- Improvement: add Russian language.
- Improvement: changed `wp_get_post_terms` to `get_the_terms` to use WordPress cache.
- Improvement: refactored code to make textarea, select use attributes.
- Improvement: `fieldset_text` now cloneable. Also removed `rows` option for this field.
- Improvement: refactored `has_been_saved()` function.

### 4.7.2

- Fix: notice undefined index in date, time fields.

### 4.7.1

- Fix: remove default `maxlength = 30` for text fields.

### 4.7

- Improvement: add `attributes` for all input fields (text, number, email, ...) so users can add any custom attributes for them. Also added default attributes `required`, `disabled`, `readonly`, `maxlength` and `pattern` for those fields as well. These attributes will be merged into the `attributes`.
- Improvement: add `js_options` for color field which allows users to define custom color palettes and other attributes for color picker.
- Fix: fix for file and image uploaded via `file_advanced` and `image_advanced` not showing up.

### 4.6

- Improvement: the plugin translation is now handled in translate.wordpress.org. While the plugin keeps old translation as backward compatibility, it's recommended to translate everything in translate.wordpress.org. Language packs will be automatically updated by WordPress.
- Improvement: rewrite code for `file_advanced` and `image_advanced`, which share the same code base. These fields are now clonable and not autosave (you have to save post to save files)! Props @funkatronic.
- Improvement: restyle clone icon, sort clone icon and add clone button for better UI. The new UI now is compatible with `color` and `date` fields
- Improvement: separate validation module into 1 class, for better code structure
- Improvement: add `pattern` attribute for `url` field
- Improvement: improve code quality
- Fix: missing "checked" when clone radio
- Fix: language file name for Dutch
- Fix: oembed not render preview if provider is added via `wp_embed_register_handler`

### 4.5.7

- Fix: Always set std as value for hidden field
- Fix: `rwmb_meta` now can display rich content from `oembed` field
- Fix: Wrong format for `datetime` field
- Fix: Check and reset clone index when add/remove/sort clones
- Improvement: Optionally display ID attribute for heading and divider
- Improvement: Adding new style to date field to match WordPress style
- Improvement: Change saving hooks to specific post types to prevent saving images to wrong post

### 4.5.6

- Fix: Warning for timestamp for datetime field.
- Fix: z-index for color picker.
- Fix: Marker title in map

### 4.5.5

- Fix: CSS alignment for sort clone icon for field type `group` (require MB Group extension)
- Fix: rwmbSelect is not defined

### 4.5.4

- Improvement: Add "Select All|None" for `select`, `select_advanced`, `post` fields
- Improvement: Add `max_clone` parameter which limits number of clones
- Improvement: Add `sort_clone` parameter which allows users to sort (drag and drop) cloned inputs
- Improvement: Add Polish language. Thank Michael
- Fix: Prevent warning when post type doesn't exist (`post` field)

### 4.5.3

- Improvement: Use `wp_json_encode` instead of `json_encode`. Thank Sam Ford.
- Fix: Escape value for cloneable fields
- Fix: Notice for missing parameters for `rwmb_meta` field for `map`

### 4.5.2

- Improvement: Add Persian (Farsi) language. Thank Ahmad Azimi.
- Improvement: Update Spanish translation. Thank David Perez.
- Fix: Cloning text fields
- Fix: rwmb\_meta works incorrectly for image fields if multiple=false

### 4.5.1

- Improvement: Add ability to use multiple post types for `post` field
- Fix: Duplicated description for `checkbox` field
- Fix: Blank gallery for image fields

### 4.5

- Improvement: Separate `esc_meta` method
- Improvement: Add ability to use URL to retrieve options for autocomplete field
- Improvement: Add `rwmb_get_field` and `rwmb_the_field` functions to get and display field values in the frontend
- Improvement: Add field type `custom_html` to display any HTML in the meta box
- Improvement: Add field type `key_value` which allows users to add any number of key-value pairs
- Improvement: Use single JS file to display Google Maps in the frontend. No more inline Javascript.
- Improvement: Code refactor

### 4.4.3

- Fix: Incorrect path to loader image for `plupload_image`
- Fix: Missing placeholder for `post` field when `field_type` = `select`
- Improvement: No errors showing if invalid value is returned from `rwmb_meta_boxes` filter
- Improvement: Add filter for add/remove clone buttons text
- Improvement: Add French translation

### 4.4.2

- Fix: Values of text\_list field not showing correctly
- Fix: Time picker field cannot select hour > 22, time > 58
- Fix: Notice error when showing fields which don't have ID
- Fix: Don't return non-existing files or images via rwmb\_meta function
- Fix: CSS alignment for taxonomy tree
- Fix: Placeholder not working for "select" taxonomy
- Improvement: Update timepicker to latest version
- Improvement: Improve output markup for checkbox field

### 4.4.1

- Fix: wrong text domain
- Fix: `select_advanced` field not cloning
- Fix: cloned emails are not saved
- Improvement: Use `post_types` instead of `pages`, accept string for single post type as well. Fallback to `pages` for previous versions.

### 4.4.0

- New: 'autocomplete' field.
- Improvement: field id is now optional (heading, divider)
- Improvement: heading now supports 'description'
- Improvement: update select2 library to version 3.5.2
- Improvement: coding standards

### 4.3.11

- Bug fix: use field id instead of field\_name for wysiwyg field
- Improvement: allow to sort files
- Improvement: use 'meta-box' text domain instead of 'rwmb'
- Improvement: coding standards

### 4.3.10

- Bug fix: upload & reorder for image fields
- Bug fix: not saving meta caused by page template issue
- Bug fix: filter names for helper and shortcode callback functions
- Bug fix: loads correct locale JS files for jQueryUI date/time picker

### 4.3.9

- Bug fix: `text-list` field type
- Improvement: better coding styles
- Improvement: wysiwyg field is now clonable
- Improvement: launch geolocation autocomplete when address field is cloned
- Improvement: better cloning for radio, checkbox
- Improvement: add more hooks
- Improvement: allow child fields to add their own add/remove clone buttons.
- Improvement: remove 'clone-group'. Too complicated and not user-friendly.

### 4.3.8

- Bug fix: compatibility with PHP 5.2

### 4.3.7

- Bug fix: use WP\_Query instead of `query_posts` to be compatible with WPML
- Bug fix: `get_called_class` function in PHP < 5.3
- Bug fix: clone now works for `slider` field
- Bug fix: fix cloning URL field
- Bug fix: hidden drop area if no max\_file\_uploads defined
- Improvement: added composer.json
- Improvement: add Chinese language
- Improvement: better check for duplication when save post
- Improvement: new `image_select` file, which is "radio image", e.g. select a radio value by selecting image
- Improvement: new `file_input` field, which allows to upload files or enter file URL
- Improvement: separate core code for meta box and fields
- Improvement: allow to add more map options in helper function
- Improvement: allow to pass more arguments to "get\_terms" function when getting meta value with "rwmb\_meta"

### 4.3.6

- Bug fix: fatal error in PHP 5.2 (continue)
- Improvement: allow register meta boxes via filter

### 4.3.5

- Bug fix: fatal error in PHP 5.2
- Bug fix: save empty values of clonable fields

### 4.3.4

- Bug fix: not show upload button after delete image when reach max\_file\_upload. #347
- Bug fix: autocomplete for map which conflicts with tags (terms) autocomplete
- Bug fix: random image order when reorder
- Bug fix: undefined index, notices in WordPress 3.6, notice error for oembed field
- Improvement: add default location for map field (via `std` param as usual)
- Improvement: add `placeholder` for text fields (url, email, etc.)
- Improvement: add `multiple` param for helper function to get value of multiple fields
- Improvement: `width` & `height` for map in helper function now requires units (allow to set %)
- Drop support for WordPress 3.3 (wysiwyg) and < 3.5 (for file & image field which uses new json functions)

### 4.3.3

- Bug fix: cannot clear all terms in taxonomy field
- Bug fix: potential problem with autosave
- Bug fix: cannot save zero string value "0"
- Improvement: add Turkish language
- Improvement: add taxonomy\_advanced field, which saves term IDs as comma separated value in custom field

### 4.3.2

- Bug fix: allow to have more than 1 map on a page
- Bug fix: use HTTPS for Google Maps to work both in HTTP & HTTPS
- Bug fix: allow to clear all terms in taxonomy field
- Bug fix: "std" value for select fields is no longer "placeholder"
- Improvement: add "placeholder" param for select fields
- Improvement: add to helper function ability to show Google Maps in the front end. Check documentation for usage.
- Improvement: add spaces between radio inputs
- Improvement: add more params to "rwmb\_meta" filter
- Improvement: using CSS animation for delete image

### 4.3.1

- Bug fix: fatal error if ASP open tag is allowed in php.ini

### 4.3

- Bug fix: show full size image after upload if thumbnail is not available
- Bug fix: new added file not shown
- Bug fix: issue with color field disappearing
- Bug fix: `max_file_upload` now works for normal `file` & `image` as well
- Bug fix: problem with uploading with the advanced fields
- Bug fix: file & image advanced not saving
- Bug fix: `select_advanced` cloning issue
- Bug fix: `plupload_image` ordering
- Improvement: add `divider`, `heading`, `button`, `range`, `oembed`, `email`, `post` fields
- Improvement: translation for file & image fields
- Improvement: add option `default_hidden` to hide meta box by default
- Improvement: allow to have multiple maps on the same page
- Improvement: file and image advanced now use Underscore.js
- Improvement: `slider` filed now has `prefix` and `suffix` for text labels and `js_options` for more JS options
- Improvement: WYSIWYS can bypass the `wpautop` using `raw` parameter
- Improvement: `color` field now supports new color picker in WP 3.5
- Improvement: add `ID` to results returned by `rwmb_meta` when getting meta value of file & image
- Improvement: auto use localized version for date & time fields
- Improvement: add `timestamp` option to save the datetime as unix timestamp internally
- Improvement: add `autosave` option for meta box
- Improvement: add `force_delete` option for file and image field
- And lots of changes and improvements

### 4.2.4

- Bug fix: path to Select2 JS and CSS
- Bug fix: `taxonomy.js` loading
- Bug fix: saving in quick mode edit
- Improvement: add `before` and `after` attributes to fields that can be used to display custom text
- Improvement: add Arabic and Spanish languages
- Improvement: add `rwmb-_before_save_post` and `rwmb-_before_save_post` actions before and after save post
- Improvement: add autocomplete for geo location in `map` field, add fancy animation to drop marker
- Improvement: add `url` field

### 4.2.3

- Bug fix: clone date field.

### 4.2.2

- Bug fix: `time` field doesn't work
- Bug fix: wrong JS call for `datetime`
- Improvement: file and images now not deleted from library, -unless- use `force_delete` option
- Improvement: add `select_advanced` field, which uses select2 for better UX. Thanks @funkedgeek

### 4.2.1

- Bug fix: not save wysiwyg field in full screen mode.
- Bug fix: default value for select/checkbox\_list.
- Bug fix: duplicated append test to `date` picker
- Bug fix: incorrect enqueue styles, issue #166
- Improvement: initial new field type `map`

### 4.2

- Bug fix: save only last element of `select` field with `multiple` values
- Improvement: add `js_options` attribute for `date`, `datetime`, `time` fields to adjust jQuery date/datetime picker options. See `demo/demo.php` for usage
- Improvement: add `options` attribute for `wysiwyg`. You now can pass arguments same as for `wp_editor` function
- Improvement: clone feature now works with `checkbox_list` and `select` with `multiple` values
- Improvement: add `rwmb-{$field_type}-wrapper` class to field markup
- Improvement: Add \[rwmb\_meta meta\_key="..."\] shortcode. Attributes are the same as `rwmb_meta` function.
- Code refactored

### 4.1.11

- Bug fix: helper function for getting `taxonomy` field type
- Bug fix: `multiple` attribute for `select` field type

### 4.1.10

- Allow helper functions can be used in admin area
- Allow cloned fields to have a uniquely indexed `name` attribute
- Add Swedish translation
- Allow hidden field has its own value
- Taxonomy field now supported by `rwmb_meta` function
- Improvement in code format and field normalizing

### 4.1.9

- Add helper function to retrieve meta values
- Add basic validation (JS based)
- Fix image reorder bug
- Fix `select_tree` option for taxonomy field
- Fix not showing loading image for 1st image using plupload

### 4.1.8

- Add missed JS file for thickbox image

### 4.1.7

- Quick fix for thickbox image

### 4.1.6

- Quick fix for checkbox list and multiple/clonable fields

### 4.1.5

- Taxonomy field is now in core
- Add demo for including meta boxes for specific posts based on IDs or page templates
- Meta box ID is now optional
- Add `thickbox_image` field for uploading image with WP style
- Fix `guid` for uploaded images

### 4.1.4

- Fix taxonomy field

### 4.1.3

- Support max\_file\_uploads for plupload\_image
- Better enqueue styles and scripts
- Store images in correct order after re-order
- Fix cloning color, date, time, datetime fields

### 4.1.2

- Improve taxonomy field
- Add filter to wp\_editor
- Add more options for time field
- Improve plupload\_image field
- Fix translation, use string for textdomain

### 4.1.1

- Fix translation
- Change jQueryUI theme to 'smoothness'
- Add more demos in the `demo` folder

### 4.1

- Added jQuery UI slider field
- Added new Plupload file uploader
- Added new checkbox list
- Fix empty jQuery UI div seen in FF in admin footer area
- Fix style for 'side' meta box

### 4.0.2

- Reformat code to make more readable
- Fix bugs of checkbox field and date field

### 4.0.1

- Change `format_response()` to `ajax_response()` and use WP\_Ajax\_Response class to control the ajax response
- Use `wp_editor()` built-in with WP 3.3 (with fallback)

### 4.0

- strongly refactor code
- create/check better nonce for each meta box
- use local JS/CSS libs instead of remote files for better control if conflict occurs
- separate field functions (enqueue scripts and styles, add actions, show, save) into separated classes
- use filters to let user change HTML of fields
- use filters to validate/change field values instead of validation class
- don't use Ajax on image upload as it's buggy and complicated. Revert to default upload

### 3.2.2

- fix WYSIWYG field for custom post type without 'editor' support. Thanks Jamie, Eugene and Selin Online
- change some helper function to static as they're shared between objects

### 3.2.1

- fix code for getting script's url in Windows
- make meta box id is optional

### 3.2

- move js and css codes to separated files (rewrite js code for fields, too)
- allow to add multiple images to image meta field with selection, modified from "Fast Insert Image" plugin
- remove 'style' attibutes for fields as all CSS rules now can be put in the 'meta=box.css' file. All fields now has the class 'rw=$type', and table cells have class 'rwmb=label' and 'rwmb=field'
- allow to use file uploader for images as well
- when delete uploaded images, they're not deleted from the server (in case you insert them from the media, not the uploader). Also remove hook to delete all attachments when delete post
- change hook for adding meta box to 'add\_meta\_boxes', according Codex. Required WP 3.0+
- fix image uploading when custom post type doesn't support "editor"
- fix show many alerts when delete files
- fix js comma missing bug when implement multiple fields with same type
- fix order of uploaded images, thank Onur
- fix deleting new uploaded image
- fix bug when save meta value = zero (0)
- some minor changes such as = add 'id' attribute to fields, show uploaded images as thumbnail, add script to header of post.php and post=new.php only

### 3.1

- use thickbox for image uploading, allow user edit title, caption or crop, rotate image (credit to Stewart Duffy, idea from Jaace)
- allow to reorder uploaded images (credit to Kai)
- save attach ID instead of url (credit to Stewart Duffy)
- escape fields value (credit to Stewart Duffy)
- add 'style' attribute to fields, allow user quick style fields (like height, width, etc.) (credit to Anders Larsson)
- wrap ajax callbacks into the class
- fix jquery UI conflict (for time picker, color picker, contextual help)
- fix notice error for checking post type

### 3.0.1

- save uploaded images and files' urls in meta fields
- fix date picker bug to not show saved value
- fix check\_admin\_referer for non=supported post types
- refactor code for showing fields

### 3.0

- separate functions for checking, displaying and saving each type of field; allow developers easily extend the class
- add 'checkbox\_list' (credit to Jan Fabry), 'color', 'date', 'time' types. The 'taxonomy' type is added as an example of extending class (credit to Manny Fresh)
- show uploaded files as well as allow to add/delete attached files
- delete attached files when post is deleted (credit to Kai)
- validation function MUST return the value instead of true, false
- change the way of definition 'radio', 'select' field type to make it more simpler, allow multiple selection of select box
- improved some codes, fix code to not show warnings when in debugging mode

### 2.4.1

- fix bug of not receiving value for select box

### 2.4

- (image upload features are credit to Kai)
- change image upload using meta fields to using default WP gallery
- add delete button for images, using ajax
- allow to upload multiple images
- add validation for meta fields

### 2.3

- add wysiwyg editor type, improve check for upload fields, change context and priority attributes to optional

### 2.2

- add enctype to post form (fix upload bug)

### 2.1

- add file upload, image upload support

### 2.0

- oop code, support multiple post types, multiple meta boxes

### 1.0

- procedural code