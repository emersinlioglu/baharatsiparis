"use strict";

/**
 * ---=== Common functions ===---
 */

/**
 * Cross browser ihnerit
 *
 * @param {object} proto
 * @return {object} object
 */
var inherit = function(proto) {
    function F() {};
    F.prototype = proto;
    var object = new F;
    return object;
};
