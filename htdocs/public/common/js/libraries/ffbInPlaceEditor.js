"use strict";

/**
 * Inplace editor
 *
 * @class
 * @constructor
 * @this {ffbInPlaceEditor}
 * @return {ffbInPlaceEditor}
 */
var ffbInPlaceEditor = function(form, onSave, onError, table, multiple, autosave, container, beforeSend) {

    var _ = this;

    /**
     * This property allows to show console logs in debug mode
     * or to suppress them in production.
     *
     * @private
     * @var {boolean}
     */
    var _dbg = false;

    /**
     *
     * @private
     * @var {object}
     */
    var _request = null;

    /**
     * Cache for edited values.
     *
     * @private
     * @var {object}
     */
    var _cache = {};

    /**
     * Event handler to be executed on error.
     *
     * @private
     * @var {function|null}
     */
    var _onError = null;

    /**
     * Event handler to be executed on save.
     *
     * @private
     * @var {function|null}
     */
    var _onSave = null;

    /**
     * The table helper.
     *
     * @private
     * @var {tableHelper|null}
     */
    var _table = null;

    /**
     * List on element name to save
     *
     * @private
     * @var {array|boolean} multiple
     */
    var _multiple = null;

    /**
     * By default all changes to inplace editors are saved automagically.
     * In order to prevent this behaviour set this property to false.
     * This can be usefull especially when using the inplace editors
     * in forms to create entities that are not yet persisted.
     *
     * @private
     * @var {boolean} autosave
     */
    var _autosave = true;

    /**
     * Ajax before send function
     *
     * @private
     * @var {function} multiple
     */
    var _beforeSend = null;

    /**
     * Object with text strings
     *
     * @private
     * @var {object}
     */
    var _translate = {
        'ok'        : ffbTranslator.translate('BTN_OK'),
        'cancel'    : ffbTranslator.translate('BTN_CANCEL'),
        'checked'   : ffbTranslator.translate('VAL_ON'),
        'none'      : '',
        'unchecked' : ffbTranslator.translate('VAL_OFF')
    }

    /**
     * Form object
     *
     * @public
     * @var {object}
     */
    _.form     = null;

    /**
     * Form value areas
     *
     * @public
     * @var {object}
     */
    _.elements = {};
    
    /**
     * HTML DOM object
     *
     * @public
     * @var {object}
     */
    _.container = null;    

    /**
     * Return translated "cancel" button w/ click event.
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @return {object} button
     */
    var _createCancelButton = function() {

        _dbg && console.log('ffbInPlaceEditor', '_createCancelButton');

        return $('<span class="editable-button cancel"/>')
            .html(ffbTranslator.translate('BTN_CANCEL'))
            .off()
            .on('click', function(e) {
                e.preventDefault();
                _.closeEditor(this);
            });
    }

    /**
     * Return translated "ok" button w/ click event.
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @return {object} button
     */
    var _createOkButton = function() {

        _dbg && console.log('ffbInPlaceEditor', '_createOkButton');

        return $('<span class="editable-button ok"/>')
            .html(ffbTranslator.translate('BTN_OK'))
            .off()
            .on('click', function(e) {
                e.preventDefault();
                _.saveValue($(this).parent().find(':input'), this);
            });
    }

    /**
     * Hide animation in indicator
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @param {object] indicator
     */
    var _hideAnimation = function(indicator) {

        var ind = $(indicator);
        if (ind.length === 0) return;

        if (ind.length > 0 && ind.hasClass('ok')) {
            ind.html(ffbTranslator.translate('BTN_OK'));
        } else {
            ind.html('');
        }
    }

    /**
     * Initializes event handlers for the DOM document.
     * @private
     *
     * @this {ffbInPlaceEditor}
     */
    var _initDocumentEvents = function() {

        $('html').on('click', function(e) {

            if (_.form) {                

                // check if form is part of DOM
                var isExist = $('form[action="' + _.form.attr('action') + '"]');
                if (isExist.length === 0) {
                    // if not so .. unset form and do nothing
                    _.form = null;
                    return;
                }

                // check lb layer
                var lb = _.form.parents('.lightbox');
                if (lb.length) {
                    var lbFade = lb.prev('.lightbox-fade');
                    if (!lbFade.hasClass('layer-1')) return;
                }                

                if (/*   $(e.target).hasClass('error')
                    || $(e.target).hasClass('upload')
                    || $(e.target).hasClass('modal')
                    || */$(e.target).parents('.editable.edit').length
                    || $(e.target).hasClass('value')
                    || $(e.target).parents('.editable.value').length
                    || $(e.target).parents('.lightbox.error').length/*
                    || $(e.target).parents('.lightbox.upload').length
                    || $(e.target).parents('.lightbox.modal').length*/
                ) {

                } else {

                    _.closeEditors();
                }
            }
        });
    }

    /**
     * Init close editor by click in document
     *
     * @private
     * @this {ffbInPlaceEditor}
     */
    var _initEvents = function() {

        //check in lightbox
        var lb = _.form.parents('.lightbox');
        if (lb.length > 0) {

            lb.on('click', function(e) {

                if (   $(e.target).parents('.editable.edit').length > 0
                    || $(e.target).hasClass('value')
                    || $(e.target).parents('.editable.value').length > 0
                ) {

                } else {
                    if (_.form) {
                        _.closeEditors();
                    }
                }
            });

            lb.prev('.lightbox-fade').on('click', function(e) {});
        }
    }

    /**
     * Init Element
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @param {object} element
     */
    var _initElement = function(element) {
        _dbg && console.log('_initElement', element);

        element = $(element);

        if (element.hasClass('inited-inplace-editor')) {
            return;
        } else {
            element.addClass('inited-inplace-editor');
        }

        //Check edit field
        var editField = element.next('.edit');
        if (editField.length === 0) return;

        //Add save/cancel buttons
        editField.append(_createOkButton());
        editField.append(_createCancelButton());

        //Init open editor for value area
        var handler = element;
        var parent = element.parent();
//        if (parent && parent.hasClass('editable')) {
//            handler = $(parent);
//        }
        handler.off().on('click', function(e) {

            if (e.target.tagName === 'A') return;
            if ($(this).hasClass('value') && $(this).hasClass('hide')) return;
            if ($(this).hasClass('row') && $(this).find('.editable.edit.hide').length === 0) return;

            ffbForm.clear(_.form);
            _.openEditor($(this).hasClass('value') ? this:$(this).find('.editable.value'));
        });
    }

    /**
     * Init elements
     *
     * @private
     * @this {ffbInPlaceEditor}
     */
    var _initElements = function() {
        _dbg && console.log('_initElements');

        _.elements = _.container.find('.editable.value');

        //Init elements and edit fields
        $(_.elements).each(function(i, el) {
            _initElement(el);
        });
    }

    /**
     * Show animation in indicator
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @param {object] indicator
     */
    var _showAnimation = function(indicator) {

        if ($(indicator).length > 0) $(indicator).html(ajax.wait);

        return ffbLightbox.showProgress({
            'title' : ffbTranslator.translate('TTL_FIELD_UPDATING'),
            'text'  : '<p>' + ffbTranslator.translate('MSG_FIELD_UPDATING') + '</p>'
        });
    }

    /**
     * Show value in view area
     *
     * @private
     * @this {ffbInPlaceEditor}
     * @param {object] element
     */
    var _showValue = function(element) {

        var parent = $(element).parents('.editable.edit').first();
        var value  = null;

        // check element type
        switch (element[0].tagName) {
            case 'INPUT' :
                switch(element[0].type) {
                    case 'radio' :
                        value = element.val();

                        // check value for radio as checkbox
                        if (parseInt(value) === 1 || parseInt(value) === 0) {
                            value = element.parents('label').text();
                        }

                        break;
                    case 'checkbox' :
                        if (element[0].checked) {
                            value = ffbTranslator.translate('VAL_ON');
                        } else {
                            value = ffbTranslator.translate('VAL_OFF');
                        }
                        break;
                    case 'button':
                    case 'submit':
                    case 'reset':
                        // no value for buttons
                        break;
                    default:
                        value = element.val();

                        // preview for colorpicker
                        if (element[0].className === 'colorpicker'){
                            var color = element.val();
                            var view = parent.prev('.editable.value.colorPicker-picker');
                            view.css('backgroundColor', color);
                            value = '';//<span style="background-color: ' + color + '">&nbsp;&nbsp;&nbsp;&nbsp</span>';
                        }

                        // preview for seriensearch
                        if (element[0].className === 'eventseriessearch'){
                            value = element.attr('data-title');
                        }

                        // check hidden is a part of checkbox
                        if (element[0].type === 'hidden') {
                            var isCheckbox = element.next('input[type="checkbox"]');
                            if (isCheckbox.length > 0) {

                                if (isCheckbox[0].checked) {
                                    value = ffbTranslator.translate('VAL_ON');
                                } else {
                                    value = ffbTranslator.translate('VAL_OFF');
                                }
                            }
                        }
                }
                break;
            case 'SELECT' :
                // get value for select or values array for multi
                if (element.val() !== '') {
                    value = element.find('option[value="' + element.val() + '"]').html();
                }
                break;
            case 'TEXTAREA' :
                value = element.val().replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
                break;
        }

        if (value === null || value === '') {
            parent.prev('.value').html(_translate.none);
        } else {
            parent.prev('.value').html(value);
        }

        // trigger an update
        parent.prev('.value').trigger('ffb.inPlaceEditor.update');
    }

    /**
     * Close editor
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object} element
     */
    _.closeEditor = function(element) {

        var parent = $(element).parents('.editable.edit');
        var inputField = _.getControl(parent.find(':input'), 'close');

        if (inputField) {

            if (_cache[inputField.attr('name')] !== null) {
                _.setValue(inputField, _cache[inputField.attr('name')]);
                _cache[inputField.attr('name')] = null;
            }

            _showValue(inputField);
        }

        parent.prev('.value').removeClass('hide');
        parent.addClass('hide');
        parent.parent().removeClass('edit-mode');
    }

    /**
     * Close opened editors
     *
     * @public
     * @this {ffbInPlaceEditor}
     */
    _.closeEditors = function() {

        _.container.find('.editable.edit:not(.hide)').each(function(i, element) {

            if ($(element).find('.invalid').length === 0) {                
                $(element).find('.editable-button.ok').trigger('click');
            } else {

                if (ffbForm.validate(_.form)) {
                    $(element).find('.editable-button.ok').trigger('click');
                }
            }
        });
    }

    /**
     * Get control value
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object} element
     * @return {mixed} value
     */
    _.getValue = function(element) {

        return ffbForm.getValue(element);
    }

    /**
     * Get control name
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {array|object} elements
     * @param {string} actionType
     * @return {object} cobntrol
     */
    _.getControl = function(elements, actionType) {

        var element = elements;
        if (elements.length > 1) {
            element = elements.last();
        }

        var row = element.parents('.row');
        if (row.hasClass('checkbox') || row.hasClass('radio')) {
            //2 inputs
            if (!element[0].checked) {
                element = elements.first();
            }
        } else if (row.hasClass('select')) {
            element = elements.first();
            if (element.attr('multiple') !== undefined && actionType === 'close') {
                element = null;
            }
        } else if (row.hasClass('fileupload')) {
            element = null;
        }

        return element;
    }

    /**
     * Open editor for value element
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object} element
     */
    _.openEditor = function(element) {

        _.closeEditors();

        var editField = $(element).next('.edit');
        if (editField.length > 0) {

            $(element).addClass('hide');
            $(element).parent().addClass('edit-mode');
            editField.removeClass('hide');
            
            // auto open upload for direct input
            if (editField.find('.fileupload-wrapper .fileupload-selectfile').length) {
                editField.find('.fileupload-wrapper .fileupload-selectfile').trigger('click');
            }

            // get edited field
            var inputField = _.getControl(editField.find(':input'), 'open');
            if (!inputField) return;

            _cache[inputField.attr('name')] = _.getValue(inputField);

            // select on open action by type
            if (inputField.hasClass('usersearch')) {
                
                // open user search
                inputField.next('.usersearch-wrapper').find('.search').trigger('click');
            } else if (inputField.hasClass('locationsearch')) {

                // open location search
                inputField.next('.locationsearch-wrapper').find('.search').trigger('click');
            } else if (inputField.hasClass('colorpicker')) {

                // open color picker
                inputField.next('.colorPicker-picker').trigger('click');
            } else if (inputField.get(0).tagName.toUpperCase() === 'SELECT') {                

                // open select list
                setTimeout(function() {
                    inputField.next('.ffbdropdown-main').find('.wrap').trigger('click');
                }, 0);
            } else {

                // focus to element
                inputField.focus();
                inputField.select();
            }
        }
    }

    /**
     * Save value via ajax
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object} element
     * @param {object} indicator
     */
    _.saveValue = function(element, indicator) {
        _dbg && console.log('ffbInPlaceEditor', 'saveValue');
        _dbg && console.log('@param {object} element', element);
        _dbg && console.log('@param {object} indicator', indicator);

        if (_request) {
            _dbg && console.log('skipped due to running request');
            return;
        }

        // prepare data
        var data      = {};
        var infoLb    = null;

        // set values
        var control = _.getControl(element, 'save');
        if (!control) {

            _.closeEditor(element);
            return;
        }

        var value = _.getValue(control);
        data[control.attr('name')] = value;
        data['isInplace'] = true;

        // check cache
        if (   value
            && typeof value === 'object'
            && _cache[control.attr('name')]
            && typeof _cache[control.attr('name')] === 'object'
        ) {

            if (_cache[control.attr('name')].toString() === value.toString()) {

                _.closeEditor(element);
                return;
            }
        } else {
            if (_cache[control.attr('name')] === value) {

                _.closeEditor(element);
                return;
            }
        }

        // check multiple
        if (_multiple) {
            for (var index in _multiple) {

                var multielm = _multiple[index];

                if (typeof multielm === 'function') {

                    data = multielm(data, element);
                } else {

                    data[index] = $(_multiple[index]).val();
                }
            }
        }

        // dont save per ajax
        if (_autosave === false) {

            _hideAnimation(indicator);

            _cache[control.attr('name')] = null;
            _.closeEditor(control);

            if (_table && $(element).hasClass('refresh-table')) {
                _table.refresh();
            }

            if (_onSave) {
                _onSave(element);
            }

            return;
        }

        // call request
        var ajax = new ffbAjax();
        _request = ajax.call(
            _.form.attr('action'),
            function(data) {

                _request = null;

                _hideAnimation(indicator);

                var json = ajax.isJSON(data);

                if (json && json.state === 'ok') {

//                    var lb = ffbLightbox.showInfo({
//                        'title'     : ffbTranslator.translate('TTL_FIELD_UPDATING'),
//                        'className' : 'success',
//                        'text'      : json.messages.join('<br />'),
//                    });

//                    setTimeout(function() {
//                        if ($('#' + lb.attr('id')).length > 0) {
//                            ffbLightbox.remove(lb.attr('id'));
//                        }
//                    }, 2000);

                    _cache[control.attr('name')] = null;
                    _.closeEditor(control);

                    ffbLightbox.remove(infoLb.attr('id'));

                    if (_table && $(element).hasClass('refresh-table')) {
                        _table.refresh();
                    }

                    if (_onSave) {
                        _onSave(element);
                    }

                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_FIELD_UPDATING'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });                    

                    // assign invalid
                    if (errorData.invalidFields) {
                        ffbForm.assignInvalid(control.parents('form'), errorData.invalidFields);

                        // close another opened editor if exist
                        $('.editable.edit:not(.hide)').each(function(i, element) {

                            if (element !== control.parents('.edit')[0]) {
                                _.closeEditor($(element).find('.editable-button.cancel'));
                            }
                            control.focus();
                        });
                    }

                    if (_onError) {
                        _onError(element);
                    }
                }
            }, {
                'accepts' : 'json',
                'type'    : 'post',
                'data'    : data,
                'beforeSend' : function() {

                    infoLb = _showAnimation(indicator);
                    if (typeof _beforeSend !== 'undefined' && _beforeSend) {
                        _beforeSend(_);
                    }
                },
                'error'   : function(xhr, state) {

                    _request = null;

                    _hideAnimation(indicator);

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_FIELD_UPDATING'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });

                    if (_onError) _onError(element);
                }
            }
        );
    }

    /**
     * Save multiple values via ajax
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object[]} elements List of elements
     * @param {object} indicator Optional
     * @param {function} callback Optional callback function
     * @todo Merge similar funtionality with _.saveValue from above
     */
    _.saveValues = function(elements, indicator, callback) {

        // Prepare data
        var data      = {};
        var infoLb    = null;
        var controls  = {};

        if (_request) {
            return;
        }

        $.each(elements, function(i, element) {

            // Set values
            var control = _.getControl(element, 'save');
            if (!control) {
                _.closeEditor(element);
                return;
            }

            var name = control.attr('name');
            var value = _.getValue(control);

            // Check cache
            if (   value
                && typeof value === 'object'
                && _cache[name]
                && typeof _cache[name] === 'object'
            ) {
                if (_cache[name].toString() === value.toString()) {
                    _.closeEditor(element);
                    return;
                }
            } else {
                if (_cache[name] === value) {
                    _.closeEditor(element);
                    return;
                }
            }

            data[name] = value;
            controls[name] = control;
        });

        if ($.isEmptyObject(data)) {
            return;
        }

        // Call request
        var ajax = new ffbAjax();
        _request = ajax.call(
            _.form.attr('action'),
            function(data) {

                _hideAnimation(indicator);

                var json = ajax.isJSON(data);

                if (json && json.state === 'ok') {

                    $.each(controls, function(name, control) {
                        _cache[name] = null;
                        _.closeEditor(control);
                    });

                    //ffbLightbox.close();
                    ffbLightbox.remove(infoLb.attr('id'));

                    $.each(elements, function(i, element) {
                        if (_table && $(element).hasClass('refresh-table')) {
                            _table.refresh();
                        }
                        if (_onSave) {
                            _onSave(element);
                        }
                    });

                    if (callback) {
                        callback(true, json);
                    }

                    _request = null;

                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_FIELD_UPDATING'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });

                    // Assign invalid
                    if (errorData.invalidFields) {
                        var formUpdated = false;
                        $.each(controls, function(name, control) {
                            if (!formUpdated) {
                                ffbForm.assignInvalid(control.parents('form'), errorData.invalidFields);
                                formUpdated = true;
                            }
                        });
                    }

                    $.each(elements, function(i, element) {
                        if (_onError) {
                            _onError(element);
                        }
                    });

                    if (callback) {
                        callback(false, errorData);
                    }

                    _request = null;
                }
            }, {
                'accepts': 'json',
                'type': 'post',
                'data': data,
                'beforeSend': function() {

                    infoLb = _showAnimation(indicator);
                    
                    if (typeof _beforeSend !== 'undefined' && _beforeSend) {
                        _beforeSend(_);
                    }
                },
                'error': function(xhr, state) {
                    _hideAnimation(indicator);

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title': ffbTranslator.translate('TTL_FIELD_UPDATING'),
                        'className': 'error',
                        'text': errorData.message
                    });

                    if (_onError) {
                        _onError(element);
                    }

                    _request = null;
                }
            }
        );
    };

    /**
     * Set value to element
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {object} element
     * @param {mixed} value
     */
    _.setValue = function(element, value) {

        element.val(value);
    }

    /**
     * Validate form by validators
     *
     * @public
     * @this {ffbInPlaceEditor}
     * @param {form|string} form
     *      DOM id or jQuery form object
     * @param {function} onSave
     *      callback
     * @param {function} onError
     *      callback
     * @param {object} table
     * @param {boolean} multiple
     * @param {boolean} autosave
     *      By default all changes to inplace editors are saved automagically.
     *      In order to prevent this behaviour set this property to false.
     *      This can be usefull especially when using the inplace editors
     *      in forms to create entities that are not yet persisted.
     */
    _.init = function(form, onSave, onError, table, multiple, autosave, container, beforeSend) {
        _dbg && console.log('ffbInPlaceEditor.init');

        // check form
        if (typeof form !== 'object') {
            _.form = $('#' + form);
        } else {
            _.form = $(form);
        }
        if (_.form.length === 0) return null;
        if (onSave !== undefined && onSave) _onSave = onSave;
        if (onError !== undefined && onError) _onError = onError;
        if (table) _table = table;
        if (multiple) _multiple = multiple;
        if (autosave === true || autosave === false) _autosave = autosave;
        if (beforeSend !== undefined && beforeSend) _beforeSend = beforeSend;
        if (typeof container !== 'undefined') {
            _.container = container;
        } else {
            _.container = _.form;
        }

        _initElements();

        _initEvents();
        
        _initDocumentEvents();
    }

    _.init(form, onSave, onError, table, multiple, autosave, container, beforeSend);

}
