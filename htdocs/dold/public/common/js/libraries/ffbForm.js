"use strict";

/**
 * Form functions:
 * - clear(form)
 *      Remove invalid classes, hide validators.
 * - reset(form)
 *      Reset form.
 * - initPlaceholders(form, dataPlacehodlersOnly)
 *      Get default values from text elements and add placeholder logic.
 * - getValue(elements, form)
 *      Get element value.
 * - isValid(form, val)
 *      Validate validator.
 * - validate(form, withTooltip)
 *      Validate form by validators.
 * - assignInvalid(form, fields, withTooltip)
 *      Search for form fields and show invalid.
 * - getValues(form, convertTo)
 *      Get from values by Id.
 * - assignValues(form, values)
 *      Set values to form from array.
 * - isChanged(form)
 *      Check is form changed.
 * - copyMandatoryTranslations(form, masterLang)
 *      Copy values for required tranlateble form fields from form field
 *      of master language.
 *
 * @class
 * @this {ffbForm}
 * @return {ffbForm}
 */
var ffbForm = new function() {

    var _ = this;

    /**
     * This property allows to show console logs in debug mode
     * or to suppress them in production.
     *
     * @private
     * @this {ffbForm}
     * @var {boolean}
     */
    var _dbg = false;

    /**
     * Checks if a given string ends with a given suffix.
     *
     * @private
     * @param {string} string
     *      to search in
     * @param {string} suffix
     *      to search for
     * @return {boolean}
     */
    var _endsWith = function(string, suffix) {
        return string.indexOf(suffix, string.length - suffix.length) !== -1;
    }

    /**
     * Init validator tooltip for invalid fields.
     *
     * @private
     * @this {ffbForm}
     * @param {object} element
     * @param {string} text
     */
    var _initValidatorTooltip = function(element, text) {

        // check if new or exist
        if (element.attr('data-validatortooltip') !== undefined) {
            element.attr('data-validatortooltip', text);
            return;
        }

        // set value
        element.attr('data-validatortooltip', text);

        // init events
        element.on('mousemove', function(e) {

            if (!$(this).hasClass('invalid')) {
                return;
            }

            var tooltip = $('body .validator-tooltip[data-name="' + $(this).attr('name') + '"]');
            if (tooltip.length === 0) {

                // create it
                // TODO update to create element
                tooltip = $('<div class="validator-tooltip" data-name="' + $(this).attr('name') + '"></div>');
                tooltip.html($(this).attr('data-validatortooltip'));
                $('body').append(tooltip);
            }

            var left = e.pageX + 10;
            if (left + tooltip.outerWidth() > $(window).width()) {
                left = $(window).width() - tooltip.outerWidth();
            }

            tooltip.css({
                'left' : left + 'px',
                'top'  : e.pageY - tooltip.outerHeight() - 10 + 'px'
            });

        });

        // hide tooltip
        element.on('mouseout', function(e) {
            $('body .validator-tooltip[data-name="' + $(this).attr('name') + '"]').remove();
        });
    }

    /**
     * Object with placeholdes by form id.
     *
     * @public
     * @this {ffbForm}
     * @type {object}
     */
    _.placeholders = {};

    /**
     * Remove invalid classes, hide validators.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} String or form jQuery object
     * @return {form}
     */
    _.clear = function(form) {

        // Check form id
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        // Get form validators
        var elements = form.find('.validator, .invalid, .validator-message');

        // iterate elements
        elements.each(function(i, element) {
            // Remove invalid classes and hide validators
            element = $(element);
            element.removeClass('invalid');
            if (element.hasClass('validator') || element.hasClass('validator-message')) element.addClass('hide');
        });

        return form;
    }

    /**
     * Reset form.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} String or form jQuery object
     * @return {form}
     */
    _.reset = function(form) {

        // Check form id
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        // Clear form
        ffbForm.clear(form);

        // Default reset
        form[0].reset();

        // Set placeholders
        for (var el in _.placeholders[form.attr('id')]) {
            if (!_.placeholders[form.attr('id')].hasOwnProperty(el)) continue;

            form.find('[name="' + el + '"]').trigger('blur');
        }

        // Set custom dropdowns
//        form.find('select').each(function(i, sel) {
//
//            // check is custom
//            var wrapper = $(sel).next('.ffbdropdown-main');
//            if (wrapper.length === 0)  return;
//
//            // get default value
//            var j     = 0;
//            var opt   = null;
//            var value = '';
//            while (opt = sel.options[j++]) {
//                if (opt.defaultSelected) value = opt.value;
//            }
//        });

        return form;
    }

    /**
     * Get default values from text elements and add placeholder logic.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} or form jQuery object
     * @param {boolean} dataPlacehodlersOnly
     * @return {form}
     */
    _.initPlaceholders = function(form, dataPlacehodlersOnly) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        var formId = form.attr('id');
        if (!formId) {
            formId = 'frm' + new Date().getTime();
            form.attr('id', formId);
        }

        // reset placeholders
        _.placeholders[formId] = {};

        // Get elements
        var elements = form.find('input[type="text"], input[type="search"], input[type="password"], textarea');

        // iterate elements
        elements.each(function(i, element) {

            // Add focus/blue actions

            element = $(element);

            // Get value
            var elementValue = element.attr('data-placeholder');
            if (typeof elementValue === 'undefined' && dataPlacehodlersOnly === true) {
                // use data-placeholders only
                return;

            }

            // Get placeholder from value
            if (typeof elementValue === 'undefined') {
                elementValue = element.val();
            }

            // trim value
            elementValue = $.trim(elementValue);

            // convert password field in texts
            if (element.attr('type') === 'password') {
                element
                    .attr('type', 'text')
                    .attr('data-type', 'password');
            }

            // check data-required
            if (typeof element.attr('data-required') !== 'undefined') {
                elementValue += element.attr('data-required');
            }

            // If default value exist, add placeholder actions
            if (elementValue) {

                // Save placeholder into object
                _.placeholders[formId][element.attr('name')] = elementValue;

                // Add show/hide actions
                element.on('focus', function(event) {

                    var self = $(this);

                    // Add active class name for css
                    self.addClass('active');

                    // Get parent form
                    var parentId = self.parents('form').attr('id');

                    // Check current value and if is placeholder hide it
                    if (_.placeholders[parentId] &&
                        _.placeholders[parentId][self.attr('name')] &&
                        $.trim(self.val()) === _.placeholders[parentId][self.attr('name')])
                    {
                        self.val('');
                    }

                    // convert password field in password
                    if (element.attr('data-type') === 'password') {
                        element.attr('type', 'password');
                    }
                });

                element.on('blur', function(event) {

                    var self = $(this);

                    // Get parent form
                    var parentId = self.parents('form').attr('id');

                    // Check current value, if empty set placeholder back
                    if (_.placeholders[parentId] &&
                        _.placeholders[parentId][self.attr('name')] &&
                        ($.trim(self.val()) === '' || $.trim(self.val()) === _.placeholders[parentId][self.attr('name')]))
                    {
                        self.removeClass('active');
                        self.val(_.placeholders[parentId][self.attr('name')]);

                        // convert password field in text
                        if (element.attr('data-type') === 'password') {
                            element.attr('type', 'text');
                        }
                    } else {
                        element.addClass('active');
                    }
                });
            }

            // set placeholder if value is empty
            element.trigger('blur');
        });

        return form;
    }

    /**
     * Get element value.
     *
     * @public
     * @this {ffbForm}
     * @param {object[]} Elements array
     * @param {object[]} Form HTMLElement
     * @return {mixed}
     */
    _.getValue = function(elements, form) {

        _dbg && console.log('== ffbForm', 'getValue');
        _dbg && console.log('@param {object[]} elements', elements);
        _dbg && console.log('@param {object[]} form', form);

        // Default value is null
        var value    = null;
        var parentId = null;
        if (form !== undefined) {
            parentId = form.attr('id');
        }

        //if (!$.isArray(elements)) elements = [elements];

        // iterate elements
        $(elements).each(function(i, element) {

            var element = $(element);
            _dbg && console.log('element', element);

            //Check element type
            switch (element[0].tagName) {
                case 'INPUT' :
                    switch(element[0].type) {
                        case 'radio' :
                            // Get checked radio
                            if (element[0].checked) {
                                value = element.val();
                            }
                            break;
                        case 'checkbox' :
                            // Get checked checkbox as value or checkboxes as array
                            if (element[0].checked) {
                                if (value === null || !_endsWith(element[0].name, '[]')) {
                                    value = element.val();
                                } else if (typeof value !== 'object') {
                                    var tmp = value;
                                    value = [tmp, element.val()];
                                } else value.push(element.val());
                            }

                            break;
                        case 'button':
                        case 'submit':
                        case 'reset':
                            // No value for buttons
                            break;
                        default:
                            // Get value for text, hidden, password
                            value = element.val();

                            // Dont send placeholders
                            if (parentId &&
                                parentId !== undefined &&
                                _.placeholders[parentId] &&
                                _.placeholders[parentId][element[0].name] &&
                                $.trim(value) === _.placeholders[parentId][element[0].name])
                            {
                                value = null;
                            }
                    }
                    break;
                case 'SELECT' :
                    // get value for select or values array for multi
                    value = element.val();
                    _dbg && console.log('select value', element, element.val());
                    break;
                case 'TEXTAREA' :

                    // Check textarea for Editor instance or get value
                    //if ($('#cke_' + element.id)) {
                    //    value = window.CKEDITOR.instances[element.id].getData();
                    //} else {
                    //    value = element.val();
                    //}
                    //if (window.tinymce !== undefined && $('#' + element.attr('id') + '_ifr').length > 0) {
                    //
                    //    for (var eIndex = 0; eIndex < window.tinymce.editors.length; eIndex++) {
                    //        if (window.tinymce.editors[eIndex].id === element.attr('id')) {
                    //            value = window.tinymce.editors[element.attr('id')].getContent();
                    //        }
                    //    }
                    //} else {
                    //    value = element.val();
                    //}

                    value = element.val();

                    break;
                default:

                    _dbg && console.log('unhandled tagName', element[0].tagName);
                    break;
            }

            _dbg && console.log('interim value', value);

        });

        _dbg && console.log('final value', value);

        return value;
    }

    /**
     * Validate validator.
     *
     * @public
     * @this {ffbForm}
     * @param {form} form
     * @param {object} val
     * @return {valid : {boolean}, focusTo : {object}}
     */
    _.isValid = function(form, val) {

        _dbg && console.log('== ffbForm', 'isValid');
        _dbg && console.log('@param {object} form', form);
        _dbg && console.log('@param {object} val', val);

        var isError = false;
        var isValid = true;
        var focusTo = null;
        var formId  = form.attr('id');

        // Check validator activity
        if ($(val).hasClass('disabled')) return {'valid' : true};

        // Get validators data
        var valsData = $(val).attr('data-validator');
        if (!valsData) return {'valid' : true};

        valsData = valsData.split(';');
        if (valsData.length === 0) return {'valid' : true};

        // Get validator type, targetName
        $(valsData).each(function(k, valInfo) {

            _dbg && console.log('k, valInfo', k, valInfo);

            // Get validator property
            var valProp = valInfo.replace(/,\s*/g, ',').match(/^([A-z0-9-_\[\]]{1,}),([a-z]{1,}),?(.*)$/);
            if (!valProp || valProp.length < 2) {
                _dbg && console.log('no valProp or less than two items');
                return;
            }

            // Get target, validator type
            var targetName = valProp[1];
            var valType    = valProp[2];
            var valOpts    = null;
            if (valProp.length === 4) {
                valOpts = valProp[3];
            }
            var target     = form.find('[name="' + targetName + '"]');

            // If no target return
            if (target.length === 0) {
                _dbg && console.log('could not find target');
                return {'valid' : true};
            }

            // check validator disabled from target
            if (target.hasClass('validator-disabled')) {
                _dbg && console.log('validator is disabled');
                return {'valid' : true};
            }

            // Get target value
            var value = _.getValue(target, form);
            if (typeof value === 'string') value = $.trim(value);

            // Check validator type
            switch (valType) {
                case 'required':
                    var placeholder = (_.placeholders[formId] && _.placeholders[formId][targetName]) ? _.placeholders[formId][targetName]:null;
                    if (!value || value === '' || value === placeholder) isError = true;
                    break;
                case 'email':
                    var emailRegEx = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    if (value && !value.match(emailRegEx)) isError = true;
                    break;
                case 'confirm':
                    var confirmTo = form.find('[name="' + valOpts + '"]');
                    if (confirmTo.length > 0 && value !== confirmTo.first().val()) isError = true;
                    break;
                case 'length':
                    var minMax = valOpts.split(',');
                    if (typeof value === 'string') {
                        var valueLength = $.trim(value).length;
                        // invalid if there is a value and it is shorter than defined
                        if (minMax[0] && valueLength < minMax[0] && 0 < valueLength) isError = true;
                        // invalid if value is longer than defined
                        if (minMax[1] && valueLength > minMax[1]) isError = true;
                    }
                    _dbg && console.log('opts', valOpts);
                    _dbg && console.log('value', value.length, value);
                    _dbg && console.log('isError', isError);
                    break;
                case 'url':
                    var urlRegEx = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/;
                    if (!value.match(urlRegEx)) isError = true;
                    break;
                case 'number':
                    var numValue = [];
                    if (typeof value !== 'object') {
                        numValue.push(value);
                    }
                    for (var r = 0; r < numValue.length; r++) {
                        if (numValue[r]) {
                            var numberRegEx = /^-?\d+([,.]{0,5}\d+)?$/g;
                            if (!numValue[r].toString().match(numberRegEx)) isError = true;
                        }
                    }
                    break;
                case 'integer':
                    var intValue = [];
                    if (typeof value !== 'object') {
                        intValue.push(value);
                    }
                    for (var g = 0; g < intValue.length; g++) {
                        if (intValue[g]) {
                            var integerRegEx = /^-?\d*$/g;
                            if (!intValue[g].toString().match(integerRegEx)) isError = true;
                        }
                    }
                    break;
                case 'regex':
                    if (typeof value === 'string' && valOpts) {
                        var tmpRegExp = new RegExp(valOpts.replace(/(^\/?|\/?$)/g, ''));
                        if (!value.match(tmpRegExp)) isError = true;
                    }
                    break;
                case 'date':
                    var locale = valOpts;
                    if (typeof value === 'string' && locale) {

                        if (value.length === 0) return;

                        var dateRegEx = null;
                        switch (locale) {
                            case 'us':
                                dateRegEx = /^(\d{1,4})?\-(\d{1,2})?\-(\d{1,2})$/ig;
                                break;
                            case 'de':
                                dateRegEx = /^(\d{1,2})?\.(\d{1,2})?\.(\d{1,4})$/ig;
                                break;
                        }

                        if (!dateRegEx) return;

                        var dateMatch = value.match(dateRegEx);
                        if (dateMatch) {

                            var day   = null;
                            var month = null;

                            switch (locale) {
                                case 'us':

                                    var dateArray = dateMatch[0].split('/');
                                    month = dateArray[1];
                                    day   = dateArray[2];
                                    break;
                                case 'de':

                                    var dateArray = dateMatch[0].split('.');
                                    day   = dateArray[0];
                                    month = dateArray[1];
                                    break;
                            }

                            if (day < 1 || day > 31) isError = true;
                            if (month < 1 || month > 12) isError = true;

                        } else isError = true;
                    }
                    break;
            }

            // Show errors and invalid
            if (isError) {

                // set invalid class
                target.each(function(j, t) {

                    $(t).addClass('invalid');

                    // check custom select
                    $('#' + $(t).attr('id') + '-custom').addClass('invalid');

                    // check custom checkbox
                    if ('checkbox' === $(t).attr('type')) {
                        $(t).next().addClass('invalid');
                    }
                });

                // show validator text
                $(val).removeClass('hide');

                // validation failed
                isValid = false;

                // set element for focus
                focusTo = $(target).last();
            }
        });

        _dbg && console.log('isFormValid', {'valid' : isValid, 'focusTo' : focusTo});

        return {'valid' : isValid, 'focusTo' : focusTo};
    }

    /**
     * Validate form by validators.
     *
     * Possible data-validator:
     * [
     *      required,
     *      email,
     *      confirm,
     *      number,
     *      integer,
     *      length
     * ]
     *
     * Validators with CSS class 'disabled' will be ignored.
     *
     * data-validator structure:
     * [
     *      (targetName),(validatorType),(etc.);
     *      (targetName),(validatorType),(etc.);
     *      ...
     * ]
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} string Dom id or jQuery form object
     * @param {bool} withTooltip Boolean for error tooltip
     * @return {boolean}
     */
    _.validate = function(form, withTooltip) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return false;

        // Clear form
        _.clear(form);

        // Validation result
        var isValid = true;
        var focusTo = null;

        // Get validators
        var vals = form.find('.validator');
        vals = vals.get().reverse();

        // Check validators by type
        $(vals).each(function(i, val) {

            var result = _.isValid(form, val);
            if (result.valid === false) {
                isValid = result.valid;
                focusTo = result.focusTo;

                // set validator text to element
                if (withTooltip) {
                    _initValidatorTooltip(focusTo, $(val).text());
                }
            }
        });

        // Set focus to last invalie
        if (focusTo) focusTo.focus();
        if (!isValid && form.find('.validator-message').length > 0) {
            form.find('.validator-message').first().removeClass('hide');
        }

        return isValid;
    }

    /**
     * Search for form fields and show invalid.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} String Dom id or form jQuery object
     * @param {array} Array of Elements names
     * @param {bool} withTooltip Boolean for error tooltip
     * @return {form}
     */
    _.assignInvalid = function(form, fields, withTooltip) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        // Get validators
        var vals = form.find('.validator');

        // Check validators targets in fields
        vals.each(function(i, val) {

            // Check validator activity
            if ($(val).hasClass('disabled')) return;

            // Get validators data
            var valsData = $(val).attr('data-validator').split(';');
            if (valsData.length === 0) return;

            // Get validator type, targetName
            $(valsData).each(function(k, valInfo) {

                // Get validator property
                var valProp = valInfo.replace(/,\s*/g, ',').match(/^([A-z0-9-_\[\]]{1,}),([a-z]{1,}),?(.*)$/);
                if (!valProp || valProp.length < 2) return;

                // Get target, validator type
                var targetName = valProp[1];
                var valType    = valProp[2];
                var valOpts    = null;
                if (valProp.length === 4) {
                    valOpts = valProp[3];
                }

                // Get target, validator type
                if ($.inArray(targetName, fields) >= 0) {

                    var target = form.find('[name="' + targetName + '"]');

                    // If no target return
                    if (target.length === 0) return;

                    // Show span and mark field as invalid
                    $(val).removeClass('hide');
                    target.each(function(j, t) {

                        $(t).addClass('invalid');

                        // Update/init validator tooltip
                        if (withTooltip) {
                            _initValidatorTooltip($(t), $(val).text());
                        }

                        // user search control is inplace
                        if (target.hasClass('usersearch')) {
                            UserSearch.prototype.setInvalid(target);
                        }
                    });
                }
            });
        });

        return form;
    }

    /**
     * Get from values by Id.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} String Dom id or form jQuery object
     * @param {string} convertTo String [json, string] return encoded json
     * @return {(object|string)}
     */
    _.getValues = function(form, convertTo) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        // Prepare values object
        var values = {};

        // Get form elements
        var elements = form.find(':input:not(.ffb-form-not-value)');

        // iterate elements
        elements.each(function(i, element) {

            var value = _.getValue(element, form);
            if (value !== null && value !== undefined) {

                if (element.name.indexOf("-fake") == -1 && element.name.indexOf("-value") == -1) {

                    switch(typeof value) {
                        case 'object':
                            if (value.length > 0) values[element.name] = value;
                            break;
                        default:

                            if (_endsWith(element.name, '[]')) {

                                if (!values[element.name]) {
                                    values[element.name] = value;
                                } else if (typeof values[element.name] !== 'object') {
                                    var tmp = values[element.name];
                                    values[element.name] = [tmp, value];
                                } else {
                                    values[element.name].push(value);
                                }

                            } else {
                                values[element.name] = value;
                            }
                    }
                }

            }

        });

        if (convertTo) {
            //if (convertTo == 'json') return Object.toJSON(values);
            if (convertTo === 'string') return $.param(values);
        }

        return values;
    }

    /**
     * Set values to form from array.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} form
     *      Dom id or form jQuery object
     * @param {object} values
     *      array name=>value array
     * @return {form}
     */
    _.assignValues = function(form, values) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return null;

        var elements = form.find(':input');

        // iterate elements
        elements.each(function(i, el) {

            if (values[el.name]) {

                // Set value
                switch (el.tagName.toUpperCase()) {
                    case 'INPUT':
                        switch (el.type) {
                            case 'radio':
                            case 'checkbox':
                                if ($(el).val() === values[el.name]) {
                                    el.checked = "checked";
                                }
                                break;
                            default:
                                $(el).val(values[el.name]);
                                break;
                        }
                        break;
                    case 'SELECT':
                        $(el).val(values[el.name]);
                        break;
                    case 'TEXTAREA':
                        // set value to hidden textarea
                        $(el).val(values[el.name]);
                        // set value to TinyMCE if it exists
                        if ('undefined' !== typeof tinymce) {
                            tinymce.get(el.id).setContent(values[el.name]);
                        }
                        // set value to CKEditor if it exists
                        //if ($('cke_' + el.id)) {
                        //    CKEDITOR.instances[el.id].setData(values[el.name]);
                        //}
                        break;
                    default:
                        break;
                }

            }

        });

        return form;
    }

    /**
     * Check is form changed.
     *
     * @public
     * @this {ffbForm}
     * @param {form|string} String Dom id or form jQuery object
     * @return {boolean}
     */
    _.isChanged = function(form) {

        // Check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return false;

        // Get form elements
        var elements = form.find(':input');

        // Init return value
        var isChanged = false;

        // iterate elements
        elements.each(function(i, el) {

            // Check for changes
            switch (el.tagName.toUpperCase()) {
                case 'INPUT':
                    switch (el.type) {
                        case 'radio':
                        case 'checkbox':
                            if (el.checked !== el.defaultChecked) {
                                isChanged = true;
                            }
                            break;
                        default:
                            if ($(el).val() !== el.defaultValue && $(el).attr('data-placeholder') !== $(el).val()) {
                                isChanged = true;
                            }
                            break;
                    }
                    break;
                case 'SELECT':
                    var j          = 0;
                    var opt        = null;
                    var hasDefault = false;
                    while (opt = el.options[j++]) {
                        if (opt.defaultSelected) hasDefault = true;
                    }
                    j = hasDefault ? 0 : 1;
                    while (opt = el.options[j++]) {
                        if (opt.selected != opt.defaultSelected) {
                            isChanged = true;
                        }
                    }
                    break;
                case 'TEXTAREA':

                    if (   window.CKEDITOR
                        && $('cke_' + el.id)
                        && window.CKEDITOR.instances[el.id].getData() !== el.defaultValue
                    ) {
                        // CK Editor
                        isChanged = true;
                    } else if (   window.tinyMCE
                               && window.tinyMCE.get(el.id)
                               && window.tinyMCE.get(el.id).getContent() !== el.defaultValue
                    ) {
                        // Tinymce
                        isChanged = true;
                    } else if ($(el).val() != el.defaultValue) {
                        // Textarea
                        isChanged = true;
                    }
                    break;
                default:
                    break;
            }
        });

        return isChanged;
    }

    /**
     * Copy values for required tranlateble form fields from form field of
     * master language.
     *
     * @see DERTMS-804
     * @public
     * @this {ffbForm}
     * @param {form|string} form
     *      Dom id or form jQuery object
     * @param {string} masterLang
     *      abbreviation of master language
     */
    _.copyMandatoryTranslations = function(form, masterLang) {

        _dbg && console.log('== ffbForm', 'copyMandatoryTranslations');
        _dbg && console.log('@param {form|string} form', form);
        _dbg && console.log('@param {string} masterLang', masterLang);

        // check form
        if (typeof form !== 'object') form = $('#' + form);
        if (!form || form.length === 0) return;

        var formId = form.attr('id');

        // CSS class of master language container
        var classMasterLang = 'lang-' + masterLang;

        // iterate validators of translatable form fields
        // which are not part of the master language container
        _dbg && console.log('iterate validators of translatable form fields');
        form.find('.trans').not('.' + classMasterLang).find('.validator').each(function(i, val) {

            _dbg && console.log('val', val);

            // skip disabled validators
            if ($(val).hasClass('disabled')) {
                _dbg && console.log('skip disabled validators');
                return;
            }

            // get translation / language container of given element
            var trans = $(val).parents('.trans');
            // if translatable element is part of master language
            if (trans.hasClass(classMasterLang)) {
                _dbg && console.log('if translatable element is part of master language there is nothing to do');
                // there is nothing to do
                return;
            }

            // get validators data
            var valsData = $(val).attr('data-validator');
            if (!valsData) {
                _dbg && console.log('skip .. no validators data');
                return;
            }
            valsData = valsData.split(';');
            if (0 === valsData.length) {
                _dbg && console.log('skip .. validators data has no length');
                return;
            }

            _dbg && console.log('valsData', valsData);

            // get validator type, targetname
            var valuesToAssign = {};
            $(valsData).each(function(k, valInfo) {

                // get validator property
                var valProp = valInfo.replace(/,\s*/g, ',').match(/^([A-z0-9-_\[\]]{1,}),([a-z]{1,}),?(.*)$/);
                if (!valProp || 2 > valProp.length) {
                    return;
                }

                _dbg && console.log('valInfo', valInfo);

                // get name of target form field
                _dbg && console.log('get name of target form field');
                var targetName = valProp[1];
                _dbg && console.log('=>', targetName);

                // get target form field
                _dbg && console.log('get target form field');
                var target = form.find('[name="' + targetName + '"]');
                if (0 === target.length) {
                    _dbg && console.log('skip .. no target form field');
                    return;
                }

                // skip if validator is disabled due to form field
                if (target.hasClass('validator-disabled')) {
                    _dbg && console.log('skip .. validator is disabled due to form field');
                    return;
                }

                // get validator type
                _dbg && console.log('get validator type');
                var valType = valProp[2];

                // get validator options
                _dbg && console.log('get validator options');
                var valOpts = null;
                if (4 === valProp.length) {
                    valOpts = valProp[3];
                }

                // skip validators that doesn't "require" a value
                if ('required' !== valType) {
                    _dbg && console.log('skip .. validator is not required');
                    return;
                }

                // get target value
                _dbg && console.log('get target value');
                var value = _.getValue(target, form);
                if ('string' === typeof value) {
                    value = $.trim(value);
                }

                // skip if value is valid
                var placeholder = null;
                if (_.placeholders[formId] && _.placeholders[formId][targetName]) {
                    placeholder =  _.placeholders[formId][targetName];
                }
                if (value && value !== '' && value !== placeholder) {
                    _dbg && console.log('skip .. value is valid');
                    return;
                }

                // find corresponding master translation / language container
                _dbg && console.log('find corresponding master translation / language container');
                var masterTrans = trans.siblings('.' + classMasterLang);
                _dbg && console.log('masterTrans', masterTrans);

                // split form field name
                // translations[1][name]
                var parts = targetName.split(/\[[0-9]+\]/);
                _dbg && console.log('parts', parts);
                if (2 !== parts.length) {
                    _dbg && console.log('skip .. wrong form field name');
                    return;
                }

                // find corresponding master language form field
                _dbg && console.log('find corresponding master language form field');
                var masterTarget = masterTrans.find(':input[name^="' + parts[0] + '"][name$="' + parts[1] + '"]');
                if (0 === masterTarget.length) {
                    _dbg && console.log('skip could not find :input[name^="' + parts[0] + '"][name$="' + parts[1] + '"]');
                }

                // copy value from master language form field
                _dbg && console.log('copy value from master language form field');
                //target.val(masterTarget.val());
                valuesToAssign[targetName] = masterTarget.val();

            });

            _.assignValues(form, valuesToAssign);

        });

    }

}
