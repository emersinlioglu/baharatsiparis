"use strict";

/**
 * Form Product init
 *
 * @class
 * @constuctor
 * @this ProductForm
 * @var {object} form
 */
var ProductForm = function(form) {

    var _ = this;
    _.form = form;
    _.request = null;
    var _dropdowns = {};

    /**
     * Init tinyMCE
     * @param {HTMLElement} element
     */
    _.initTiny =  function() {

        // prepare default values
        var plugins      = [
            //'autolink link image'
        ];
        var toolbar      = [
            'bold italic underline',
            'bullist'
        ];

        // get variable from global if exist
        if ('_gProjectConfig' in window) {

            var tinyConf = window._gProjectConfig.tinymce;

            if (tinyConf.plugins !== undefined) {
                plugins = tinyConf.plugins;
            }

            if (tinyConf.toolbar !== undefined) {
                toolbar = tinyConf.toolbar;
            }
        }

        tinymce.remove();

        // get elements
        var els = _.form.find('textarea');
        els.each(function(i, el) {

            // check upload conf
//            var uplconf     = ajax.isJSON($(el).attr('data-uploadconf'));
//            var fileBrowser = null;
//            if (uplconf) {
//                fileBrowser = _fileBrowser;
//            }

            if (typeof $(el).attr('id') === 'undefined') {
                $(el).attr('id', 'edt' + parseInt((1 + Math.random() * 10000)).toString(16));
            }

            // init editor
            tinymce.init({
                relative_urls         : false,
                remove_script_host    : false,
                elements              : el.id,
//                file_browser_callback : fileBrowser,
                inline                : false,
                language              : 'de',
                menubar               : false,
                mode                  : 'exact',
                plugins               : plugins,
                theme                 : 'modern',
                toolbar               : toolbar.join(' | '),
                toolbar_items_size    : 'small'
            });
        });
    };

    /**
     * Inits for toggle between the product and parent product values
     * @returns {void}
     */
    _.initInheritCheckboxes = function() {

//        // init checkbox for attribute group
//        _.form.find('.inheritFromParent').change(function() {
//
//            var accordion = $(this).closest('.accordion-content');
//            var translations = accordion.find('.trans:not(.hide)');
//            var isInheritedHiddenFields =  translations.find('input[type="hidden"][name*="isInherited"]');
//
//            if($(this).is(":checked")) {
//               //'CHECKED' EVENT CODE
//
//               // set all attribute values inherited
//                isInheritedHiddenFields.val(1);
//
//                // show parent attribute values
//                translations.find('.row.view').removeClass('hide');
//                translations.find('.row:not(.view)').addClass('hide');
//
//            } else {
//                //'UNCHECKED' EVENT CODE
//
//                // set all attribute values inherited
//                isInheritedHiddenFields.val(0);
//
//                // hide parent attribute values
//                translations.find('.row.view').addClass('hide');
//                translations.find('.row:not(.view)').removeClass('hide');
//            }
//        });

        _.form.find('input[name*="isInherited"]').change(function() {

            var container = $(this).closest('.trans');
            if($(this).is(":checked")) {
               //'CHECKED' EVENT CODE

                // show parent attribute values
                container.find('.row.view').removeClass('hide');
                container.find('.row:not(.view):not(.is-inherited)').addClass('hide');

            } else {
                //'UNCHECKED' EVENT CODE

                // hide parent attribute values
                container.find('.row.view').addClass('hide');
                container.find('.row:not(.view):not(.is-inherited)').removeClass('hide');
            }
        });
    }

    /**
     *
     * @returns {this}
     */
    _.initTooltips = function(elements) {
        // init tooltips
        elements.each(function(i, inp) {
            var tooltip = new ffbTooltip(inp);
            $(inp).off('mouseover');
            $(inp).off('mouseout');
            $(inp).on('click', function() {

                var el = $(this);

                // title
                var table = el.closest('table');
                var title = $(table.find('th').get(el.index()));

                // content
                var content = el.data('tooltip') !== '' ? el.data('tooltip') : '&nbsp';

                ffbLightbox.showInfo({
                    'title': title.text(),
                    'className': 'attribute-value-preview',
                    'text': '<p>' + el.data('tooltip') + '</p>'
                });
            });

        });
    }

    /**
     * Adds or updates the log list.
     *
     * @param {string} timeperiod
     * @param {int} offset
     * @param {boolean} addToExisting
     * @returns {this}
     */
    _.getLog = function(timeperiod, offset, addToExisting) {

        var newAjax = new ffbAjax();
        if (_.request) {
            _.request.abort();
        }

        var url = _.form.find('.log-filter select').data('url');
        var data = {
            'timeperiod': timeperiod,
            'offset': offset
        };

        if (!addToExisting) {
            data.offset = 0;
        }

        var logTable = _.form.find('.table-default.attribute-value-log');
        var indicator = '';
        if (addToExisting) {
            indicator = $('<tr>').addClass('indicator');
            _.form.find('.table-default.attribute-value-log tbody')
                    .append(indicator);
        } else {
            indicator = logTable.find('tbody');
        }

        _.request = newAjax.call(
            url,
            function (data) {

                var partial = $(data);

                _.initTooltips(partial.find('[data-tooltip]'));

                if (addToExisting) {
                    // add new rows
                    partial.find('tr')
                            .hide()
                            .appendTo(logTable.find('tbody'))
                            .show('slow');
                    // remove indicator
                    indicator.remove();

                    // scroll to bottom
                    var mainPaneContent = $('.panemanager-pane-main .panemanager-pane-content');
                    mainPaneContent.animate({ scrollTop: mainPaneContent[0].scrollHeight}, 500);

                } else {
                    // update rows
                    var rows = partial.find('tr').hide();
                    logTable.find('tbody').html(rows);
                    rows.fadeIn('slow');
                }

                var moreButton = logTable.next('.more.button');
                if (logTable.find('.no-result').length === 0) {
                    moreButton.removeClass('hide');
                } else {
                    moreButton.addClass('hide');
                }

            },
            {
                'data': data,
                'type': 'post',
                'accepts': 'partial',
                'indicator': indicator
            }
        );
    }

    /**
     * Adds product assignment
     * @param {string} url
     * @returns {void}
     */
    _.addProductAssignment = function(tabContent, id) {

        var newAjax = new ffbAjax();

        if (_.request) {
            _.request.abort();
        }

        var url = tabContent.data('add-url') + id;

        _.request = newAjax.call(
            url,
            function (data) {

                var result = ajax.isJSON(data);

                if (result.state === 'ok') {

                    // set linkedProducts list
                    tabContent.find('.results')
                        .html(result.assignedProductsList);

                    // init remove icons
                    _.initRemoveProductAssignment(tabContent);

                } else {
                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title': ffbTranslator.translate('TTL_ERROR'),
                        'className': 'error',
                        'text': errorData.message
                    });
                }

            },
            {
                //'data': data,
                'type': 'post',
                'accepts': 'json'
            }
        );
    }

    /**
     * Init log filter
     * @returns {this}
     */
    _.initLogFilter = function() {

        var self = this;

        // time period filter
        if (_dropdowns.hasOwnProperty('timeperiod')) {
            _dropdowns['timeperiod'].opt.onSelect = function(element, index, value) {

                var offset = self.form.find('.table-default.attribute-value-log tr').length - 1;
                _.getLog(value, offset, false);
            }
        }

        // init more button
        self.form.find('.more.button').on('click', function() {

            var value  = self.form.find('select[name="timeperiod"] option:selected').val();
            var offset = self.form.find('.table-default.attribute-value-log tr').length - 1;
            _.getLog(value, offset, true);
        });
    }

    /**
     * Inits remove linkedProduct icon
     * @param tabContent
     * @returns {void}
     */
    _.initRemoveProductAssignment = function(tabContent) {

        tabContent.find('.remove').click(function() {

            var newAjax = new ffbAjax();
            if (_.request) {
                _.request.abort();
            }

            var removeUrl = $(this).data('remove-url');

            _.request = newAjax.call(
                removeUrl,
                function (data) {

                    var result = ajax.isJSON(data);

                    if (result.state === 'ok') {

                        // set linkedProducts list
                        tabContent.find('.results')
                            .html(result.assignedProductsList);

                        // init remove icons
                        _.initRemoveProductAssignment(tabContent);

                    } else {
                        var errorData = ajax.parseError(data);
                        ffbLightbox.showInfo({
                            'title': ffbTranslator.translate('TTL_ERROR'),
                            'className': 'error',
                            'text': errorData.message
                        });
                    }

                },
                {
                    //'data': data,
                    'type': 'post',
                    'accepts': 'json'
                }
            );
        });
    }

    /**
     * Init linkedProducts tab-content
     * @returns {void}
     */
    _.initAssignedProducts = function () {

        // init assigned products
        _.form.find('.tab-content.assigned-products').each(function(i, elm) {
            var tabContent = $(elm);

            _.initRemoveProductAssignment(tabContent);

            // init auto complete for assigned products
            tabContent.find('.search-product').keydown(function(e){
                    if(e.which == 13) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                })
                .autocomplete({
                    source: tabContent.data('search-url'),
                    minLength: 1,
                    select: function (event, ui) {

                        if (ui.item && ui.item.id) {
                            // item selected and has an id
                            _.addProductAssignment(tabContent, ui.item.id);
                        }
                    }
                });
        });

    }

    /**
     * Init multiple usage assignment checkboxes
     *
     * @public
     * @this ProductForm
     */
    this.initMultipleUsageCheckboxes = function () {

        //init click on checkboxes
        _.form.find('.tab-content.multiple-usage .category-list input[type="checkbox"]').on('click', function(e) {

            e.preventDefault();

            var naviAjax = new ffbAjax();
            // abort previously
            if (_.request) {
                _.request.abort();
            }

            var elm = $(this);
            var isChecked = elm.is(':checked');

            // toggle multiple-usage assignment
            _.request = naviAjax.call(
                elm.data('href'),
                function (data) {

                    var result = ajax.isJSON(data);

                    if (result.state === 'ok') {

                        // set choice
                        elm.prop("checked", isChecked);

                    } else {

                        // revert choice
                        elm.prop("checked", !isChecked);

                        var errorData = ajax.parseError(data);
                        ffbLightbox.showInfo({
                            'title': ffbTranslator.translate('TTL_ERROR'),
                            'className': 'error',
                            'text': errorData.message
                        });
                    }
                },
                {
                    'data': {assignAttribute: isChecked},
                    'type': 'post',
                    'accepts': 'json'
                }
            );
        });
    };

    /**
     * Init create form js
     *
     * @private
     * @this ProductForm
     */
    this.init = function() {

        // hide inactive translations
        myApp.langSwitcher.hideInactiveTranslations();
        var contentPane = $(myApp.pm.panes.get(2));

        // init form
        var fi = new FormInitializer(this.form, 'create');
        fi.initTabs(contentPane);
        _dropdowns = fi.initDropdowns();
        _.initTooltips(this.form.find('[data-tooltip]'));
        _.initLogFilter();
        _.initAssignedProducts();
        _.initMultipleUsageCheckboxes();

        // init fileuploads
        var fileuploads = [];
        this.form.find('.row.fileupload input[type="hidden"]').each(function(i, inp) {
            fileuploads.push(new FilesUpload(inp, function(fileupload) {
                // on render callback
            }, function(fileupload) {
                // on change callback
                // add inclusive class name to fix padding bottom for tmsconfig upload
                if (fileupload.lb) {
                    //fileupload.lb.addClass('tmsconfig-upload');
                }
            }));
        });

        // init tiny
        _.initTiny();

        _.initInheritCheckboxes();

        // init SimpleGallery
        _.form.find('.fileupload.view div[data-files]').each(function(i, elm) {
            new SimpleGallery(elm);
        });

        // init accordion
        new ffbAccordion(_.form.find('.ffb-accordion'));

        // on language change
        _.form.find('.active-lang .language-code').text(myApp.langSwitcher.getActiveLanguageCode());
        myApp.langSwitcher.callBack = function(langId, langCode) {

            _.form.find('.active-lang .language-code').text(langCode);
        };

        // init form submit
        var fo = new FormObject(this.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_PRODUCT'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                var mainNavi = $(myApp.pm.panes.get(0));
                var contentPane = $(myApp.pm.panes.get(2));

                if (_.form.hasClass('form-root-product')) {
                    // Root-Product
                    var categoryFormUrl = contentPane.find('.form-category').attr('action');
                    mainNavi.find('.edit[data-form-url="' + categoryFormUrl + '"]').click();

                } else {

                    // Standard-Product
                    if (typeof json.productUrl !== 'undefined') {

                        // subnavi url with selected category
                        var subnaviUrl = mainNavi.find('.pane-navi-link.selected').attr('href');

                        // subnavi url without selected category
                        if (!subnaviUrl) {
                            subnaviUrl = json.subnaviUrl;
                        }

                        myApp.refreshSubnavi(
                            $('<a/>').attr('href', subnaviUrl),
                            function(pane) {

                                window.productVariantUrl = json.productVariantUrl;

                                // refresh subnavi
                                if (!json.productVariantUrl) {
                                    var productMenuItem = $(pane).find('.pane-navi-link[href^="' + json.productUrl + '"]');
                                    productMenuItem.click();
                                    productMenuItem.next('.edit').click();
                                } else {

                                    $(pane).find('.pane-navi-link[href^="' + json.productUrl + '"]').click();
                                }

                            }
                        );

                    }
                }

            }
        });

        this.form.addClass('active');
    }

    this.init();
}