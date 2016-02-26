"use strict";

/**
 * Panes controll
 *
 * @constructor
 * @this PaneManager
 */
var PaneManager = function(elem, opt) {

    this.cnt             = null;
    this.panes           = null;
    this.request         = null;
    this.navigationMenue = null;
    this.onSubnaviLoad   = null;
    this.opt             = {
        'isScrollable' : true
    };

    /**
     * Close pane
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} pane
     */
    this.closePane = function(pane) {

        pane.addClass('panemanager-pane-closed');
        this.reflow();
    }

    /**
     * Open link in main content
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} link
     */
    this.getContent = function(link) {

        //update parent link in main navi, css
        var parentPane = link.parents('.panemanager-pane-content');
        parentPane.find('.pane-navi-link.selected').removeClass('selected');

        // set current link selected
        link.addClass('selected');

        // remove last selected
        parentPane.find('.pane-navi-link-cnt.active').removeClass('active');

        // set container active
        link.parents('.pane-navi-link-cnt').addClass('active');

        // remove selected, close panes, why?
//        if (link.parents('.ffb-accordion.navi').length === 0) {
//
//            var firstPane = this.panes.first();
//            firstPane.find('.pane-navi-link-cnt.active').removeClass('active');
//            firstPane.find('.pane-navi-link.selected').removeClass('selected');
//            firstPane.find('.overlay-wrapper').remove();
//        }

        $(this.panes.get(1)).removeClass('loaded');
        this.closePane($(this.panes.get(1)));

        //get create form
        this.getMainPaneContent(link);
    }

    /**
     * Get main pane content
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} link
     */
    this.getMainPaneContent = function(link, openedAccordion) {

        //get main pane, init ajax
        var pane     = this.panes.last();
        var naviAjax = new ffbAjax();
        var self     = this;

        //abort previously
        if (this.request) {
            this.request.abort();
        }

        //open pane to show loading
        this.openPane(pane);

        // get url
        var url = $(link).prop('tagName') === 'A' ? link.attr('href'):link.attr('data-href');

        //get content
        this.request = naviAjax.call(
            url,
            function(data) {

                //set title
                var newTitle = link.attr('data-pane-title');
                if (typeof newTitle === 'undefined') {
                    newTitle = link.text();
                }

                pane.find('> h2').html(newTitle);
                if (newTitle.length == 0) {
                    pane.find('> h2').addClass('hide');
                } else {
                    pane.find('> h2').removeClass('hide');
                }

                //set content
                pane.find('.panemanager-pane-scrollpane').html(data);

                //set as loaded, show second, close first
                pane.addClass('loaded');

                //close lightbox
                //ffbLightbox.close();

                //clear request
                self.request = null;

                // reflow
                self.reflow();

                $(openedAccordion).parent().parent().addClass('open');
                $(openedAccordion).parent().parent().prev().addClass('open');
            },
            {
                'type'      : 'get',
                'accepts'   : 'partial',
                'indicator' : pane.find('.panemanager-pane-scrollpane')
            }
        );
    }

    /**
     * Get navigation
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} link
     * @param {unknown_type} callBackUrl
     * @param {unknown_type} callBack
     * @param {unknown_type} values
     */
    this.getNavigation = function(link, callBackUrl, callBack, values) {

        //abort previously
        if (this.request) {
            this.request.abort();
        }

        //new Ajax
        var naviAjax = new ffbAjax();
        var self = this;

        //show progress
        this.openPane($(this.panes.get(1)));

        //close previously main
        this.panes.last()
            .removeClass('loaded')
            .find('> h2')
                .html('&nbsp;')
                .next('.panemanager-pane-scrollpane')
                    .html('');
        this.closePane(this.panes.last());
        this.closePane($(this.panes.get(1)));

        // get url
        var url = $(link).prop('tagName') === 'A' ? link.attr('href') : link.attr('data-href');

        //get mainnavi html
        this.request = naviAjax.call(
            url,
            function(data) {

                //get second pane
                var pane = self.panes.first();

                var result = ajax.isJSON(data);
                if (result && result.state === 'ok') {

                    //set html
                    self.setNavigationHtml(result.entityList);

                    //set as loaded
                    pane.addClass('loaded');

                    //init mainnavi
                    self.initNavigation();

                    //call callBackUrl
                    var activeElement = pane.find('.pane-navi-link[href="' + callBackUrl + '"], .pane-navi-link[data-href="' + callBackUrl + '"]');
                    if (callBackUrl && callBackUrl !== undefined &&
                        activeElement.first().length > 0
                    ) {

                        if (self.opt.isScrollable) {
                            // scroll to active element
                            var mainNaviList = activeElement.closest('.main-navi-list');
                            var offset = mainNaviList.offset().top - activeElement.offset().top;
                            mainNaviList.animate({ top: (offset)}, 'slow');
                        }

                        if (self.panes.first().find('.mainnavi-container.with-subnavi').length > 0) {
                            self.getSubnavi(pane.find('.pane-navi-link[href="' + callBackUrl + '"]').first(), callBack);
                        } else {
                            self.getContent(pane.find('.pane-navi-link[href="' + callBackUrl + '"]').first());
                        }
                    }

                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_LOAD_NAVIGATION'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });
                }

                //clear request
                self.request = null;

                // reflow
                self.reflow();
            },
            {
                'type'      : 'post',
                'data'      : values,
                'accepts'   : 'json',
                'indicator' : $(this.panes.first()).find('.ffb-accordion')
            }
        );
    };

    /**
     * Replaces the given html with entityList
     * @param {string} entityList
     * @returns {undefined}
     */
    this.setNavigationHtml = function(entityListHtml) {
        this.panes.first()
                .find('.main-navi-list')
                .replaceWith(entityListHtml);
    }

    /**
     * Get subnavigation
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} link
     * @param {Function} callBack
     * @param {array} data
     */
    this.getSubnavi = function(link, callBack, data) {

        //update parent link in main navi, css
        var parentPane = link.parents('.panemanager-pane-content');
        parentPane.find('.pane-navi-link.selected').removeClass('selected');

        // set current link selected
        link.addClass('selected');

        // remove last selected
        parentPane.find('.pane-navi-link-cnt.active').removeClass('active');

        // set container active
        link.closest('.pane-navi-link-cnt').addClass('active');

        //abort previously
        if (this.request) {
            this.request.abort();
        }

        //new Ajax
        var naviAjax = new ffbAjax();
        var self = this;

        //show progress
        this.openPane($(this.panes.get(1)));

        // get url
        var url = $(link).prop('tagName') === 'A' ? link.attr('href'):link.attr('data-href');

        //close previously main
        this.panes.last()
            .removeClass('loaded')
            .removeClass('show')
            .find('> h2')
                .html('&nbsp;')
                .next('.panemanager-pane-scrollpane')
                    .html('');
        this.closePane(this.panes.last());

        //get subnavi html
        this.request = naviAjax.call(
            url,
            function(data) {

                //get second pane
                var pane = $(self.panes[1]);

                //set title
                var newTitle = link.attr('data-pane-title');
                if (typeof newTitle !== 'undefined') {
                    pane.find('> h2').html(newTitle);
                }

                //set html
                pane.find('.panemanager-pane-content').html(data);

                //set as loaded, show second, close first
                pane.addClass('loaded');
                //self.closePane($(self.panes[0]));

                //init subnavi
                self.initSubnavi();

                //close lightbox
                ffbLightbox.close();

                //clear request
                self.request = null;

                //callBack
                if (typeof(callBack) === "function") {
                    callBack(pane);
                }

                if (self.onSubnaviLoad) {
                    self.onSubnaviLoad(self);
                }

                // reflow
                self.reflow();
            },
            {
                'type'      : 'post',
                'accepts'   : 'partial',
                'indicator' : $(this.panes.get(1)).find('.panemanager-pane-content'),
                'data'      : data
            }
        );
    };

    /**
     * Init panes navi
     *
     * @public
     * @this PaneManager
     */
    this.initNavigation = function() {

        var self = this;
        this.panes.first().find('.pane-navi-link')
            .on('click', function(e) {

                if (self.panes.first().find('.mainnavi-container.with-subnavi').length > 0) {
                    self.getSubnavi($(this));
                } else {
                    self.getContent($(this));
                }

                if (this.tagName === 'A') {
                    return false;
                }
            });

        this.panes.first().find('.ffb-accordion.navi.main-navi-list:not(.dont-init)').each(function() {
            var cnt = $(this);
            new ffbNavigationMenu(cnt);
        });
        //new ffbVerticalScroll(this.panes.first());
        //new ffbVerticalScroll(this.panes.first().find('.mainnavi-container'));
    }

    /**
     * Init panes subnavi
     *
     * @returns {undefined}
     */
    this.initSubnavi = function() {

        var pane = $(this.panes[1]);
        var self = this;

        //init tabs
        new ffbTabs(pane.find('.panemanager-pane-content'));

        //init accordions
        pane.find('.ffb-accordion.navi').each(function(i, acc) {
            new ffbAccordion($(acc));
        });

        //init click on accordion links
        pane.find('.pane-navi-link').on('click', function(e, openedAccordion) {
            pane.find('.pane-navi-link.selected').removeClass('selected');
            $(this).addClass('selected');

            self.getMainPaneContent($(this), openedAccordion);

            if (this.tagName === 'A') {
                return false;
            }
        });
    }

    /**
     * Init panes
     *
     * @public
     * @this PaneManager
     */
    this.initPanes = function() {

        var self = this;
        this.panes.each(function(i, pane) {

            //init toggle
            $(pane).find('> h2').on('click', function() {

                self.toggle($(this).closest('.panemanager-pane'));
                return false;
            });
        });
    }

    /**
     * Open pane
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} pane
     */
    this.openPane = function(pane) {

        pane.removeClass('panemanager-pane-closed');
        //this.reflow();
        $(window).resize();
    }

    /**
     * Update width for main pane
     *
     * @public
     * @this PaneManager
     */
    this.reflow = function() {

        // set panes width
        var width              = this.cnt.width();
        var panesWidth         = 0;
        var panesContentHeight = null;
        this.panes.each(function(i, pane) {

            if (!$(pane).hasClass('panemanager-pane-main')) {

                panesWidth += $(pane).outerWidth(true);
            }

            var paneContent = $(pane).find('.panemanager-pane-content');
            $(pane).css('height', '100%');
            var paneHeight = $(pane).height() - $(pane).position().top;

            // resolve wrong position top
            if (paneHeight === 0) {
                paneHeight = $(pane).height();
            }
            //if ($(pane).hasClass('panemanager-pane-main')) {
            //    console.log(paneHeight, $(pane).height(), $(pane).position().top);
            //}
            $(pane).css('height', paneHeight);
            var paneContentHeight = $(pane).height() - paneContent.position().top + 1;
            //if ($(pane).hasClass('panemanager-pane-main')) {
            //    console.log(paneContentHeight, $(pane).height(), paneContent.position().top);
            //}
            paneContent.css('height', paneContentHeight);

            //check is accordion scrollable
            var scrollableAccs = paneContent.find('.ffb-accordion.navi.scrollable');
            if (scrollableAccs.length > 0) {

                scrollableAccs.each(function(i, acc) {
                    var pHeight = paneContentHeight;

                    if ($(this).hasClass('half')) {
                        pHeight = paneContentHeight / 2;
                    }

                    $(acc).css('height', pHeight - $(acc).position().top);
                });
            }
        });

        var targetWidth = width - panesWidth;
        this.panes.last().css('width', Math.floor(targetWidth));
    }

    /**
     * Refresh main navi
     *
     * @public
     * @this PaneManager
     */
    this.refreshNavigation = function(callBackUrl, callBack) {

        var href = this.panes.first().find('.mainnavi-container').attr('data-url');

        this.getNavigation(
            $('<a>').attr('href', href),
            callBackUrl,
            callBack
        );
    }

    /**
     * Open/close pane
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} pane
     */
    this.toggle = function(pane) {

        //disable click for not loaded and main
        if (!pane.hasClass('loaded') || pane.hasClass('panemanager-pane-main')) {
            return;
        }

        if (pane.hasClass('panemanager-pane-closed')) {
            this.openPane(pane);
        } else {
            this.closePane(pane);
        }

        $(window).resize();
        //this.reflow();
    }

    /**
     * Init pane manager
     *
     * @public
     * @this PaneManager
     * @param {HTMLElement} container
     */
    this.init = function(container, opt) {

        this.cnt = container;

        this.panes = this.cnt.find('.panemanager-pane');
        this.initPanes();
        this.initNavigation();
        this.reflow();
        var self = this;

        //Init options
        for (var key in opt) {
            if(this.opt[key] !== undefined) this.opt[key] = opt[key];
        }

        $(window).on('resize', function() {
            self.reflow();
        });
    }

    this.init(elem, opt);
}
