/*jshint -W117 */
"use strict";

/**
 * Js for table
 *
 * @class
 * @constructor
 * @this TableHelper
 * @param {object} container
 * @param {function} callBack for sort/paging
 * @return TableHelper
 */
var TableHelper = function(container, callBack) {
    
    this.cnt      = typeof container !== 'undefined' ? container : null;
    this.callBack = typeof callBack !== 'undefined' ? callBack : null;    
    
    if (!this.cnt) {
        return;
    }
    
    // get container
    this.cnt = $(container);
    if (this.cnt.length === 0) {
        return;
    }

    // init sort
    this.initSort();

    // init paging
    this.initPaging();
};

/**
 * Refresh table
 *
 * @public
 * @this TableHelper
 */
TableHelper.prototype.refresh = function() {

    var pageLink = this.cnt.find('.table-paging .page.active');

    if (pageLink.length > 0) {
        pageLink.trigger('click');
    } else if (this.cnt.find('.table-default tr th .title .sort.active').length > 0) {
        this.cnt.find('.table-default tr th .title .sort.active').first().trigger('click');
    } else if (this.callBack) {
        var func = ajax.getFunctionsParts(this.callBack);
        if (func) {
            func(0, null, null);
        }
    }

    this.cnt.trigger('refresh');
};

/**
 * Reload table
 *
 * @public
 * @this TableHelper
 */
TableHelper.prototype.reload = function() {

    // init sort
    this.initSort();

    // init paging
    this.initPaging();
};

/**
 * Init paging links
 *
 * @public
 * @this TableHelper
 */
TableHelper.prototype.initPaging = function() {

    var self = this;

    //Init paging
    this.cnt.find('.table-paging .page').off().on('click', function(e) {

        var activeSort = self.cnt.find('.title .sort.active');

        var page    = $(this).attr('data-page');
        var sortcon = activeSort.attr('data-sortcon');
        var sortdir = activeSort.hasClass('asc') ? 'asc':'desc';

        if (self.callBack) {
            var func = ajax.getFunctionsParts(self.callBack);
            if (func) {
                func(page, sortcon, sortdir);
            }
        }
    });
};

/**
 * Init sort links
 *
 * @private
 * @this TableHelper
 */
TableHelper.prototype.initSort = function () {

    var self = this;

    //Init sort
    this.cnt.find('.title .sort').off().on('click', function (e) {

        var activePage = self.cnt.find('.table-paging .page.active');
        var page       = activePage.length > 0 ? activePage.attr('data-page') : 0;
        var sortcon    = $(this).attr('data-sortcon');
        var sortdir    = $(this).hasClass('asc') ? 'asc' : 'desc';

        if (self.callBack) {
            var func = ajax.getFunctionsParts(self.callBack);
            if (func) {
                func(page, sortcon, sortdir);
            }
        }
    });
};
