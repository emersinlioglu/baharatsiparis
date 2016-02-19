"use strict";

/**
 * ffbTranslator
 *
 * @class
 * @this ffbTranslator
 * @return ffbTranslator
 */
var ffbTranslator = new function() {

    var _     = this;
    var _data = {};

    /**
     * Set translations
     *
     * @public
     * @this ffbTranslator
     * @param {object} data
     */
    _.init = function(data) {

        if (typeof data === 'object') {
            for (var key in data) {
                _data[key] = data[key];
            }
        }
    }

    /**
     * Get translations
     *
     * @public
     * @this ffbTranslator
     * @return {object} data
     */
    _.getData = function(data) {

        return _data;
    }

    /**
     * Translate by key
     *
     * @public
     * @this ffbTranslator
     * @param {string} key
     * @return {string} translation
     */
    _.translate = function(key) {

        if (key && _data[key]) {
            return _data[key];
        } else {
            return key;
        }
    }

    _.init();
}
