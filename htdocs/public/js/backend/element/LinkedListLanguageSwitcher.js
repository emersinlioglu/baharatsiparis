"use strict";

/**
 * Element LinkedListLanguageSwitcher init
 *
 * @class
 * @constructor
 * @this LinkedListLanguageSwitcher
 */
var LinkedListLanguageSwitcher = function() {

    var _               = this;
    var _container      = null;
    _.callBack          = null;
    _.elmSwitcher       = null;

    /**
     *  hides the translations of inactive languages
     */
    _.hideInactiveTranslations = function() {

        var activeLanguage = _.getActiveLanguage();

        // form translations
        $('[class^="trans"]:not(.lang-' + activeLanguage + ')')
            .addClass('hide');

        // navigation translations
        $('.panemanager-pane-content').each(function(i, elm) {

            // remove last lang code
            elm.className = elm.className.replace(/\blang-(.*)?\b/g, '');
            // add new lang code
            elm.className += " lang-" + _.getActiveLanguageCode();
        });
    }

    /**
     * Get active language
     *
     * @returns {LinkedListLanguageSwitcher}
     */
    _.getActiveLanguage = function() {

        return _.elmSwitcher.find('li.selected').data('id');
    }

    /**
     * Get active language code
     *
     * @returns {LinkedListLanguageSwitcher}
     */
    _.getActiveLanguageCode = function() {

        return _.elmSwitcher.find('li.selected').data('iso');
    }

    /**
     * Shows the selected translation
     * @returns {undefined}
     */
    _.showSelectedTranslation = function() {

        var activeLanguage = _.getActiveLanguage();

        $('.trans.lang-' + activeLanguage)
            .removeClass('hide')
            .addClass('selected');

        _.hideInactiveTranslations();
    }

    /**
     * Init LanguageSwitcher
     *
     * @this {LanguageSwitcher}
     * @param {Object} container  Lightbox container
     */
    _.init = function() {

        // get select
        _.elmSwitcher = $('.linked-list-lang-switcher');

        // check if inited
        if (_.elmSwitcher.hasClass('inited')) {
            return;
        }

        //hide inactive translations
        _.hideInactiveTranslations();

        _.elmSwitcher.find('li').click(function(element) {

            // set selected language
            _.elmSwitcher.find('li').removeClass('selected');
            $(this).addClass('selected');

            // mark lang switcher as changed
            _.elmSwitcher.addClass('changed');

            //// reset invalid fields
            //$('.trans.hide').each(function(i, elm) {
            //    var elm = $(elm);
            //
            //    if (0 < elm.find('.invalid').length) {
            //        elm.addClass('invalid');
            //    } else {
            //        elm.removeClass('invalid');
            //    }
            //});

            // execute callBack
            if (typeof _.callBack == 'function') {
                _.callBack(
                    _.getActiveLanguage(),
                    _.getActiveLanguageCode()
                );
            }

            // show translation
            _.showSelectedTranslation();
        });

        _.elmSwitcher.addClass('inited');

    }

    _.init();
}