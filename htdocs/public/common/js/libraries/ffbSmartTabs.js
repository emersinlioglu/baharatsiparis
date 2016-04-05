"use strict";

/**
 * Js for smart tabs with tab groups
 *
 * @class
 * @constructor
 * @this ffbSmartTabs
 * @param {object} container
 * @return ffbSmartTabs
 */
var ffbSmartTabs = function(container, onSelect, options) {

    var _         = this;
    var _cnt      = null;
    var _onSelect = null;
    _.options       = {
        'showTmsChoice'   : true
    };

    /**
     * Init tabs js
     *
     * @private
     * @this ffbSmartTabs
     */
    var _initTabs = function() {

        // init tab group
        _cnt.find('.smart-tabs > .tab-group').each(function(i, tg) {

            // check group with elements
            if ($(tg).find('.tab:not(.hide)').length === 0) {
                $(tg).addClass('empty');
            }

            // init handler
            $(tg).find('.toggle:not(.disabled)')
                .unbind()
                .on('click', function(e) {

                    var group = $(this).parents('.tab-group');
                    if (group.hasClass('open')) {

                        // close list
                        group.removeClass('open');
                    } else {

                        // close previously
                        _cnt.find('.smart-tabs > .tab-group.open .toggle').trigger('click');

                        // open list
                        group.addClass('open');
                    }

                    return false;
                });
        });

        // init value click
        _cnt.find('.smart-tabs > .tab-group .selected-value').each(function(i, sv) {

            // init handler
            $(sv)
                .unbind()
                .on('click', function(e) {

                    // find active tab if exist
                    var group = $(this).parents('.tab-group');
                    if (group.find('.tab.active').length > 0) {

                        group.find('.tab.active').trigger('click');
                    } else {

                        group.find('.tab:not(.disabled)').eq(0).trigger('click');
                    }
                    return false;
                });
        });

        // init tabs click
        _cnt.find('.smart-tabs .tab:not(.disabled)').each(function(i, tab) {

            if ($(tab).attr('data-content') === undefined) return;

            //check content for tab
            var content = _cnt.find('.tab-content[data-content="' + $(tab).attr('data-content') + '"]');

            if (content.length === 0) {

                $(tab)
                .unbind()
                .on('click', function(e) {
                    // callback
                    if (_onSelect) {
                        var func = ajax.getFunctionsParts(_onSelect);
                        if (func) func($(tab));
                    }
                });

                return;
            }

            //show if tab active
            if ($(tab).hasClass('active')) {
                content.removeClass('hidden');
            }

            //init event
            $(tab)
                .unbind()
                .on('click', function(e) {

                    // callback only
                    if ($(tab).attr('data-content') == 'tms-choice' && _.options.showTmsChoice == false) {

                        if (_onSelect) {
                            var func = ajax.getFunctionsParts(_onSelect);
                            if (func) func($(tab), _.options);
                        }
                        return false;
                    }

                    // clear previously selected groups
                    _cnt.find('.smart-tabs > .tab-group.active').removeClass('active');

                    // set group active, if exist
                    var tg = $(this).parents('.tab-group');
                    if (tg.length > 0) {
                        tg.addClass('active');

                        // clear previously active tabs in group
                        tg.find('.tab.active').removeClass('active');
                    }

                    // clear active tabs in first layer if exists
                    _cnt.find('.smart-tabs > .tab.active').removeClass('active');

                    // set tab active
                    $(this).addClass('active');

                    // hide all other contents
                    _cnt.find('.tab-content').addClass('hidden');

                    // find content and show
                    var con = _cnt.find('.tab-content[data-content="' + $(this).attr('data-content') + '"]');
                    con.removeClass('hidden');

                    // set tabvalue in groupvalue
                    if (tg.length > 0) {

                        tg.find('.selected-value').html($(this).find('> .title').html());
                    }

                    // close opened groups if exists
                    _cnt.find('.smart-tabs > .tab-group.open .toggle').trigger('click');

                    // callback
                    if (_onSelect) {
                        var func = ajax.getFunctionsParts(_onSelect);
                        if (func) func($(tab), _.options);
                    }

                    return false;
                });

        });
    }

    /**
     * Init element
     *
     * @pulic
     * @this ffbSmartTabs
     * @param {object} container
     */
    _.init = function(container, onSelect, options) {

        //check container
        if (typeof container !== 'object') {
            container = $('#' + container);
        }
        _cnt = $(container);
        if (_cnt.length === 0) return;

        if (onSelect) _onSelect = onSelect;

        //Init options
        if (options && typeof(options) === 'object') {
            for (var key in options) {
                if (!options.hasOwnProperty(key)) continue;
                _.options[key] = options[key];
            }
        }

        _initTabs();
    }

    _.init(container, onSelect, options);
}
