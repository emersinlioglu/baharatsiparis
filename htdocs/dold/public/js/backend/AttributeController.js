"use strict";

/**
 * JS for attribute controller
 *
 * @class
 * @constructor
 * @this AttributeController
 */
var AttributeController = new function () {

    var _ = this;
    this.request = null;

    /**
     * Init subnavi panel after it has been loaded.
     *
     * @private
     * @param {PaneManager} pm
     */
    this.initSubnavi = function (pm) {

        var self = this;
        var subpane = $(pm.panes.get(1));

        // new link
        var addLink = subpane.find('.button.add');
        addLink.on('click', function () {
            myApp.pm.getMainPaneContent(addLink);
            return false;
        });

        // init navi-link
        subpane.find('.pane-navi-link').off().on('click', function (e, openedAccordion) {
            subpane.find('.pane-navi-link.selected').removeClass('selected');
            $(this).addClass('selected');

            pm.getMainPaneContent($(this), openedAccordion);

            if (this.tagName === 'A') {
                return false;
            }
        });

        // init attributes navigation menu
        new ffbNavigationMenu(subpane.find('.ffb-accordion.navi.attributes'), {
            //'copyCallBack': function () {
            //},
            'deleteCallBack': function () {
                subpane.find('.form-search-attribute-subnavi').submit();
            },
            'refreshNavigation': false
        });

        // init subnavi search form
        var serachForm = $('.form-search-attribute-subnavi');
        serachForm.on('submit', function(e) {

            e.stopPropagation();
            e.preventDefault();

            var href = $(this).attr('action');
            var values = ffbForm.getValues(serachForm);

            var link = $('<a>').attr('href', href);

            //reload subnavi
            myApp.pm.getSubnavi(
                link,
                function(pane) {

                },
                values
            );

            return false;
        });

        // init Search
        //_.initAttributeSearch(subpane, pm);

        // init Attributes
        _.initAssignmentCheckboxes();

        // init AttributeGroups
        _.initAttributeGroups();

    }

    /**
     * Init panes navi
     *
     * @public
     * @this PaneManager
     */
    this.initNavigation = function () {

        var pm = myApp.pm;

        // init tabs
        new ffbTabs($('.main-navi-pane'));

        // init click on menu item
        pm.panes.first().find('.pane-navi-link').off().click(function (e) {
            e.preventDefault();
            pm.getSubnavi($(this));
        });

        // init edit
        pm.panes.first().find('.edit').click(function (e) {
            e.preventDefault();
            var link = $('<a>').attr('href', $(this).data('form-url'));
            pm.getContent(link);
        });

        // init add new template
        // init add new attribute-group
        pm.panes.first().find('.button.add.attribute-group, .button.add.template').click(function (e) {
            e.preventDefault();
            myApp.pm.getContent($(this));
        });

        // init attribute-groups navigation menu
        new ffbNavigationMenu(myApp.pm.panes.first().find('.attribute-groups .ffb-accordion.navi'), {
            'copyCallBack': function () {
                var formUrl = myApp.pm.panes.first().find('.pane-navi-link.selected').attr('data-form-url');
                myApp.pm.getContent($('<a>').attr('href', formUrl));
            },
            'deleteCallBack': function () {
                // todo
            }
        });
        // init templates navigation menu
        new ffbNavigationMenu(myApp.pm.panes.first().find('.templates .ffb-accordion.navi'), {
            'copyCallBack': function () {
                // open templates tab
                myApp.pm.panes.first().find('.tab.templates').click();
            },
            'deleteCallBack': function () {
                // open templates tab
                myApp.pm.panes.first().find('.tab.templates').click();
            }
        });
    }

    /**
     * Refreshes the navigation
     * @returns {undefined}
     */
    this.getNavigation = function (activeElementUrl) {

        var mainNavi = $(myApp.pm.panes.get(0));

        var navigationDataUrl = mainNavi
                .find('.mainnavi-container:not(.hidden)')
                .data('url');

        myApp.pm.getNavigation(
                $('<a>').attr('href', navigationDataUrl),
                activeElementUrl,
                function () {

                    _.initNavigation();
                }
        );
    }

    /**
     * Init attribute actions
     *
     * @public
     * @this AttributeController
     */
    this.initAssignmentCheckboxes = function () {

        var subpane = $(myApp.pm.panes.get(1));
        var pm = myApp.pm;

        //init click on attribute checkboxes
        subpane.find('.attributes .pane-navi-link-cnt input[type="checkbox"]').on('click', function (e) {

            var self = this;
            var checked = $(this).is(':checked');

            var naviAjax = new ffbAjax();
            //var self     = this;

            //abort previously
            if (this.request) {
                this.request.abort();
            }

            var url = $(this).attr('data-href');
            //get content
            this.request = naviAjax.call(
                    url,
                    function (data) {

                        var result = ajax.isJSON(data);

                        // trigger reload content area
                        $(self).siblings('a.pane-navi-link').click();

                        if (result.state !== 'ok') {
                            var errorData = ajax.parseError(data);
                            ffbLightbox.showInfo({
                                'title': ffbTranslator.translate('TTL_ERROR'),
                                'className': 'error',
                                'text': errorData.message
                            });
                        }
                    },
                    {
                        'data': {assignAttribute: checked},
                        'type': 'post',
                        'accepts': 'json'
                    }
            );
        });
    };

    /**
     * Init attributeGroups actions
     *
     * @public
     * @this AttributeController
     */
    this.initAttributeGroups = function () {

        var subpane = $(myApp.pm.panes.get(1));

        //init click on attribute checkboxes
        subpane.find('.attribute-groups .pane-navi-link-cnt input[type="checkbox"]').on('click', function (e) {

            var self = this;
            var checked = $(this).is(':checked');

            var naviAjax = new ffbAjax();
            //var self     = this;

            //abort previously
            if (this.request) {
                this.request.abort();
            }

            var url = $(this).attr('data-href');
            //get content
            this.request = naviAjax.call(
                url,
                function (data) {

                    var result = ajax.isJSON(data);

                    // trigger reload content area
                    $(self).siblings('a.pane-navi-link').click();

                    if (result.state !== 'ok') {
                        var errorData = ajax.parseError(data);
                        ffbLightbox.showInfo({
                            'title': ffbTranslator.translate('TTL_ERROR'),
                            'className': 'error',
                            'text': errorData.message
                        });
                    }
                },
                {
                    'data': {assignAttribute: checked},
                    'type': 'post',
                    'accepts': 'json'
                }
            );
        });
    };

    /**
     * Init attribute search
     *
     * @public
     * @this AttributeController
     */
    this.initAttributeSearch = function (subpane, pm) {
        //        // search form
//        var form = subpane.find('.form-search');
//
//        // init form elements
//        var fi = new FormInitializer(form, 'create');
//        fi.initPlaceholders();
//
//        // init form submit
//        form.on('submit', function(e) {
//
//            //reload subnavi
//            myApp.pm.getSubnavi(
//                $('<a>').attr('href', $(this).attr('action')),
//                function(pane) {
//
//                    //self.initSubnavi();
//                },
//                ffbForm.getValues($(this))
//            );
//
//            return false;
//        });
    }


    /**
     * Init index page
     *
     * @public
     * @this AdminController
     */
    this.initIndex = function () {

        // init navigation & subnavi panel
        myApp.pm.initNavigation = this.initNavigation;
        myApp.pm.onSubnaviLoad = this.initSubnavi;

        this.initNavigation();
        this.initSubnavi(myApp.pm);
    };

};