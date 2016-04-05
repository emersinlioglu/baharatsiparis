"use strict";

/**
 * Js for tabs
 *
 *  <div class="tabs">
 *      <div class="tab active" data-content="accessories">
 *          <span></span>
 *      </div>
 *      <div class="tab active" data-content="categories">
 *          <span></span>
 *      </div>
 *  </div>
 *  <div class="tab-content" data-content="accessories">
 *  </div>
 *  <div class="tab-content" data-content="categories">
 *  </div>
 *
 * @class
 * @constructor
 * @this ffbTabs
 * @param {object} container
 * @return ffbTabs
 */
var ffbTabs = function(container, onSelect) {

    var _         = this;
    var _cnt      = null;
    var _onSelect = null;

    /**
     * Init tabs js
     *
     * @private
     * @this ffbTabs
     */
    var _initTabs = function() {

        //get tabs
        _cnt.find('.tabs .tab').each(function(i, tab) {

            //check data-content
            if ($(tab).attr('data-content') !== undefined) {

                //check content for tab
                var content = _cnt.find('.tab-content[data-content="' + $(tab).attr('data-content') + '"]');
                if (content.length > 0) {

                    //show if tab active
                    if ($(tab).hasClass('active')) {
                        content.removeClass('hidden');
                    } else {
                        content.addClass('hidden');
                    }

                    //init event
                    $(tab)
                        .unbind()
                        .on('click', function(e) {

                            //set tab active
                            $(this).parent('.tabs').find('.active').removeClass('active');
                            $(this).addClass('active');

                            //hide all other contents
                            _cnt.find('.tab-content').addClass('hidden');

                            //find content and show
                            var con = _cnt.find('.tab-content[data-content="' + $(this).attr('data-content') + '"]');
                            con.removeClass('hidden');

                            if (_onSelect) {
                                var func = ajax.getFunctionsParts(_onSelect);
                                if (func) func($(this));
                            }

                            return false;
                        });
                }
            }
        });
    }

    /**
     * Init element
     *
     * @pulic
     * @this ffbTabs
     * @param {object} container
     */
    _.init = function(container, onSelect) {

        //check container
        if (typeof container !== 'object') {
            container = $('#' + container);
        }
        _cnt = $(container);
        if (_cnt.length === 0) return;

        if (onSelect) _onSelect = onSelect;

        _initTabs();
    }

    _.init(container, onSelect);
}
