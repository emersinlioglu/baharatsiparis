"use strict";

/**
 * JS for admin controller
 *
 * @class
 * @constructor
 * @this AdminController
 */
var AdminController = new function() {

    /**
     * Init subnavi panel after it has been loaded.
     *
     * @private
     * @param {PaneManager} pm
     */
    this.initSubnavi = function(pm) {

        var self    = this;
        var subpane = $(pm.panes.get(1));

        // init vertical scroll
        new ffbVerticalScroll(subpane.find('.scroll-container'));

        // new link
        var addLink = subpane.find('.button.add');
        addLink.on('click', function() {

            myApp.pm.getMainPaneContent(addLink);
            return false;
        });

        // search form
        var form = subpane.find('.form-search');

        // init form elements
        var fi = new FormInitializer(form, 'create');
        fi.initPlaceholders();

        // init dropdowns callback
        var dropdowns = fi.initDropdowns();
        $.each(dropdowns, function(i, dd) {

            dd.opt.onSelect = function() {
                form.trigger('submit');
            }
        });

        // init form submit
        form.on('submit', function(e) {

            //reload subnavi
            myApp.pm.getSubnavi(
                $('<a>').attr('href', $(this).attr('action')),
                function(pane) {

                    //self.initSubnavi();
                },
                ffbForm.getValues($(this))
            );

            return false;
        });
    };

    /**
     * Init index page
     *
     * @public
     * @this AdminController
     */
    this.initIndex = function() {

        // init subnavi panel after it has been loaded
        myApp.pm.onSubnaviLoad = this.initSubnavi;

        // open first link
        myApp.pm.panes.first().find('.pane-navi-link').first().click();

        // init errorlog link
        myApp.pm.panes.first().find('.errorlog').first().off().click(function(e) {
            e.preventDefault();
            myApp.pm.getContent($(this));
        });
    };
};