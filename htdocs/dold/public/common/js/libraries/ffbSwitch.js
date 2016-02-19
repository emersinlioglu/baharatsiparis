"use strict";

/**
 * Switch tool
 *
 * @class
 * @constructor
 * @this ffbSwitch
 * @return ffbSwitch
 */
var ffbSwitch = function(element, onClick) {

    var _       = this;

    _.el        = null;
    _.disabled  = false;
    _.checked   = null;
    _.onClick   = null;

    /**
     * Returns if checked
     * @private
     * @returns {Boolean}
     */
    var _isChecked = function() {

        _.checked = _.el.find('input[type="checkbox"]').is(':checked');
        return _.checked;
    }

    /**
     * Init event listeners
     *
     * @private
     * @this ffbSwitch
     */
    var _initListeners = function() {

        if (_.disabled) {
            return;
        }

        _.el.find('.options span').click(function() {

            _.checked = _.checked ? false : true;

            _.el.find('input[type="checkbox"]').click();

            _.updateSwitch();

            var func = _.getFunctionsParts(_.onClick);
            if (func) func(_.el);
        });
    }

    /**
     * Update switch
     *
     * @public
     * @this ffbSwitch
     */
    _.updateSwitch = function() {

        if (_.checked) {
            _.el.find('.checked').addClass('active');
            _.el.find('.not-checked').removeClass('active');
        } else {
            _.el.find('.checked').removeClass('active');
            _.el.find('.not-checked').addClass('active');
        }
    }

    /**
     * Init switch
     *
     * @this ffbSwitch
     * @public
     * @param {object} element
     * @param {function} element
     */
    _.init = function(element, onClick) {

        //check element
        _.el = $(element);
        if (_.el.length === 0) return null;

        // check is disabled
        if (typeof _.el.find('input[type="checkbox"]').attr('disabled') !== 'undefined') {
            _.disabled = true;
        }

        _.onClick = onClick;
        _.checked = _.el.find('input[type="checkbox"]').is(':checked');

        _.updateSwitch();

        _initListeners();
    }

    /**
     * Parse function name or call function
     *
     * @param {function|string}
     * @return function|false
     */
    _.getFunctionsParts = function(functionName) {

        //Check, if function return
        if ($.isFunction(functionName) === true) return functionName;

        //If function name parse
        var func = null;

        if (functionName) {
            var parts = functionName.split('.');
            func      = window[parts[0]];
            var i     = 1;
            while(i < parts.length) {
                func = func[parts[i]];
                i++;
            }
        }

        //If function exist in window, return
        if ($.isFunction(func)) return func;
        else return false;
    }

    _.init(element, onClick);
}
