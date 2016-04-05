/*jshint -W117 */
"use strict";

/**
 * Js for OwnContentSelect
 *
 * @class
 * @constructor
 * @this {OwnContentSelect}
 * @param {object} element
 * @return {OwnContentSelect}
 */
var OwnContentSelect = function(element) {

    var _   = this;
    var _el = null;

    /**
     * Init select events
     *
     * @private
     * @this {OwnContentSelect}
     */
    var _initEvents = function() {

        _el.on('click', function(e) {
            e.preventDefault();

            _el.toggleClass('active');
            return false;
        });

        _el.on('mouseleave', function() {

            _el.removeClass('active');
        });
    };

    /**
     * Init element
     *
     * @pulic
     * @this {OwnContentSelect}
     * @param {object} element
     */
    _.init = function(element) {

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
