[Project Page](http://www.deluxeblogtips.com/meta-box) | [Documentation](http://www.deluxeblogtips.com/docs) | [Support](http://www.deluxeblogtips.com/support) | [Donate](http://www.deluxeblogtips.com/donate)

***

## Contributors

- [Tran Ngoc Tuan Anh a.k.a Rilwis](http://www.deluxeblogtips.com/) - Initial & lead developer
- [Franz Josef Kaiser](http://unserkaiser.com)
- Omnicia
- ruanmer
- PerWiklander
- erodesign
- funkedgeek

[See full list of angels!](https://github.com/rilwis/meta-box/contributors)

***

## Meta Box Information

**Requires at least:** WordPress v3.0

**Tested up to:** WordPress v3.3.2

Meta Box plugin helps you easily implement multiple meta boxes in editing pages in WordPress. It works with custom post types and supports various field types.

### Description

Meta Box Script for WordPress is a WordPress plugin that provides an API for you to easily implement multiple meta boxes in editing pages in WordPress. It works with custom post types and supports various field types.

* Create meta box for editing pages in WordPress.
* Compatible with custom post types since WordPress 3.0.
* **Support input types:** text, textarea, checkbox, checkbox list, radio box, select, wysiwyg, old & new file upload, date, time, color, slider.

### Installation

1. Unzip the download package
1. Upload `meta-box` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Copy `demo/demo.php` file from plugin directory to your theme folder
1. Open `functions.php` file of your theme and add the following code:

    include 'demo.php';

To change meta box definition, please see comments in `demo.php`.

### Changelog

### 4.1.8
* Add missed JS file for thickbox image

### 4.1.7
* Quick fix for thickbox image

### 4.1.6
* Quick fix for checkbox list and multiple/clonable fields

### 4.1.5
* Taxonomy field is now in core
* Add demo for including meta boxes for specific posts based on IDs or page templates
* Meta box ID is now optional
* Add `thickbox_image` field for uploading image with WP style
* Fix `guid` for uploaded images

### 4.1.4
* Fix taxonomy field

### 4.1.3
* Support max_file_uploads for plupload_image
* Better enqueue styles and scripts
* Store images in correct order after re-order
* Fix cloning color, date, time, datetime fields

### 4.1.2
* Improve taxonomy field
* Add filter to wp_editor
* Add more options for time field
* Improve plupload_image field
* Fix translation, use string for textdomain

#### 4.1.1
* Fix translation
* Change jQueryUI theme to 'smoothness'
* Add more demos in the `demo` folder

#### 4.1
* Added jQuery UI slider field
* Added new Plupload file uploader
* Added new checkbox list
* Fix empty jQuery UI div seen in FF in admin footer area
* Fix style for 'side' meta box

#### 4.0.2
* Reformat code to make more readable
* Fix bugs of checkbox field and date field

#### 4.0.1
* Change `format_response()` to `ajax_response()` and use WP_Ajax_Response class to control the ajax response
* Use `wp_editor()` built-in with WP 3.3 (with fallback)

#### 4.0
* strongly refactor code
* create/check better nonce for each meta box
* use local JS/CSS libs instead of remote files for better control if conflict occurs
* separate field functions (enqueue scripts and styles, add actions, show, save) into separated classes
* use filters to let user change HTML of fields
* use filters to validate/change field values instead of validation class
* don't use Ajax on image upload as it's buggy and complicated. Revert to default upload

#### 3.2.2
* fix WYSIWYG field for custom post type without 'editor' support. Thanks Jamie, Eugene and Selin Online. [link](http://disq.us/2hzgsk)
* change some helper function to static as they're shared between objects

#### 3.2.1
* fix code for getting script's url in Windows
* make meta box id is optional

#### 3.2
* move js and css codes to separated files (rewrite js code for fields, too)
* allow to add multiple images to image meta field with selection, modified from "Fast Insert Image" plugin
* remove 'style' attibutes for fields as all CSS rules now can be put in the 'meta=box.css' file. All fields now has the class 'rw=$type', and table cells have class 'rwmb=label' and 'rwmb=field'
* allow to use file uploader for images as well, regarding [link](http://disq.us/1k2lwf)
* when delete uploaded images, they're not deleted from the server (in case you insert them from the media, not the uploader). Also remove hook to delete all attachments when delete post. Regarding [link](http://disq.us/1nppyi)
* change hook for adding meta box to 'add_meta_boxes', according Codex. Required WP 3.0+
* fix image uploading when custom post type doesn't support "editor"
* fix show many alerts when delete files, regarding [link](http://disq.us/1lolgb)
* fix js comma missing bug when implement multiple fields with same type
* fix order of uploaded images, thank Onur
* fix deleting new uploaded image
* fix bug when save meta value = zero (0), regarding [link](http://disq.us/1tg008)
* some minor changes such as = add 'id' attribute to fields, show uploaded images as thumbnail, add script to header of post.php and post=new.php only

#### 3.1
* use thickbox for image uploading, allow user edit title, caption or crop, rotate image (credit to Stewart Duffy, idea from Jaace http =//disq.us/1bu64d)
* allow to reorder uploaded images (credit to Kai)
* save attach ID instead of url (credit to Stewart Duffy)
* escape fields value (credit to Stewart Duffy)
* add 'style' attribute to fields, allow user quick style fields (like height, width, etc.) (credit to Anders Larsson [link](http://disq.us/1eg4kp))
* wrap ajax callbacks into the class
* fix jquery UI conflict (for time picker, color picker, contextual help)
* fix notice error for checking post type

#### 3.0.1
* save uploaded images and files' urls in meta fields
* fix date picker bug to not show saved value [link](http://disq.us/1cg6mx)
* fix check_admin_referer for non=supported post types [link](http://goo.gl/B6cah)
* refactor code for showing fields

#### 3.0
* separate functions for checking, displaying and saving each type of field; allow developers easily extend the class
* add 'checkbox_list' (credit to Jan Fabry [link](http://goo.gl/9sDAx)), 'color', 'date', 'time' types. The 'taxonomy' type is added as an example of extending class (credit to Manny Fresh [link](http://goo.gl/goGfm))
* show uploaded files as well as allow to add/delete attached files
* delete attached files when post is deleted (credit to Kai [link](http://goo.gl/9gfvd))
* validation function MUST return the value instead of true, false
* change the way of definition 'radio', 'select' field type to make it more simpler, allow multiple selection of select box
* improved some codes, fix code to not show warnings when in debugging mode

#### 2.4.1
* fix bug of not receiving value for select box

#### 2.4
* (image upload features are credit to Kai [link](http://twitter.com/ungestaltbar))
* change image upload using meta fields to using default WP gallery
* add delete button for images, using ajax
* allow to upload multiple images
* add validation for meta fields

#### 2.3
* add wysiwyg editor type, improve check for upload fields, change context and priority attributes to optional

#### 2.2
* add enctype to post form (fix upload bug), thanks to [link](http://goo.gl/PWWNf)

#### 2.1
* add file upload, image upload support

#### 2.0
* oop code, support multiple post types, multiple meta boxes

#### 1.0
* procedural code