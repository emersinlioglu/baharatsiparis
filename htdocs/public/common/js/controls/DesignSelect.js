/*jshint -W117 */
"use strict";

/**
 * Js for design select
 *
 * @class
 * @constructor
 * @this DesignSelect
 * @param {object} element
 * @return DesignSelect
 */
var DesignSelect = function(element) {

    var _    = this;
    var _el = null;

    /**
     * Init select events
     *
     * @private
     * @this DesignSelect
     */
    var _initEvents = function() {

        _el.unbind();
        _el.find('.wrap').on('click', function(e) {

            var list = _el.find('.design-select-list');
            if (list.hasClass('hide')) {
                list.removeClass('hide');
                _el.addClass('active');
            } else {
                list.addClass('hide');
                _el.removeClass('active');
            }
        });

        //add close by document click
        $(document).on('click', function(e) {

            if (!$(e.target).hasClass('value') && !$(e.target).hasClass('wrap')) {
                _el.find('.design-select-list').addClass('hide');
                _el.removeClass('active');
            }
        });
    };

    /**
     * Init element
     *
     * @pulic
     * @this DesignSelect
     * @param {object} element
     */
    _.init = function(element) {

        //check container
        if (typeof element !== 'object') {
            element = $('#' + element);
        }
        _el = $(element);
        if (_el.length === 0) {
            return;
        }

        _initEvents();
    };

    _.init(element);
};
