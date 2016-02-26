"use strict";

/**
 * Backend App
 *
 * @constructor
 * @this App
 */
var App = function(locale) {

    this.locale         = typeof locale !== 'undefined' ? locale:'de';
    this.pm             = null;
    this.langSwitcher   = null;

    /**
     * Init app
     *
     */
    this.init = function() {

        //init header select
        $('header .main-header-menu .design-select').each(function(i, sel) {
            new DesignSelect($(sel));
        });

        //adjust lightboxes to screen
        ffbLightbox.adjustInScreen = true;

        this.initPanesManager();

        // init navi css for min width
        $(window).on('resize', function() {

            $('header .navi').removeClass('tablet');
            if ($('header .navi .main-menu').height() > $('header .navi .main-menu li').height()) {

                $('header .navi').addClass('tablet');
            }
        });

        this.langSwitcher = new LinkedListLanguageSwitcher();
        
        // reflow
        $(window).trigger('resize');
    }

    /**
     * Init main pane forms
     *
     * @public
     * @throws {App} description
     */
    this.initPaneForms = function() {

        var self = this;

        //events pane
        var pane = $('.panemanager-pane.main-navi-pane');

        //init filter
        pane.find('.controls .design-select').each(function(i, sel) {
            new DesignSelect($(sel));
        });

        //get controls
        var controls = pane.find('.controls');
        var form = controls.find('form');

        ffbForm.initPlaceholders(form);

        var fi = new FormInitializer(form);
        fi.initDatePicker(null, null, function() {
            if (form.length > 0) {
                form.trigger('submit');
            }
        });
        var dropdowns = fi.initDropdowns();
        for (var id in dropdowns) {

            dropdowns[id].opt.onSelect = function() {
                if (form.length > 0) {
                    form.trigger('submit');
                }
            }
        }

        //init events filter
        pane.find('.controls .design-select .design-select-list li a')
            .on('click', function(e) {

                var select = $(this).parents('.design-select');
                select.find('.design-select-list').addClass('hide');
                select.find('.wrap .value').html($(this).html());
                select.find('.active').removeClass('active');
                $(this).addClass('active');

                if (form.length > 0) {
                    form.trigger('submit');
                } else {
                    self.pm.getNavigation($(this));
                }

                return false;
            });

        //init new event button
        pane.find('.controls .button.add').click(function(e) {

            self.pm.getContent($(this));
            return false;
        });

        //init search
        form.on('submit', function(e) {

            self.onSearchFormSubmit.call(self, e, form);
        });
    }

    /**
     * Init index page
     *
     * @public
     * @this {App}
     */
    this.initPanesManager = function() {

        var self = this;

        //init panemanager
        this.pm = new PaneManager($('.panemanager').first());

        setTimeout(function() {
            self.initPaneForms();
        }, 0);
    };

    /**
     * Refresh main navi
     *
     * @param {function} callBackUrl
     */
    this.refreshNavigation = function(callBackUrl, callBack) {

        this.pm.refreshNavigation(callBackUrl, callBack);
    }

    /**
     * Refreshes the subnavigation
     *
     * @param {HTMLElement} link
     * @param {function} callBack
     */
    this.refreshSubnavi = function(link, callBack) {

        this.pm.getSubnavi(link, callBack);
    }


    this.init();
}

/**
 * Search form submit callback
 *
 * @returns {Boolean}
 */
App.prototype.onSearchFormSubmit = function(e, form) {

    e.stopPropagation();
    e.preventDefault();
    
    var href = form.attr('action');
    var values = ffbForm.getValues(form);

    //check filter, get url from filter
    var activeFilter = $('.panemanager-pane.main-navi-pane .controls .design-select a.active');
    if (activeFilter.length > 0) {
        href = activeFilter.attr('href');
    }

    //check search
    if (   values.search !== undefined
        && values.search.length > 0
        && ffbForm.placeholders[form.attr('id')].search !== values.search
    ) {
        href += '/search/' + ajax.enc(values.search);
    }
    if (values.search !== undefined) {
        delete values.search;
    }

    var link = $('<a>')
            .attr('href', href);

    this.pm.getNavigation(link, null, null, values);
    return false;
}