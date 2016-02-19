"use strict";

/**
 * Element LanguageSwitcher init
 *
 * @class
 * @constructor
 * @this LanguageSwitcher
 */
var LanguageSwitcher = function(container, callBack) {

    var _               = this;
    var _callBack       = callBack;
    var _container      = container;
    var _activeLanguage = null;

    /**
     *  hides the translations of inactive languages
     */
    _.hideInactiveTranslations = function() {

        _container.find('div[class^="trans"]:not(.lang-' + _activeLanguage + ')')
            .addClass('hide');
    }

    /**
     * Init LanguageSwitcher
     *
     * @this {LanguageSwitcher}
     * @param {Object} container  Lightbox container
     */
    _.init = function() {

        // get select
        var selects = _container.find('.lang-switcher');

        for(var i = 0; i < selects.length; i++) {
            var sel = $(selects.get(i));

            // check if inited
            if (sel.hasClass('inited')) {
                continue;
            }

            //active language
            _activeLanguage = sel.val();

            //hide inactive translations
            _.hideInactiveTranslations();

            // on change
            new ffbDropdown(sel.get(0), {
                'liHeight' : 25,
                'onSelect' : function(element, value) {

                    $('.trans.hide').each(function(i, elm) {
                        var elm = $(elm);

                        if (0 < elm.find('.invalid').length) {
                            elm.addClass('invalid');
                        } else {
                            elm.removeClass('invalid');
                        }
                    });

                    if (typeof _callBack !== 'undefined') {
                        var func = ajax.getFunctionsParts(_callBack);
                        if (func) {
                            func(element, value);
                            return;
                        }
                    }

                    _activeLanguage = $(element).val();

                    $(element).closest('.lang-switcher-wrapper').addClass('changed');
                    _container.find('.trans.lang-' + _activeLanguage)
                        .removeClass('hide')
                        .addClass('selected');

                    _.hideInactiveTranslations();
                }
            });

            sel.addClass('inited');
        }
    }

    _.init();
}
