/*jshint -W117 */
"use strict";

/**
 * Js for filtered table
 *
 * @class
 * @constructor
 * @this FilterTableHelper
 * @param {object} container
 * @param {function} callBack for sorting/paging
 * @param {function} actionCallBack actions
 * @return FilterTableHelper
 */
var FilterTableHelper = function(container, callBack, actionCallBack) {

    this.actionCallBack  = null;
    this.autoHeight      = false;
    this.autoWidth       = true;
    this.callBack        = null; // to be called after sorting or paging of table
    this.cnt             = null;
    this.expertLb        = null;
    this.resizeMouseDown = false;
    this.request         = null;
    this.updateRequest   = null;
    this.scroll          = null;
    this.th              = null; // table helper
    this.views           = [];
    this.scrollX         = 0;
    this.type            = 'expert';
    this.updateFilters   = false;
    this.editorsEnabled  = true;

    // check container
    if (typeof container !== 'object') {
        container = $('#' + container);
    }
    this.cnt = $(container);
    if (this.cnt.length === 0) {
        return;
    }

    // check callbacks
    if (typeof callBack !== 'undefined') {
        this.callBack = callBack;
    }
    if (typeof actionCallBack !== 'undefined') {
        this.actionCallBack = actionCallBack;
    }

    // init table helper
    this.initTableHelper();

    // init resize logic
    this.initResize();

    // init elements
    this.initElements();

    // update width
    this.updateWidth();

    // set initial height to parent max height
    this.updateHeight();

    // init click event for tds with editors to render editor
    this.initDocumentClick();

    // double width update
    $(window).resize();
};

/**
 * Create checkbox element
 *
 * @public
 * @this FilterTableHelper
 * @param {string} caption
 * @param {string} value
 * @return {HTMLInput}
 */
FilterTableHelper.prototype.createCheckbox = function(caption, value) {

    var chkId = 'chk' + parseInt(Math.random() * 9999) + new Date().getTime() +  parseInt(Math.random() * 9999);
    var div   = $('<div class="custom-checkbox white" />');
    var label = $('<label />')
        .attr('for', chkId)
        .html(caption);
    var input = $('<input type="checkbox" />')
        .attr('id', chkId)
        .attr('name', value)
        .attr('value', value);

    div.append(input);
    div.append(label);

    return div;
};

/**
 * Create select element
 *
 * @public
 * @this FilterTableHelper
 * @param {string} name
 * @param {object} options
 * @param {string} title
 * @return {HTMLSelect}
 */
FilterTableHelper.prototype.createSelect = function(name, options, title) {

    // create select and options
    var newDropdown = $('<select />')
        .attr('name', name)
        .attr('title', title);

    $.each(options, function (i, el) {

        var option = $('<option />');
        option
            .val(el.value)
            .text(el.title);

        newDropdown.append(option);
    });

    return newDropdown;
};

/**
 * Init table elements, filters, actions
 *
 * @public
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.initElements = function() {

    var self = this;

    // remove self init if exist
    this.cnt.find('.self-init').removeClass('self-init');

    // init dropdowns
    var fiFilters = new FormInitializer(this.cnt.find('.table-filtered-filters'));
    var fDropdowns = fiFilters.initDropdowns();

    var fiActions = new FormInitializer(this.cnt.find('.table-filtered-actions'));
    var aDropdowns = fiActions.initDropdowns();

    // init search
    this.cnt.find('.search .search').off().on('keypress', function(e) {

        if (e.which === 13) {
            e.preventDefault();
            self.refresh();
        }
    });
    this.cnt.find('.search .search-icon').off().on('click', function(e) {

        self.refresh();
    });

    // init check all
    this.cnt.find('.table-filtered-actions .select-all').off().on('click', function() {

        var check = self.cnt.find('input[type="checkbox"]:checked').length > 0 ? false : true;
        if (check) {
            self.cnt.find('.table-filtered-list tr td:first-child input[type="checkbox"]').prop('checked', true);
        } else {
            self.cnt.find('.table-filtered-list tr td:first-child input[type="checkbox"]').prop('checked', false);
        }

        // update selected rows
        self.updateTotalValues();
    });

    // init checkboxes
    this.cnt.find('.table-filtered-list input[type="checkbox"]').off().on('click', function(e) {

        e.stopPropagation();
    });

    // init (actions) if defined
    if (typeof aDropdowns.actions !== 'undefined' && typeof self.actionCallBack === 'function') {
        aDropdowns.actions.opt.onSelect = self.actionCallBack;
    }

    // init top filters
    if (   typeof fDropdowns.column !== 'undefined'
        && typeof this.callBack === 'function'
    ) {

        // onSelect (first filter)
        fDropdowns.column.opt.onSelect = function(element, value) {

            // create (second filter), get options
            var options = ajax.isJSON($(element).find('option:selected').attr('data-options'));

            // get element and clear it
            var secondFilter = $('.column-value-select');
            secondFilter.empty();

            // add options to (second filter)
            if (typeof options !== 'undefined') {

                var newDropdown = self.createSelect('columnValue', options);

                // add to filzer
                secondFilter.append(newDropdown);

                // init second filter dropdown box
                var newFilter = fiFilters.initDropdowns(secondFilter.selector + ' select');
                newFilter.columnValue.opt.onSelect = function () {

                    self.refresh();
                };
            }

            // refresh table
            self.refresh();
        };
    }

    // init update selected for footer
    this.cnt.find('.table-filtered-list tr td:first-child input[type="checkbox"]').on('click', function() {

        self.updateTotalValues();
    });

    // update total divs
    this.updateTotalValues();

    // init expert view if available
    var expert_button = this.cnt.find('.table-filtered-filters .links-and-buttons .expert');
    if (0 < expert_button.length) {

        // init button
        expert_button.off().on('click', function(e) {

            self.type = 'report' === $(e.target).attr('data-type') ? 'report' : 'expert';
            self.showExpertView();
            return false;
        });

        // init window click for helper
        $(window).on('click', function(e) {

            if (self.expertLb) {

                var buttons = self.expertLb.find('.table-filtered-filters-controls.open');
                if (buttons.length > 0) {

                    var target = $(e.target);
                    if (   target.hasClass('table-filtered-filters-controls')
                        || target.closest('.table-filtered-filters-controls').length > 0
                    ) {

                    } else {
                        buttons.removeClass('open');
                    }
                }
            }
        });
    }
};

/**
 * Creates a new table helper or just reloads it if it already exists.
 * A new table helper will be aggregated and initialized for sorting and paging.
 *
 * @public
 * @this {FilterTableHelper}
 */
FilterTableHelper.prototype.initTableHelper = function() {

    var self = this;

    // reload
    if (this.th) {

        this.th.reload();
        return;
    }

    // create new
    this.th = new TableHelper(this.cnt, function(page, sortcon, sortdir) {

        // save x scroll position
        if (self.expertLb) {
            self.scrollX = self.expertLb.find('.table-filtered-dynamic').prop('scrollLeft');
        } else {
            self.scrollX = self.cnt.find('.table-filtered-dynamic').prop('scrollLeft');
        }

        if (self.callBack) {
            var func = ajax.getFunctionsParts(self.callBack);
            if (func) {
                func(page, sortcon, sortdir);
            }
        }
    });
};

/**
 * Init fly header resize
 *
 * @public
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.initResize = function() {

    var self = this;

    // add resize div
    this.cnt.find('.table-filtered-actions .resize-handler').remove();
    this.cnt.find('.table-filtered-actions').append($('<div class="resize-handler"></div>'));

    // get parts
    var actionBarHeight = this.cnt.find('.table-filtered-actions').height();
    var listBar         = this.cnt.find('.table-filtered-list');

    // init handler
    this.cnt.find('.resize-handler')
        .on('mousedown', function(e) {

            // start drag
            self.resizeMouseDown = true;
        });

    // get wrapper
    var tableWrapper = this.cnt.find('.table-filtered-dynamic');

    // init mouse up for document
    $(document)
        .on('mouseup', function() {

            self.resizeMouseDown = false;
        })
        .on('mousemove', function(e) {

            if (!self.resizeMouseDown) {
                return;
            }

            // calculate diff
            var diff = e.pageY - tableWrapper.offset().top - actionBarHeight;

            // get min height if exist
            var minHeight = tableWrapper.css('min-height').replace('px');

            // ignore empty min heights,
            if (!isNaN(parseFloat(minHeight)) && isFinite(minHeight)) {

                //set min height
                if (diff < minHeight) {
                    diff = minHeight;
                }
            }

            // check max height
            if (diff > listBar.height() + actionBarHeight - 10) {
                return;
            }

            // update height
            tableWrapper.css({'height': diff + 'px'});

            // get widths
            var headerTable = tableWrapper.find('.table-filtered-headers');
            var twWidth     = tableWrapper.find('.table-filtered-list table').width();
            var paddingDif  = tableWrapper.width() - twWidth;

            // fix header width
            headerTable.css({'width': twWidth + 'px'/*, 'padding-right': paddingDif + 'px'*/});
        });

    // init fixed header
    tableWrapper.on('scroll', function(e) {

        $(this).find('.table-filtered-headers').css('top', $(this).scrollTop());
    });

    $(window).on('resize', function() {

        if (self.expertLb) {

            var height = self.expertLb.find('.wrapper').height() - self.expertLb.find('.wrapper > .menu').outerHeight(true);
            self.expertLb.find('.content').css('height', height);

            // update table width
            self.updateWidth(self.expertLb.find('.expert-view'));
            self.updateHeight(self.expertLb.find('.expert-view'));
        }

        self.updateWidth();

        if (self.autoHeight) {
            self.updateHeight();
        }
    });
};

/**
 * Refresh table
 *
 * @public
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.reload = function() {

    // init table helper
    this.initTableHelper();

    // init resize logic
    //this.initResize();

    // init elements
    //this.initElements();

    // update width
    this.updateWidth();

    // set initial height to parent max height
    this.updateHeight();

    if (this.expertLb) {

        this.updateExpertTable();

        // update total divs
        this.updateTotalValues(this.expertLb);

        // update scroll left
        this.expertLb.find('.table-filtered-dynamic').prop('scrollLeft', this.scrollX);
    } else {

        // update total divs
        this.updateTotalValues();

        // update scroll left
        this.cnt.find('.table-filtered-dynamic').prop('scrollLeft', this.scrollX);
    }

};

/**
 * Refresh table
 *
 * @public
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.refresh = function() {

    this.th.refresh();

    $(window).resize();
};

/**
 * Update table height to parent free height
 *
 * @public
 * @param {object} container
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.updateHeight = function(container) {

    // get container
    if (typeof container === 'undefined') {
        container = this.cnt;
    }
    
    var filters   = container.find('.table-filtered-filters');
    var actions   = container.find('.table-filtered-actions');
    var dynamic   = container.find('.table-filtered-dynamic');

    if (!dynamic.length) return;

    var elementsH = 0;
    if (filters.length) {
        elementsH += filters.get(0).offsetHeight;/*outerHeight(true);*/
    }
    if (actions.length) {
        elementsH += actions.get(0).offsetHeight;/*outerHeight(true);*/
    }

    var cntParent = container.parent();    

    dynamic.get(0).style.height = cntParent.height() - elementsH + 'px';
};

/**
 * Update total values
 *
 * @public
 * @param {object} container
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.updateTotalValues = function(container) {

    // get container
    if (typeof container === 'undefined') {
        container = this.cnt;
    }

   container.find('.total-rows-selected span').html(container.find('.table-filtered-list tbody tr:not(.empty)').length);
   container.find('.rows-selected span').html(container.find('.table-filtered-list input:checked').length);
};

/**
 * Update headers width, to adjust to cells
 *
 * @public
 * @param {object} container
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.updateWidth = function(container) {

    if (!this.autoWidth) return;

    // get container
    if (typeof container === 'undefined') {
        container = this.cnt;
    }

    // get filtered list
    var table = container.find('.table-filtered-list > table');

    // set table layout to get real width
    if (!table.length) return;
    table.get(0).style.tableLayout = 'initial';
    table.get(0).style.minWidth    = '100%';

    // get headers
    var ths = container.find('.table-filtered-headers thead tr th');
    var tds = container.find('.table-filtered-list thead tr th');

    // update headers width
    for (var i = 0; i < tds.length; i++) {

        var td    = tds[i];
        //var width = $(td).width();
        var width = td.offsetWidth;

        if (width > 0) {
            //$(ths[i]).css('width', width + 'px');
            ths[i].style.width = width + 'px';
            //$(tds[i]).css('width', width + 'px');
            tds[i].style.width = width + 'px';
        }
    };

    // set fixed layout back
    table.get(0).style.tableLayout = 'fixed';
    table.get(0).style.minWidth    = '0px';

    // set head width
    //container.find('.table-filtered-headers').get(0).style.minWidth = container.find('.table-filtered-list').width() + 'px';
    container.find('.table-filtered-headers').get(0).style.width = table.width() + 'px';
};

/**
 * Show expert view
 *
 * @public
 * @this FilterTableHelper
 */
FilterTableHelper.prototype.showExpertView = function() {

    var self = this;

    var title = ffbTranslator.translate('TTL_EXPERT_VIEW');
    if ('report' === this.type) {
        title = ffbTranslator.translate('TTL_REPORTING');
    }

    ffbLightbox.showBase({
        className : 'expert-view',
        title     : title,
        callBack  : function(lb) {

            // save lb
            self.expertLb = lb;

            // init expert view
            self.openExpertView.call(self);
        },
        onClose   : function(lbId, fadeId) {

            // close expert view
            self.closeExpertView.call(self);
        }
    });

    $('body').addClass('lb-expert-view-opened');

    $(window).resize();

    // update table dimensions
    var expert_view = this.expertLb.find('.expert-view');
    this.updateWidth(expert_view);
    this.updateHeight(expert_view);
};

/**
 * Close expert view
 *
 * @param {string} lightbox id
 * @param {string} lightboxfade id
 */
FilterTableHelper.prototype.closeExpertView = function(lbId, fadeId) {

    // remove expert data
    this.cnt.find('.expert-view-data').remove();

    // remove lightbox
    ffbLightbox.remove(this.expertLb.attr('id'));

    // remove body settings
    $('body').removeClass('lb-expert-view-opened');

    // set lb null
    this.expertLb = null;

    // refresh base table, close lb
    this.refresh();
};

/**
 * Open expert view.
 */
FilterTableHelper.prototype.openExpertView = function() {

    var self = this;

    // get parent settings
    var parentCnt = self.cnt.find('.table-default.table-filtered').first();
    var url       = parentCnt.attr('data-url');

    // create container for expert view
    var container = $('<div />')
        .attr('class', parentCnt.attr('class'))
        .addClass('expert-view');

    // create initialiser
    var fi = new FormInitializer(container);

    // create expert filters
    this.createExpertFilters(container);

    // create dynamic container
    var dynamicTable = this.cnt.find('.table-filtered-dynamic').clone().empty();
    container.append(dynamicTable);

    // create footer
    var footer = this.cnt.find('.table-filtered-actions').clone();

    // remove ffbDropdown stuff
    footer.find('.ffbdropdown-main').remove();
    footer.find('select').removeAttr('id');
    container.append(footer);

    // add container to lightbox
    this.expertLb.find('.content').empty().append(container);

    // init "check all"
    footer.find('.select-all').off().on('click', function() {

        var check = container.find('.table-filtered-list input[type="checkbox"]:checked').length > 0 ? false : true;
        if (check) {
            container.find('.table-filtered-list tr td:first-child input[type="checkbox"]').prop('checked', true);
        } else {
            container.find('.table-filtered-list tr td:first-child input[type="checkbox"]').prop('checked', false);
        }

        // update selected rows
        self.updateTotalValues(self.expertLb);
    });

    // init expert filters
    this.initExpertFilters(container);

    // init footer dropdowns
    var footerDropdowns = fi.initDropdowns(footer.find('select'));

    // init (actions) if defined
    if (   'undefined' !== typeof footerDropdowns.actions
        && 'function' === typeof self.actionCallBack
    ) {
        footerDropdowns.actions.opt.onSelect = self.actionCallBack;
    }

    // remove all actions but the export function in reporting view
    if ('report' === this.type) {
        footer.find('.action-select, label').addClass('hide');
        footer.append('<div class="button gray export">Export</div>');
        footer.find('.button.export').click(function() {
            footerDropdowns.actions.setValue('export');
        });
    }

    // init views select
    this.initViews();

    this.refresh();
};

/**
 * Get selected fields
 *
 * @return object
 */
FilterTableHelper.prototype.getFields = function() {

    var result = [];

    this.expertLb.find('.table-filtered-filters-controls.columns-view input').each(function(i, inp) {

        if ($(inp).prop('checked')) {

            result.push($(inp).attr('name'));
        }
    });

    return result;
};

/**
 * Get selected filters
 *
 * @return object
 */
FilterTableHelper.prototype.getFilters = function() {

    var result = [];

    this.expertLb.find('.table-filtered-filters select').each(function(i, sel) {

        if ($('#' + $(sel).attr('id') + '-custom:not(.hide)').length > 0) {

            result.push($(sel).attr('name'));
        }
    });

    return result;
};

/**
 * Get selected filters data
 *
 * @return object
 */
FilterTableHelper.prototype.getFiltersData = function() {

    var result = {};

    this.expertLb.find('.table-filtered-filters select').each(function(i, sel) {

        if ($('#' + $(sel).attr('id') + '-custom:not(.hide)').length > 0) {

            result[$(sel).attr('name')] = $(sel).val();
        }
    });

    return result;
};

/**
 * Update expert table from parent table, show/hide columns
 *
 * @param {ffbLightbox} lightbox
 */
FilterTableHelper.prototype.saveExpertData = function() {

    // set expert hidden in form to
    var expertData = {
        'expertView'    : true,
        'filters'       : {},
        'fields'        : {},
        'data'          : {},
        'eventpartId'   : $('.expert-views-controls select').val()
    };

    // get filters values
    expertData.filters = this.getFiltersData();

    // get showed fields
    expertData.fields = this.getFields();

    // get data from table
    expertData.data = ffbForm.getValues(this.expertLb.find('.table-filtered-dynamic'));

    // create or use existed hidden
    var expertHidden = this.cnt.find('.expert-view-data');
    if (expertHidden.length === 0) {

        expertHidden = $('<input class="expert-view-data" type="hidden" name="expertViewData" />');
        this.cnt.append(expertHidden);
    }

    // save
    expertHidden.val(JSON.stringify(expertData));
};

/**
 * Update expert table from parent table, show/hide columns
 *
 * @param {ffbLightbox} lightbox
 */
FilterTableHelper.prototype.updateExpertTable = function() {

    var self = this;

    // create dynamic container
    var dynamicTable   = this.cnt.find('.table-filtered-dynamic').clone(true);
    var lbDynaminTable = this.expertLb.find('.table-filtered-dynamic');

    // update table
    lbDynaminTable.replaceWith(dynamicTable);

    // init fixed header
    this.expertLb.find('.table-filtered-dynamic').on('scroll', function(e) {

        self.expertLb.find('.table-filtered-headers').css('top', $(this).scrollTop());
    });

    // update total divs
    // init update selected for footer
    this.expertLb.find('.table-filtered-list tr td:first-child input[type="checkbox"]').on('click', function() {

        self.updateTotalValues(self.expertLb);
    });

    // check if update filters needed
    if (this.updateFilters) {

        // get container
        var container = this.expertLb.find('.table-default.table-filtered.expert-view');

        // reinit filters and views
        this.createExpertFilters(container);
        this.initExpertFilters(container);
        this.initViews();

        // show/hide controls for eventpart
        var selectEventpart =  this.expertLb.find('.expert-views-controls > select');
        if (selectEventpart.length) {
            this.toggleControlsByEventpart(selectEventpart.val());
        }

        this.updateFilters = false;
    }

    // update visibility
    this.updateColumnsVisibility();

    if (this.editorsEnabled) {

        // init editors
        this.initEditors();
    }

    // update total divs
    this.updateTotalValues(self.expertLb);
};

/**
 * Update filters selects visibility
 */
FilterTableHelper.prototype.updateFiltersVisibility = function() {

    var self = this;

    this.expertLb
        .find('.table-filtered-filters-controls:first input').each(function(i, chk)
    {

        var select = self.expertLb.find('.table-filtered-filters select[name="' + $(chk).val() + '"]');
        if ($(chk).prop('checked')) {
            select.next('.ffbdropdown-main').removeClass('hide');
        } else {
            select.next('.ffbdropdown-main').addClass('hide');
        }
    });

    // update table width
    this.updateWidth(this.expertLb.find('.expert-view'));
    this.updateHeight(this.expertLb.find('.expert-view'));
};

/**
 * Update expert table columns visibility
 */
FilterTableHelper.prototype.updateColumnsVisibility = function() {

    var self = this;
    var rows = null;

    this.expertLb
        .find('.table-filtered-filters-controls.columns-view input').each(function(i, chk)
    {
        var indx      = self.expertLb.find('.table-filtered-headers th[data-columnname="' + $(chk).val() + '"]').index() + 1;
        var selectors = [
            '.table-filtered-headers thead tr th:nth-child(' + indx + ')',
            '.table-filtered-list thead tr th:nth-child(' + indx + ')',
            '.table-filtered-list tbody tr td:nth-child(' + indx + ')'
        ];

        rows = self.expertLb.find(selectors.join(', '));
        if (rows.length > 0) {
            if ($(chk).prop('checked')) {
                rows.removeClass('hide');
            } else {
                rows.addClass('hide');
            }
        }
    });

    // update table width
    this.updateWidth(this.expertLb.find('.expert-view'));
    this.updateHeight(this.expertLb.find('.expert-view'));
};

/**
 * Render select with new values or reload
 *
 * @param integer activeId
 * @return HTML
 */
FilterTableHelper.prototype.renderViewSelect = function(activeId) {

    var self = this;

    // add select to form
    var select = $('<div class="design-select default expert-views" />');
    select.append($('<div class="wrap" />'));
    select.append($('<ul class="design-select-list hide" />'));

    var inputExists = false;
    $(this.views.views).each(function(i, view) {

        if (view.type !== self.type) {
            return;
        }

        // create wrapper value
        if (!inputExists) {

            select.find('.wrap').append(
                $('<div class="value" />')
                    .append(
                        $('<input class="view-name" name="viewName" type="text" maxlength="127" />')
                            .val(view.name)
                    )
            );
            inputExists = true;
        }

        // create row
        var li = $('<li />')
            .html(view.name)
            .attr('data-id', view.id)
            .on('click', function() {

                // update value
                var parentSelect = $(this).parents('.design-select');
                parentSelect.find('.active').removeClass('active');
                parentSelect.find('.view-name').val($(this).text());

                // set view settings
                var id = parseInt($(this).attr('data-id'));

                $(this).addClass('active');

                // get view json
                $(self.views.views).each(function(k, viewData) {

                    // get json
                    if (viewData.id === id) {

                        // show/hide fields
                        var fields = [];
                        if (typeof viewData.json.fields !== 'undefined') {

                            fields = viewData.json.fields;
                        }

                        // default select all
                        self.expertLb.find('.table-filtered-filters-controls.columns-view input').each(function(i, inp) {

                            if (fields.length === 0 || $.inArray($(inp).attr('name'), fields) !== -1) {
                                $(inp).prop('checked', true);
                            } else {
                                $(inp).prop('checked', false);
                            }
                        });

                        self.updateColumnsVisibility();

                        // show/hide filters
                        var filters = [];
                        if (typeof viewData.json.filters !== 'undefined') {

                            filters = viewData.json.filters;
                        }

                        // default select none
                        self.expertLb.find('.table-filtered-filters-controls:first input').each(function(i, inp) {

                            $(inp).prop('checked', false);

                            if ($.inArray($(inp).attr('name'), filters) !== -1) {
                                $(inp).prop('checked', true);
                            }
                        });

                        self.updateFiltersVisibility();
                    }
                });
            });

        // create delete link
        if (view.isdeletable === 1) {

            li.append(
                $('<span class="remove" />')
                .on('click', function() {

                    if (self.request) {
                        self.request.abort();
                        self.request = null;
                    }

                    // get current active li
                    var activeId = parseInt($(this).parents('.design-select-list').first().find('.active').attr('data-id'));
                    var id       = parseInt($(this).parent().attr('data-id'));

                    var ajax = new ffbAjax();
                    self.request = ajax.call(
                        self.views.deleteviewurl,
                        function(result) {

                            var res = ajax.isJSON(result);
                            if (res.state === 'ok') {

                                // remove deleted view from list
                                var newViews = [];
                                $(self.views.views).each(function(k, v) {

                                    if (v.id && v.id !== id) {
                                        newViews.push(v);
                                    }
                                });

                                // save result
                                self.views.views = newViews;

                                // refresh select
                                if (activeId === id) {
                                    // select first
                                    self.renderViewSelect();
                                } else {
                                    // select active
                                    self.renderViewSelect(activeId);
                                }
                            } else {

                                var errorData = ajax.parseError(result);
                                ffbLightbox.showInfo({
                                    'title'     : ffbTranslator.translate('TTL_DELETE_VIEW'),
                                    'className' : 'error',
                                    'text'      : errorData.message
                                });

                            }
                        },
                        {
                            'accepts' : 'json',
                            'type'    : 'post',
                            'data'    : {
                                'id' :  id
                            },
                            'indicator' : self.expertLb.find('.expert-views-controls .expert-views-ajax')
                        }
                    );

                    // close list
                    $(document).trigger('click');

                    return false;
                })
            );
        }

        // create list entry
        select.find('.design-select-list').append(li);
    });

    // replace if exist
    var isExist = this.expertLb ? this.expertLb.find('.content .expert-views-controls .design-select') : false;
    if (isExist && isExist.length > 0) {
        isExist.replaceWith(select);

        // init design select
        new DesignSelect(select);
    }

    if (typeof activeId !== 'undefined') {
        // set active id
        select.find('.design-select-list li[data-id="' + activeId +'"]').trigger('click');
    } else {
        // select first
        select.find('.design-select-list li').first().trigger('click');
    }

    return select;
};

/**
 * Init views select if config exist
 *
 * @return {object}
 *      expert views controls
 */
FilterTableHelper.prototype.initViews = function() {

    var self = this;

    // get parent, get settings
    var parentCnt        = this.cnt.find('.table-default.table-filtered').first();
    var expertviewsData = parentCnt.attr('data-expertviews');
    this.views          = ajax.isJSON(expertviewsData);

    // create views container in existed or new
    var isUpdate = false;
    if (this.expertLb.find('.expert-views-controls').length) {
        isUpdate = true;
        var viewsCnt = this.expertLb.find('.expert-views-controls');
        var viewsWrp = viewsCnt.find('.views-wrapper');
        viewsWrp.empty();
    } else {
        var viewsCnt = $('<div class="expert-views-controls"><div class="views-wrapper"></div></div>');
        var viewsWrp = viewsCnt.find('.views-wrapper');
    }

    // get select
    var select = this.renderViewSelect();
    viewsWrp.append(select);

    // add "save" control
    var saveButton   = $('<button class="expert-views-save" />')
        .html(ffbTranslator.translate('BTN_SAVE_VIEW'))
        .on('click', function() {

            if (self.request) {
                self.request.abort();
                self.request = null;
            }

            // get input
            var input = viewsWrp.find('input');
            input.removeClass('invalid');

            // validate
            if (input.val().length === 0) {
                input.addClass('invalid');
                return false;
            }

            var currentData = {
                'filters' : self.getFilters(),
                'fields'  : self.getFields()
            };

            var id = self.expertLb.find('.expert-views-controls .design-select-list .active').first().attr('data-id');
            if (typeof id === 'undefined') {
                return false;
            }

            // save view as new
            var ajax = new ffbAjax();
            self.request = ajax.call(
                self.views.updateviewurl,
                function(result) {

                    var res = ajax.isJSON(result);
                    if (res.state === 'ok') {

                        // update view from result
                        var newViews = [];
                        $(self.views.views).each(function(k, v) {

                            if (v.id === res.id) {
                                v.name = res.name;
                                v.json = ajax.isJSON(res.json);
                            }
                            newViews.push(v);
                        });

                        // save result
                        self.views.views = newViews;

                        // render select
                        self.renderViewSelect(res.id);
                    } else {

                        var errorData = ajax.parseError(result);
                        ffbLightbox.showInfo({
                            'title'     : ffbTranslator.translate('TTL_SAVE_VIEW'),
                            'className' : 'error',
                            'text'      : errorData.message
                        });

                    }
                },
                {
                    'accepts' : 'json',
                    'type'    : 'post',
                    'data'    : {
                        'id'   : id,
                        'name' : input.val(),
                        'type' : self.type,
                        'json' : currentData
                    },
                    'indicator' : self.expertLb.find('.expert-views-controls .expert-views-ajax')
                }
            );

            return false;
        });

    // add "add new" control
    var createButton = $('<button class="expert-views-create" />')
        .html(ffbTranslator.translate('BTN_CREATE_VIEW'))
        .on('click', function() {

            if (self.request) {
                self.request.abort();
                self.request = null;
            }

            // get input
            var input = viewsWrp.find('input');
            input.removeClass('invalid');

            // validate
            if (input.val().length === 0) {
                input.addClass('invalid');
                return false;
            }

            var currentData = {
                'filters' : self.getFilters(),
                'fields'  : self.getFields()
            };

            // save view as new
            var ajax = new ffbAjax();
            self.request = ajax.call(
                self.views.addviewurl,
                function(result) {

                    var res = ajax.isJSON(result);
                    if (res.state === 'ok') {

                        self.views.views.push(
                            {
                                'id'          : res.id,
                                'name'        : res.name,
                                'isdeletable' : res.isdeletable,
                                'type'        : res.type,
                                'json'        : ajax.isJSON(res.json)
                            }
                        );

                        self.renderViewSelect(res.id);
                    } else {

                        var errorData = ajax.parseError(result);
                        ffbLightbox.showInfo({
                            'title'     : ffbTranslator.translate('TTL_SAVE_VIEW'),
                            'className' : 'error',
                            'text'      : errorData.message
                        });

                    }
                },
                {
                    'accepts' : 'json',
                    'type'    : 'post',
                    'data'    : {
                        'name' : input.val(),
                        'type' : self.type,
                        'json' : currentData
                    },
                    'indicator' : self.expertLb.find('.expert-views-controls .expert-views-ajax')
                }
            );

            return false;
        });
    var createButtonInfo = $('<i>').addClass('icon-tooltip')
            .attr('data-tooltip', ffbTranslator.translate('LBL_EXPERTVIEW_CREATE_BUTTON_INFO'));

    viewsWrp.append(saveButton);
    viewsWrp.append(createButton);
    viewsWrp.append(createButtonInfo);

    if (!isUpdate) {

        // DERTMS-836 add select for eventpart to expert view controls
        var selectEventpart = $('.eventpart-container .eventpart select');
        if (selectEventpart.length) {
            selectEventpart = selectEventpart.clone()
                .removeAttr('id');

            // get ids
            var ids = [];
            selectEventpart.children().each(function(i, ch) {
                ids.push(ch.value);
            });

            var values = ids.join(',');

            // create all option
            var allopt = $('<option />')
                .attr('value', values)
                .html(ffbTranslator.translate('OPT_ALL_EVENTPARTS'));
            allopt.insertBefore(selectEventpart.children().first());

            if (!isUpdate) {
                // set default all
                selectEventpart.val(values);
            }

            viewsCnt.append(selectEventpart);
        }
    }

    viewsWrp.append($('<div class="expert-views-ajax" />'));
    viewsWrp.append($('.smart-tabs .eventpart').clone());

    // add to form
    if (!isUpdate) {

        viewsCnt.insertBefore(this.expertLb.find('.content > .table-default').first());

        var fi = new FormInitializer(viewsCnt);
        var eventpartDropdown = fi.initDropdowns();

        if (typeof eventpartDropdown.eventpartId !== 'undefined') {

            eventpartDropdown['eventpartId'].opt.onSelect = function(element, index, optionValue) {

                // show/hide controls for all
                self.hideControlsByEventpart();
                var idslen = optionValue.split(',').length;

                if (idslen > 1) {
//                    self.updateFilters  = false;
                    self.editorsEnabled = false;
                } else if (idslen === 1) {
                    self.editorsEnabled = true;
//                    self.updateFilters = true;
                }

                self.updateFilters  = true;

                // refresh table;
                self.refresh();

                // update total divs
                self.updateTotalValues(self.expertLb);
            };
        }

        // add class for css
        viewsCnt.find('.ffbdropdown-main.default').addClass('evenpart-select');

        // show/hide controls for eventpart
        if (selectEventpart.length) {
            this.toggleControlsByEventpart(selectEventpart.val());
        }
    }

    // init design select
    new DesignSelect(select);

    // init tooltip
    new ffbTooltip(createButtonInfo);

    return viewsCnt;
};

/**
 * Init editors for fields
 *
 */
FilterTableHelper.prototype.initEditors = function() {

    // get parent, get target table
    var parentCnt = this.cnt.find('.table-default.table-filtered').first();
    var table     = this.expertLb.find('.content .table-filtered-dynamic .table-filtered-list .table-default').first();
    var updateUrl = parentCnt.attr('data-updatefield-url');

    // check if update is available
    if (typeof updateUrl === 'undefined') {
        return;
    }

    // check columns inputtype and set classes to tds
    table.find('thead th').each(function(i, th) {

        if (typeof $(th).attr('data-inputtype') === 'undefined') return;

        // set class
        table.find('tbody tr td:nth-child(' + (i + 1) + ')').addClass('editor editortype-' + $(th).attr('data-inputtype'));
    });
};

/**
 * Show editor by target
 *
 * @return {object} expert views controls
 */
FilterTableHelper.prototype.showEditor = function(target) {

    // check is target correct
    if (   target.get(0).tagName.toUpperCase() !== 'TD'
        || !target.hasClass('editor')
    ) {
        return;
    }

    // render text editor
    if (target.hasClass('editortype-1')) {

        // prepare control
        var inp = $('<input class="editor-text" name="inplaceeditor" type="text" maxlength="128" />');

        // set value
        inp.attr('data-defaultvalue', target.text());
        inp.val(target.text());

        // show editor
        target
            .addClass('editor-active')
            .empty()
            .append(inp);

        inp.focus();
    }

    // render textarea editor
    if (target.hasClass('editortype-2')) {

        // prepare control
        var inp = $('<textarea class="editor-textarea" name="inplaceeditor" maxlength="128" />');

        // load full text version
        inp.attr('data-defaultvalue', target.text());
        inp.val(target.text());

        // show editor
        target
            .addClass('editor-active')
            .empty()
            .append(inp);

        inp.focus();
    }

    // render select editor
    if (target.hasClass('editortype-3')) {

        // get column params
        var indx       = target.index();
        var table      = this.expertLb.find('.content .table-filtered-dynamic .table-filtered-list .table-default').first();
        var column     = table.find('thead th:nth-child(' + (indx + 1) + ')');
        var columnname = column.attr('data-columnname');

        // find select to clone
        var select = this.expertLb
            .find('.content .table-filtered-filters .selects select[name="' + columnname + '"]')
            .first();
        if (select.length > 0) {

            // prepare control
            var inp = select.clone().removeAttr('id');

            // get value by option
            var value = '';
            inp.find('option').each(function(i, opt) {

                // update to Please select if empty
                if ($(opt).val() === '') {
                    $(opt).html(ffbTranslator.translate('VAL_PLEASE_SELECT'));
                }

                // get value
                if ($(opt).text() === target.text()) {
                    value = $(opt).val();
                }
            });

            // load full text version
            inp.addClass('editor-select')
               .attr('data-defaultvalue', value)
               .val(value);

            // show editor
            target
                .addClass('editor-active')
                .empty()
                .append(inp);

            // init select
            var fi  = new FormInitializer(target);
            var dds = fi.initDropdowns();
        }
    }
};

/**
 * hide editor by target
 *
 * @return {object} expert views controls
 */
FilterTableHelper.prototype.hideEditor = function(target) {

    // check is target correct
    if (target.get(0).tagName.toUpperCase() !== 'TD' || !target.hasClass('editor')) {
        return;
    }

    // hide text editor
    if (target.hasClass('editortype-1')) {

        // get control
        var inp = target.find('input');

        // set value
        target.html(inp.val());

        // hide editor
        target.removeClass('editor-active');
    }

    // hide textarea editor
    if (target.hasClass('editortype-2')) {

        // get control
        var inp = target.find('textarea');

        // set value
        //target.html(inp.val().substr(0, 100));
        target.html(inp.val());

        // hide editor
        target.removeClass('editor-active');
    }

    // hide select editor
    if (target.hasClass('editortype-3')) {

        // get control
        var inp = target.find('select');

        // set value
        if (inp.val() === '') {
            target.html('');
        } else {
            target.html(inp.find(':selected').first().text());
        }

        // hide editor
        target.removeClass('editor-active');
    }
};

/**
 * document on click event
 *
 * @return {object} expert views controls
 */
FilterTableHelper.prototype.initDocumentClick = function(e) {

    var self = this;

    // saved last clicked target to open
    var nextTarget = null;

    $(document).on('click', function(e) {

        // check if exper view is oppened
        if (!self.expertLb) return;

        // get parent, get target table
        var parentCnt = self.cnt.find('.table-default.table-filtered').first();
        var table     = self.expertLb.find('.content .table-filtered-dynamic .table-filtered-list .table-default').first();
        var updateUrl = parentCnt.attr('data-updatefield-url');

        // get targets
        var target       = $(e.target);
        var activeTarget = table.find('.editor-active');

        // check target openned already or is a child of editor
        if (target.hasClass('editor-active') || target.parents('.editor-active').length > 0) {
            return;
        }

        // check was no ajax error and we close not a error lb
        if ($('.lightbox.error.modal.info').length > 0 || target.parents('.lightbox.error.modal.info').length > 0) {
            return;
        }

        // check if other editors oppened
        if (activeTarget.length > 0) {

            // get value
            var value = activeTarget.find('input, textarea, select').val();

            // check if value was changed, if not close without saving
            if (value == activeTarget.find('input, textarea, select').attr('data-defaultvalue')) {

                // hide active editor
                self.hideEditor(activeTarget);

                // show editor
                self.showEditor(target);
                return;
            }

            // set next target
            nextTarget = target;

            // try to save changed
            if (self.updateRequest) {
                self.updateRequest.abort();
                self.updateRequest = null;
            }

            // get column params
            var indx   = activeTarget.index();
            var column = table.find('thead th:nth-child(' + (indx + 1) + ')');

            // get data to save
            var data = {
                'userId'     : activeTarget.parent().find('input[name="userid[]"]').val(),
                'columnname' : column.attr('data-columnname'),
                'value'      : value
            };

            // show saving
            activeTarget.addClass('saving');

            // save view as new
            var ajax = new ffbAjax();
            self.updateRequest = ajax.call(
                updateUrl,
                function(result) {

                    // remove saving
                    activeTarget.removeClass('saving');

                    var res = ajax.isJSON(result);
                    if (res.state === 'ok') {

                        // hide active editor
                        self.hideEditor(activeTarget);

                        // open next if was clicked
                        if (nextTarget) {
                            self.showEditor(nextTarget);
                            nextTarget = null;
                        }
                    } else {

                        var errorData = ajax.parseError(result);
                        ffbLightbox.showInfo({
                            'title'     : ffbTranslator.translate('TTL_SAVE_FIELD'),
                            'className' : 'error',
                            'text'      : errorData.message
                        });
                    }
                },
                {
                    'accepts' : 'json',
                    'type'    : 'post',
                    'data'    : data
                }
            );

        } else {

            // show editor
            self.showEditor(target);
        }
    });
};

/**
 * hide controls by selected eventpart
 *
 */
FilterTableHelper.prototype.hideControlsByEventpart = function() {

    // hide controls for all
    this.expertLb.find('.design-select, button, i').css('visibility', 'hidden');
    this.expertLb.find('.table-filtered-filters').css('visibility', 'hidden');
}

/**
 * show or hide controls by selected eventpart
 *
 * @return {string} value
 */
FilterTableHelper.prototype.toggleControlsByEventpart = function(value) {

    var ids = value.split(',');

    if (ids.length > 1) {
        // hide controls for all
        this.hideControlsByEventpart();
        this.editorsEnabled = false;
    } else {
        // show elements
        this.expertLb.find('.design-select, button, i').css('visibility', 'visible');
        this.expertLb.find('.table-filtered-filters').css('visibility', 'visible');
        this.editorsEnabled = true;
    }

    return ids.length;
}

/**
 * Create expert filters and view columns
 *
 * @return {object} container
 */
FilterTableHelper.prototype.createExpertFilters = function(container) {

    // get parent settings
    var self      = this;
    var parentCnt = self.cnt.find('.table-default.table-filtered').first();

    // create filters & append to container
    if (container.find('.table-filtered-filters').length) {
        var filters = container.find('.table-filtered-filters');
        filters.empty();
    } else {
        var filters = $('<div />').addClass('table-filtered-filters');
        container.append(filters);
    }

    // create container for selects
    var filtersSelects = $('<div />').addClass('selects');
    filters.append(filtersSelects);

    // create filters view element
    var filtersChecks = $('<div />')
        .on('click', function(e) {

            if (e.target === this) {
                $(this).toggleClass('open');
            }
        })
        .addClass('table-filtered-filters-controls')
        .html(ffbTranslator.translate('BTN_FILTERS'))
        .append('<div class="checkboxes" />');
    filtersSelects.append(filtersChecks);

    // create filters selects
    var filtersData = ajax.isJSON(parentCnt.attr('data-expertfilters'));
    $(filtersData).each(function(i, opt) {

        // create filter
        var name    = opt.value;
        var options = opt.options;
        var title   = opt.title;

        // add options to filter
        if (typeof options !== 'undefined') {

            // create select
            var newDropdown = self.createSelect(name, options, title);

            // add in dom
            // BUG sends an ajax request which is immediately aborted
            filtersSelects.append(newDropdown);

            // create checkbox for select
            var newCheckbox = self.createCheckbox(title, name);
            newCheckbox.find('input').on('change', function() {

                self.updateFiltersVisibility();
            });
            filtersChecks.find('.checkboxes').append(newCheckbox);
        }
    });

    // create columns view element
    var columnsView = $('<div />')
        .on('click', function(e) {

            if (e.target === this) {
                $(this).toggleClass('open');
            }
        })
        .addClass('table-filtered-filters-controls columns-view')
        .html(ffbTranslator.translate('BTN_COLUMNS_VIEW'))
        .append('<div class="checkboxes" />');
    filters.append(columnsView);

    // show all columns by default
    // get viewed columns from normal view
    var showColumns = [];
    this.expertLb.find('.table-filtered-headers thead tr th').each(function(i, th) {

        var ColName = $(th).attr('data-columnname');
        if (typeof ColName !== 'undefined') {
            showColumns.push(ColName);
        }
    });

    // create columns checkboxes
    var columnsData = ajax.isJSON(parentCnt.attr('data-expertcolumns'));
    if (typeof columnsData !== 'undefined') {

        for (var key in columnsData) {
            if (!columnsData.hasOwnProperty(key)) {
                continue;
            }

            // create checkbox for select
            var newCheckbox = self.createCheckbox(columnsData[key], key);

            newCheckbox.find('input')
                .prop('checked', true)
                .on('change', function() {

                    self.updateColumnsVisibility();
                });
            columnsView.find('.checkboxes').append(newCheckbox);
        }
    }
}

/**
 * init expert filters and view columns
 *
 * @return {object} container
 */
FilterTableHelper.prototype.initExpertFilters = function(container) {

    var fi      = new FormInitializer(container);
    var self    = this;
    var filters = container.find('.table-filtered-filters');

    // init filter dropdowns
    var dropdowns = fi.initDropdowns(filters.find('select'));
    for (var dKey in dropdowns) {
        if (!dropdowns.hasOwnProperty(dKey)) {
            continue;
        }

        dropdowns[dKey].opt.valuePrefix = $(dropdowns[dKey].select).attr('title') + ': ';
        dropdowns[dKey].setValue('');
        dropdowns[dKey].opt.onSelect    = function(select, valueIndex, value) {

            self.refresh();
        };
    }

    // hide all filters
    filters.find('.ffbdropdown-main').addClass('hide');
}