"use strict";

/**
 * Form js logic
 *
 * @class
 * @constructor
 * @this {FormObject}
 * @var {object} form
 * @var {string} viewType
 * @param {TableHelper} table
 */
var FormObject = function(form, viewType, table) {

    var _             = this;
    var _options      = null;
    var _callBack     = null;
    var _preSubmit    = null;
    var _befortAjax   = null;
    var _table        = table;
    var _viewType     = viewType;
    var _request      = null;

    _.form            = form;

    /**
     * Init submit logic
     *
     * @public
     * @this {FormObject}
     * @param {object} options {'translations' : {...}}
     * @param {function} callBack to be called after submitting the form
     * @param {function} preSubmit to be called before submitting the form
     * @param {function} befortAjax to be called before ajax call
     */
    _.initFormSubmit = function(options, callBack, preSubmit, befortAjax) {

        _callBack   = callBack;
        _options    = options;
        _preSubmit  = preSubmit;
        _befortAjax = befortAjax;

        _.form.on('submit', function(e) {

            e.stopPropagation();
            e.preventDefault();

            if (_viewType === 'view') {
                return false;
            }

            if (preSubmit) {
                var result = preSubmit(_);
                if (result === false) {
                    return result;
                }
            }

            //submit form
            _.submit.call(this, _options, _callBack, _befortAjax);

            return false;
        });
    };

    /**
     * Init button(s) as auto scrollable.
     *
     * @public
     * @this {FormObject}
     * @param {object} options {'translations' : {...}}
     * @param {object} selector
     */
    _.initScrollButton = function(options, selector) {

        var elements = _.form.find(selector);

        $(window).scroll(function() {
            elements.css('margin-top', $(this).scrollTop());
        });
    };

    /**
     * Init delete form entity logic
     *
     * @public
     * @this {FormObject}
     * @param {object} options {'translations' : {...}}
     * @param {object} selector
     */
    _.initDeleteButton = function(options, selector) {

        var elements = null;
        if (selector) {
            elements = $(selector);
        } else {
            elements = _.form.find('.delete-entity');
        }

        elements.unbind().on('click', function(e) {

            var delLink = $(this).attr('href');

            var lb = ffbLightbox.showModal({
                'title'    : options.translations.confirmTitle,
                'text'     : options.translations.confirmMsg,
                'cancelAction' : {
                    'caption'  : ffbTranslator.translate('BTN_CANCEL')
                },
                'okAction'    : {
                    'caption'  : ffbTranslator.translate('BTN_DELETE'),
                    'className': 'button red ok',
                    'callBack' : function() {

                        ffbLightbox.showProgress({
                            'title' : options.translations.lbTitle,
                            'text'  : '<p>' + options.translations.progressMsg + '</p>'
                        });

                        //call delete request
                        ajax.call(
                            delLink,
                            function(data) {

                                //parse result
                                var result = ajax.isJSON(data);

                                //if jsona and ok
                                if (result && result.state === 'ok') {

                                    ffbLightbox.showInfo({
                                        'title'     : options.translations.lbTitle,
                                        'className' : 'success',
                                        'text'      : options.translations.sucessMsg
                                    });

                                    //ffbLightbox.closeAllAfter(2000);

                                    ffbLightbox.closeAll();

                                    //Refresh list
                                    if (_table) {
                                        _table.refresh();
                                    }

                                } else {

                                    var errorData = ajax.parseError(data);
                                    ffbLightbox.showInfo({
                                        'title'     : options.translations.lbTitle,
                                        'className' : 'error',
                                        'text'      : errorData.message
                                    });
                                }
                            },
                            {
                                'accepts' : 'json',
                                'type'    : 'get'
                            }
                        );
                    }
                }
            });

            return false;
        });
    };

    /**
     * Form submit logic
     *
     * @param {object} options
     * @param {function} callBack
     */
    _.submit = function(options, callBack, befortAjax) {

        if (options === undefined) {
            options = _options;
        }
        if (callBack === undefined) {
            callBack = _callBack;
        }
        if (befortAjax === undefined) {
            befortAjax = _befortAjax;
        }

        var form = $(this);

        var isDontShowInvalidFields = typeof options.isDontShowInvalidFields !== 'undefined';
        var isShowInvalidFieldList  = typeof options.isShowInvalidFieldList !== 'undefined';
        var isShowProgress          = typeof options.showConfirmDialog === 'undefined';
        var isShowSuccess           = typeof options.isShowSuccess === 'undefined';
        var isUseFullscreen         = typeof options.fullScreenLoading !== 'undefined' && options.fullScreenLoading === true;
        var isShowResult            = typeof options.isShowResult === 'undefined';

        // DERTMS-804
        if (options.masterLang) {
            ffbForm.copyMandatoryTranslations(form, options.masterLang);
        }
        var isFormValid = ffbForm.validate(form);

        $('.lang-switcher-wrapper .ffbdropdown-main').removeClass('invalid');

        if (!isFormValid) {

            // @see DERTRA-675
            if (0 < form.find('.trans.hide .invalid').length) {
                $('.lang-switcher-wrapper .ffbdropdown-main').addClass('invalid');
            }

            if (isDontShowInvalidFields) {

                form.find('.ajax-wait').remove();
                return false;
            }

            var text = '<h3>' + ffbTranslator.translate('TTL_INVALID_FIELDS') + '</h3>';

            if (isShowInvalidFieldList) {
                var list = [];
                if (form.hasClass('label-as-fieldname')) {
                    form.find('.invalid').each(function(i, field) {
                        list.push($(field).siblings('label').first().text());
                    });
                    text += '<p>' + list.join('<br />') + '</p>';
                } else {
                    form.find('.invalid').each(function(i, field) {
                        list.push($(field).attr('name'));
                    });
                    // invalid fields should not be displayed as text
                    text += '<p>' + list.join('<br />') + '</p>';
                }
            }

            ffbLightbox.showInfo({
                'title' : options.translations.lbTitle,
                'text'  : text
            });

            return false;
        }

        // check show/hide progress
        if (isShowProgress) {

            if (isUseFullscreen) {

                ffbLightbox.showFullscreenProgress();
            } else {

                ffbLightbox.showProgress({
                    'title' : options.translations.lbTitle,
                    'text'  : '<p>' + options.translations.progressMsg + '</p>'
                });
            }
        }

        // call before ajax action
        if (befortAjax) {
            var result = befortAjax(_);
            if (result === false) {
                return result;
            }
        }

        if (_request) {
            _request.abort();
        }

        _request = ajax.call(
            form.attr('action'),
            function(data) {

                var result = ajax.isJSON(data);

                // close progress dialog if exists
                if (isShowProgress) {
                    ffbLightbox.close();
                }

                $('.lang-switcher-wrapper .ffbdropdown-main').removeClass('invalid');

                if (result && result.state === 'ok') {

                    if (isShowProgress && isShowSuccess && !isUseFullscreen) {

                        ffbLightbox.showInfo({
                            'title'     : options.translations.lbTitle,
                            'className' : 'success',
                            'text'      : result.messages.join('<br />')
                        });

                        if (!callBack) {
                            ffbLightbox.closeAllAfter(2000);
                        }
                    }

                    //Refresh list
                    if (_table) {
                        _table.refresh();
                    }

                    if (callBack) {
                        callBack(true, result);
                    }

                } else {

                    var errorData = ajax.parseError(data);

                    // check show/hide progress
                    // invalid fields should be shown
                    if (isShowProgress && isShowResult) {

                        ffbLightbox.showInfo({
                            'title'     : options.translations.lbTitle,
                            'className' : 'error',
                            'text'      : errorData.message
                        });
                    }

                    if (typeof errorData.invalidFields !== 'undefined') {
                        ffbForm.assignInvalid(form, errorData.invalidFields);
                    }

                    // @see DERTRA-675
                    if (0 < form.find('.trans.hide .invalid').length) {
                        $('.lang-switcher-wrapper .ffbdropdown-main').addClass('invalid');
                    }

                    if (callBack) {
                        callBack(false, result);
                    }
                }
            },
            {
                'accepts'   : 'json',
                'data'      : ffbForm.getValues($(this)),
                'type'      : 'post',
                'indicator' : options.indicator
            }
        );
    };
};
