"use strict";

/**
 * JS for product controller
 *
 * @class
 * @constructor
 * @this IndexController
 */
var IndexController = new function() {

    var _ = this;
    var _request = null;

    /**
     * Init index page
     *
     * @public
     * @this ProductController
     */
    this.initIndex = function() {

        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'click hover'
        });
    };

};