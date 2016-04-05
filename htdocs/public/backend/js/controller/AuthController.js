"use strict";

/**
 * JS for attendee controller
 *
 * @class
 * @constructor
 * @this AuthController
 * @return AuthController
 */
var AuthController = new function() {

    var _ = this;

    /**
     * @this {AuthController}
     */
    _.initLogin = function() {

        //get login form and init
        var form = $('.form-login');

        ffbForm.initPlaceholders(form, true);

        form.on('submit', function(e) {

            if (!ffbForm.validate(form)) {
                return false;
            }

            ffbLightbox.showProgress({
                'title' : ffbTranslator.translate('TTL_LOGIN'),
                'text'  : '<p>' + ffbTranslator.translate('TTL_PLEASE_WAIT') + '</p>'
            });

            ajax.call($(this).attr('action'), function(data) {

                var result = ajax.isJSON(data);
                if (result && result.state === 'ok') {

                    if (result.redirect) {
                        if (typeof ajax.assign !== 'undefined') {
                            ajax.assign(result.redirect);
                        } else {
                            window.location.assign(result.redirect);
                        }
                    }

                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_LOGIN'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });
                    if (errorData.invalidFields) {
                        ffbForm.assignInvalid(form, errorData.invalidFields);
                    }
                }
            }, {
                'accepts' : 'json',
                'data'    : ffbForm.getValues($(this)),
                'type'    : 'post',
                'error'   : function(xhr, bar) {

                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_LOGIN'),
                        'className' : 'error',
                        'text'      : xhr.statusText
                    });
                }
            });

            return false;
        });
    };
};
