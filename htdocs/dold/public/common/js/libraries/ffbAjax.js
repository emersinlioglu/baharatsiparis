"use strict";

/**
 * Ajax factory
 *
 * @class
 * @constructor
 * @this ffbAjax
 * @return ffbAjax
 */
var ffbAjax = function() {

    var _ = this;

    /**
     * Accepts list
     *
     * @type {object}
     */
    _.accepts = {
        '*'       : '*/*',
        'json'    : 'application/json',
        'partial' : 'text/html',
        'plain'   : 'text/plain'
    }

    /**
     * Wait icons
     *
     * @type {object}
     */
    _.waitIcons = {
        'default' : '<span class="ajax-wait"></span>'
    }

    /**
     * Set defaul wait animation
     *
     * @type {string}
     */
    _.wait = _.waitIcons['default'];

    /**
     * Set Object with defaults values for reset
     *
     * @type {object}
     */
    _.defOpt  = {
        'async'       : true,
        'accepts'     : '*',
        'beforeSend'  : null,           //Function( jqXHR jqXHR, PlainObject settings )
        'complete'    : null,           //Function( jqXHR jqXHR, String textStatus )
        'data'        : {},             //Request data object
        'error'       : null,           //Function( jqXHR jqXHR, String textStatus, String errorThrown )
        'icon'        : null,           //Icon name from ajax.waitIcons
        'indicator'   : null,           //DOM element for progress animation
        'success'     : null,           //Function( PlainObject data, String textStatus, jqXHR jqXHR )
        'type'        : 'GET'           //GET|POST
    }

    /**
     * Set options to defaults values
     *
     * @type {object}
     */
    _.options = {};

    /**
     * Convert string to json object
     *
     * @public
     * @this {ffbAjax}
     * @param {string} data String to parse into json
     * @return {(number|boolean)}  json Object with answer result
     */
    _.isJSON = function(response) {

        if (response === undefined) return false;

        if (typeof response === 'object') return response;

        var result = false;
        try {
            result = $.parseJSON(response);
        } catch (e) {}

        return result;
    }

    /**
     * Encode schortcut
     *
     * @public
     * @this {ffbAjax}
     * @param {string} value
     * @return {string}
     */
    _.enc = function(value) {

        return encodeURIComponent(value);
    }

    /**
     * Parse function name or call function
     *
     * @public
     * @this {ffbAjax}
     * @param {(function|string)}
     * @return {(function|false)}
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

    /**
     * Parse ajax result and return error text
     *
     * @public
     * @this
     * @param {object|string} data
     * @return {object} result
     */
    _.parseError = function(data) {

        var result = {
            'message'       : '',
            'invalidFields' : null
        }

        var json   = _.isJSON(data);
        var text   = [];
        var fields = [];

        if (json && json.messages) {
            text.push(json.messages.join('<br />'));
            text.push('<br />');
        }
        if (json && json.invalidFields) {
            _parseInvalidFields('', json.invalidFields, text, fields);
        }
        if (!json) {
            text.push(data);
        }

        result.message = text.join('<br />');
        if (fields) {
            result.invalidFields = fields;
        }

        return result;
    }

    /**
     * Assign url to page
     *
     * @param {string} url
     * @returns {undefined}
     */
    _.assign = function(url) {

        var base     = document.getElementsByTagName('base');
        var redirect = null;

        if (!base.length || url.search('http') >= 0) {
            redirect = url;
        } else {

            var href = base[0].getAttribute('href');
            if (href[href.length - 1] !== '/' && url[0] !== '/') {
                redirect = base[0].getAttribute('href') + '/' + url;
            } else {
                redirect = base[0].getAttribute('href') + url;
            }
        }

        return window.location.assign(redirect);
    }

    /**
     * This method is responsible for the interpretation of informations about
     * invalid fields as they are created by the Zend framework. Its is meant
     * to be called recursivly so that even errors in fieldsets are displayed
     * correctly.
     *
     * @param {string} prefix to be used for naming field
     * @param {object} invalidFields that are to be reported
     * @param {array} text to be displayed in lightbox
     * @param {array} fields to be marked as invalid
     */
    var _parseInvalidFields = function(prefix, invalidFields, text, fields) {
        for (var key in invalidFields) {
            if (invalidFields.hasOwnProperty(key)) {
                if ('object' === typeof invalidFields[key]) {
                    // its an array of error messages OR an array of fields
                    // parse invalid fields recursivly
                    var name = prefix + (0 < prefix.length ? '[' + key + ']' : key);
                    _parseInvalidFields(name, invalidFields[key], text, fields);
                } else if (invalidFields[key] !== 'hide') {
                    // its an error message

                    // show label, not name if exist
                    var label = $('label[for="' + prefix + '"]');
                    if (label.length > 0) {
                        text.push(label.first().text().replace('*', '') + ' : ' + invalidFields[key]);
                    } else {
                        text.push(prefix + ' : ' + invalidFields[key]);
                    }
                    fields.push(prefix);
                } else {
                    fields.push(prefix);
                }
            }
        }
    }

    /**
     * Ajax call
     *
     * @public
     * @this ffbAjax
     * @param {string} url
     * @param {function} onSuccess
     * @param {object} options {
     *     'async'       : bool,           //[true, false]
     *     'accepts'     : '*',            //[*, plain, json]
     *     'beforeSend'  : null,           //Function( jqXHR jqXHR, PlainObject settings )
     *     'complete'    : null,           //Function( jqXHR jqXHR, String textStatus )
     *     'data'        : {},             //Request data object
     *     'error'       : null,           //Function( jqXHR jqXHR, String textStatus, String errorThrown )
     *     'indicator'   : null,           //DOM element for progress animation
     *     'type'        : 'GET',          //GET|POST
     *     'icon'        : null            //Icon type
     * } [Optional]
     * @return jQuery.ajax
     */
    _.call = function(url, onSuccess, options) {

        //Return, when is no url or onSuccess
        if (!url || !onSuccess) return false;

        //Reset options
        for (var key in _.defOpt) {
            _.options[key] = _.defOpt[key];
        }

        //Fill options with new values from user
        if (options && typeof options === 'object') {
            for (key in options) {
                _.options[key] = options[key];
            }
        }

        //Set onSuccess property from function parameter
        _.options.url     = url;
        _.options.success = onSuccess;

        //Make request
        return $.ajax(
            {
                'async'      : _.options.async,
                'url'        : _.options.url,
                'type'       : _.options.type,
                'data'       : _.options.data,
                'headers'    : {
                    'Accept' : _.accepts[_.options.accepts] ? _.accepts[_.options.accepts] : _.accepts['*']
                },
                'beforeSend' : function() {

                    //Get icon from options
                    var icon = _.options.icon ? _.waitIcons[_.options.icon] : _.wait;

                    var func = _.getFunctionsParts(_.options.beforeSend);
                    if (func) {
                        func();
                    }

                    //Show indicator, if is in options
                    if (_.options.indicator && $(_.options.indicator).length > 0) {
                        $(_.options.indicator).html(icon);
                    }
                },

                'success' : function(data) {

                    //Remove indicator if exist
                    if (_.options.indicator && $(_.options.indicator).length > 0) {
                        $(_.options.indicator).html('');
                    }

                    var func = _.getFunctionsParts(_.options.success);
                    if (func) {
                        func(data);
                    }
                },

                'error' : function(xhr, statusText) {

                    // check for redirect
                    if (   typeof xhr.responseText !== 'undefined'
                        && xhr.responseText.search(/redirectto/igm) !== -1
                    ) {
                        window.location.assign(xhr.responseText.replace('redirectto:', ''));
                        return false;
                    }

                    //Remove indicator if exist
                    if (_.options.indicator && $(_.options.indicator).length > 0) {
                        $(_.options.indicator).html('');
                    }

                    var func = _.getFunctionsParts(_.options.error);
                    if (func) {
                        func(xhr, statusText);
                    }
                }
            }
        );
    }
}

/**
 * Create default global ajax object
 *
 * @global
 * @type {ffbAjax}
 */
var ajax = new ffbAjax();
