"use strict";

/**
 * JS for product controller
 *
 * @class
 * @constructor
 * @this ProductController
 */
var ProductController = new function() {

    var _ = this;
    var _request = null;

    /**
     * reflow
     * @returns {void}
     */
    _.reflow = function() {

        var subpane = $(myApp.pm.panes.get(1));
        var container = subpane.find('.panemanager-pane-content');
        var productvariantsCnt = container.find('.productvariants-cnt');

        if (productvariantsCnt.length > 0) {
            // calculate heights
            var halfPaneHeight = subpane.height() / 2;
            var productsHeight =  halfPaneHeight
                - subpane.find('form').first().outerHeight()
                - subpane.find('h2').first().outerHeight() - 1;

            // set height
            container.find('.ffb-accordion.navi.products')
                .css('height', productsHeight);
            productvariantsCnt
                .css('height', halfPaneHeight);
        }
    }

    /**
     * Init product variants
     * @returns {void}
     */
    _.initProductvariants = function() {
        var subpane = $(myApp.pm.panes.get(1));
        var cnt = subpane.find('.productvariants-cnt');

        // add new productvariant
        var addLink = subpane.find('.button.add.productvariant');
        addLink.click(function(e) {
            e.preventDefault();
            myApp.pm.getMainPaneContent(addLink);
            return false;
        });

        // edit productvariant
        cnt.find('.pane-navi-link').off().click(function(e) {
            e.preventDefault();
            myApp.pm.getMainPaneContent($(this));
        });

        // products overlay menu
        new ffbNavigationMenu(cnt.find('.ffb-accordion.navi.productvariants'), {
            //'copyCallBack': function () {
            //    var formUrl = myApp.pm.panes.first().find('.pane-navi-link.selected').attr('data-form-url');
            //    myApp.pm.getContent($('<a>').attr('href', formUrl));
            //},
            'deleteCallBack': function () {
                subpane.find('.pane-navi-link.product.selected').click();
            },
            'refreshNavigation' : false
        });

    }

    /**
     * Gets product variants
     */
    _.getProductVariants = function() {
        var newAjax = new ffbAjax();

        // set this class for calculation of panes height
        $(this).closest('ul').addClass('half');

        //abort previously
        if (_request) {
            _request.abort();
        }

        var subpane = $(myApp.pm.panes.get(1));
        var container = subpane.find('.panemanager-pane-content');
        var productvariantsCnt = container.find('.productvariants-cnt');
        var url = $(this).attr('href');

        _.reflow();

        //get content
        _request = newAjax.call(
            url,
            function (data) {

                // append variants html
                productvariantsCnt.addClass('active');
                productvariantsCnt.html(data);

                // init product variants
                _.initProductvariants();

                // check if product variant should be clicked
                if (window.productVariantUrl) {

                    productvariantsCnt.find('[href^="' + window.productVariantUrl +'"]').click();
                    window.productVariantUrl = null;
                }

            },
            {
                'type': 'get',
                'accepts': 'partial',
                'indicator': productvariantsCnt
            }
        );
    }

    /**
     * Init subnavi panel after it has been loaded.
     *
     * @private
     */
    _.initSubnavi = function() {

        var pm = myApp.pm;

        var self    = this;
        var subpane = $(pm.panes.get(1));

        // add new product
        var addLink = subpane.find('.button.add');
        addLink.on('click', function() {

            myApp.pm.getMainPaneContent(addLink);
            return false;
        });

        // get product variants
        subpane.find('.pane-navi-link').off().click(_.getProductVariants);

        // edit product icon
        subpane.find('.pane-navi-link-cnt .edit').click(function(e) {
            e.preventDefault();
            var link = $('<a>').attr('href' , $(this).data('form-url'));
            pm.getMainPaneContent(link);
        });

        // products overlay menu
        new ffbNavigationMenu(subpane.find('.ffb-accordion.navi.products'), {
            //'copyCallBack': function () {
            //    var formUrl = myApp.pm.panes.first().find('.pane-navi-link.selected').attr('data-form-url');
            //    myApp.pm.getContent($('<a>').attr('href', formUrl));
            //},
            //'deleteCallBack': function () {
            //    // todo
            //}
        });

        // search product form
        var form = subpane.find('.form-search-subnavi');

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
     * Inits navigation
     * @returns {undefined}
     */
    this.initNavigation = function() {

        var pm = myApp.pm;
        var pane = $(pm.panes.first());

        // clear onClick events because of the default initialization of panemanager
        pane.find('.pane-navi-link').off();
        pane.find('.entry-action').off();
        pane.find('.edit').off();

        // init edit
        pm.panes.first().find('.edit').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            var link = $('<a>').attr('href', $(this).data('form-url'));
            pm.getContent(link);

            return false;
        });

        // init add new category
        myApp.pm.panes.first().find('.button.add.category').off().click(function(e) {
            e.preventDefault();
            myApp.pm.getContent($(this));
        });

        // init click
        pm.panes.first().find('.pane-navi-link').click(function(e) {
            e.preventDefault();
            pm.getSubnavi($(this));

            var mainNaviList = $(this).closest('.main-navi-list');

            // selected-parents
            mainNaviList.find('li').removeClass('selected-parent');
            $(this).parents('li').addClass('selected-parent');
        });

        // overlay menu
        new ffbNavigationMenu(pane.find('.ffb-accordion.navi.main-navi-list'));

        //init accordion
        new ffbAccordion(pane.find('.ffb-accordion.navi'), false);
    }

    /**
     * Init index page
     *
     * @public
     * @this ProductController
     */
    this.initIndex = function() {

        // init subnavi panel after it has been loaded
        myApp.pm.initSubnavi    = this.initSubnavi;
        myApp.pm.initNavigation = this.initNavigation;

        this.initSubnavi(myApp.pm);
        this.initNavigation(true);

        // override pm functions
        myApp.pm.opt['isScrollable'] = false;
        myApp.pm.setNavigationHtml = function(entityListHtml) {
            this.panes.first()
                .find('.panemanager-pane-content')
                .html(entityListHtml);
        }
    };

};