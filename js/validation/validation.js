(function($, rwmb, i18n) {
    'use strict';

    class Validation {
        constructor(formSelector) {
            this.$form = $(formSelector);
            this.validationElements = this.$form.find('.rwmb-validation');
            this.showAsterisks();
            this.getSettings();
        }

        init() {
            this.$form
                // Update underlying textarea before submit.
                // Don't use submitHandler() because form can be submitted via Ajax on the front end.
                .on('submit', function() {
                    if (typeof tinyMCE !== 'undefined') {
                        tinyMCE.triggerSave();
                    }
                })
                .validate(this.settings);
        }

        showAsterisks() {
            this.validationElements.each(function() {
                var data = $(this).data('validation');

                $.each(data.rules, function(k, v) {
                    if (!v['required']) {
                        return;
                    }
                    var $el = $('[name="' + k + '"]');
                    if (!$el.length) {
                        return;
                    }
                    $el.closest('.rwmb-input').siblings('.rwmb-label').find('label').append('<span class="rwmb-required">*</span>');
                });
            });
        }

        getSettings() {
            this.settings = {
                ignore: ':not(.rwmb-media,.rwmb-image_select,.rwmb-wysiwyg,.rwmb-color,.rwmb-map,.rwmb-osm,.rwmb-switch,[class|="rwmb"])',
                errorPlacement: function(error, element) {
                    error.appendTo(element.closest('.rwmb-input'));
                },
                errorClass: 'rwmb-error',
                errorElement: 'p',
                invalidHandler: this.invalidHandler.bind(this)
            };

            // Gather all validation rules.
            var that = this;
            this.validationElements.each(function() {
                $.extend(true, that.settings, $(this).data('validation'));
            });
        }

        invalidHandler() {
            this.showMessage();
            // Group field will automatically expand and show an error warning when collapsing
            for (var i = 0; i < this.$form.data('validator').errorList.length; i++) {
                $('#' + this.$form.data('validator').errorList[i].element.id).closest('.rwmb-group-collapsed').removeClass('rwmb-group-collapsed');
            }
            // Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
            var that = this;
            setTimeout(function() {
                that.$form.trigger('after_validate');
            }, 200);
        }

        showMessage() {
            // Re-enable the submit ( publish/update ) button and hide the ajax indicator
            $('#publish').removeClass('button-primary-disabled');
            $('#ajax-loading').attr('style', '');
            $('#rwmb-validation-message').remove();
            this.$form.before('<div id="rwmb-validation-message" class="notice notice-error is-dismissible"><p>' + i18n.message + '</p></div>');
        }
    };

    class GutenbergValidation extends Validation {
        init() {
            var that = this,
                editor = wp.data.dispatch('core/editor'),
                savePost = editor.savePost; // Reference original method.

            if (that.settings) {
                that.$form.validate(that.settings);
            }

            // Change the editor method.
            editor.savePost = function(object) {
                // Bypass the validation when previewing in Gutenberg.
                if (typeof object === 'object' && object.isPreview) {
                    savePost(object);
                    return;
                }

                // Must call savePost() here instead of in submitHandler() because the form has inline onsubmit callback.
                if (that.$form.valid()) {
                    return savePost(object);
                }
            };
        }

        showMessage() {
            wp.data.dispatch('core/notices').createErrorNotice(i18n.message, {
                id: 'meta-box-validation',
                isDismissible: true
            });
        }
    };

    // Run on document ready.
    function init() {
        // Overwrite function staticRules
        $.validator.staticRules = function(element) {
            var rules = {},
                validator = $.data(element.form, "validator");

            // Not rules validate
            if (validator.settings.rules === null || Object.keys(validator.settings.rules).length === 0) {
                return rules;
            }

            // Field hidden not valid
            if (element.type === 'hidden') {
                return rules;
            }
            // Get basename of input name
            const $nameInput = element.name.match(/^(.+?)(?:\[\d+\]|(?:\[\]))?$/);

            if ($nameInput[1] && isNaN($nameInput[1])) {
                const $nameSubInput = element.name.match(/(\w+)|(\[\w+\])/g);
                const resultArray = [$nameSubInput.join("")];

                $nameSubInput.forEach(matchedValue => {
                    if (matchedValue.startsWith("[")) {
                        resultArray.push(matchedValue.substring(1, matchedValue.length - 1));
                    } else {
                        resultArray.push(matchedValue);
                    }
                });
                $nameInput[0] = resultArray[0];
                $nameInput[1] = isNaN(resultArray[resultArray.length - 1]) ? resultArray[resultArray.length - 1] : resultArray[resultArray.length - 2];
            }

            // Validate Input type file and have clone or group
            if (element.type === 'file' && ($(element).closest('.rwmb-clone').length > 0 || $(element).closest('.rwmb-group-wrapper').length > 0)) {
                const $input = $(element).closest('.rwmb-input');
                const $nameInputClone = $input.find('*[value="' + $nameInput[1] + '"]').attr('name').match(/^(.+?)(?:\[\d+\]|(?:\[\]))?$/);
                if ($nameInputClone[1] && isNaN($nameInputClone[1])) {
                    const $nameSubInputClone = $input.find('*[value="' + $nameInput[0] + '"]').attr('name').match(/(\w+)|(\[\w+\])/g);
                    const resultCloneArray = [$nameSubInputClone.join("")];

                    $nameSubInputClone.forEach(matchedValue => {
                        if (matchedValue.startsWith("[")) {
                            resultCloneArray.push(matchedValue.substring(1, matchedValue.length - 1));
                        } else {
                            resultCloneArray.push(matchedValue);
                        }
                    });
                    $nameInputClone[0] = resultCloneArray[0];
                    $nameInputClone[1] = isNaN(resultCloneArray[resultCloneArray.length - 1]) ? resultCloneArray[resultCloneArray.length - 1] : resultCloneArray[resultCloneArray.length - 2];
                }

                if (!validator.settings.rules[$nameInputClone[1]] && $nameInputClone[1].includes('_index_')) {
                    $nameInputClone[1] = ($nameInputClone[1]).slice(7);
                }

                if (validator.settings.rules[$nameInputClone[1]]) {
                    // Set message for element					
                    validator.settings.messages[element.name] = validator.settings.messages[$nameInputClone[1]];
                    // Set Rule for element
                    return $.validator.normalizeRule(validator.settings.rules[$nameInputClone[1]]) || {};
                }

                return rules;
            }

            // Validate other input
            const inputNameList = $(element.form).find('*[name^="' + $nameInput[1] + '"]');
            if (inputNameList.length > 0) {
                // Set message for element					
                validator.settings.messages[element.name] = validator.settings.messages[$nameInput[1]];
                // Set Rule for element
                return $.validator.normalizeRule(validator.settings.rules[$nameInput[1]]) || {};
            }

            if (validator.settings.rules) {
                return $.validator.normalizeRule(validator.settings.rules[element.name]) || {};
            }

            return rules;
        };

        if (rwmb.isGutenberg) {
            var advanced = new GutenbergValidation('.metabox-location-advanced'),
                normal = new GutenbergValidation('.metabox-location-normal'),
                side = new GutenbergValidation('.metabox-location-side');

            side.init();
            normal.init();
            advanced.init();
            return;
        }

        // Edit post, edit term, edit user, front-end form.
        var $forms = $('#post, #edittag, #your-profile, .rwmb-form');
        $forms.each(function() {
            var form = new Validation(this);
            form.init();
        });
    };

    rwmb.$document
        .on('mb_ready', init);

})(jQuery, rwmb, rwmbValidation);