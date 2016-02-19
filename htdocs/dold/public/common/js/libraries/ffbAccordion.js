"use strict";

/**
 * Js for accordion
 *
 * @class
 * @constructor
 * @this ffbAccordion
 * @param {object} container
 * @param {boolean} thereCanBeOnlyOne Autohide previous opened
 */

/*

Example markup:
---------------
<div class="ffb-accordion">
    <div class="accordion-title open">
        Title
    </div>
    <div class="accordion-content open">
        Content
    </div>
    <div class="accordion-title">
        Title 2
    </div>
    <div class="accordion-content">
        Content 2
    </div>
</div>

*/

var ffbAccordion = function(container, thereCanBeOnlyOne, name) {

    var _                  = this;
    var _cnt               = null;
    var _thereCanBeOnlyOne = true;
    var _name              = 'accordion';

    /**
     * Gets the height of an element
     * @private
     * @param {Object} elem
     * @return {Number}
     */
    var _getHeight = function(elem) {
        var height;

        // Move element out of viewport to calculate its height!
        var oldHeight = elem.height();
        elem.css({
            'height'   : 'auto',
            'opacity'  : 0,
            'position' : 'relative'
        });

        height = elem.height();

        // Remove previous set styles
        elem.css({
            'height'   : oldHeight,
            'left'     : 0,
            'opacity'  : 1,
            'position' : 'relative'
        });

        return height;
    };

    /**
     * Init title click events
     *
     * @private
     * @this
     */
    var _initTitles = function() {

        _cnt.find('.'+_name+'-title').each(function(i, title) {

            $(title).on('click', function(e) {

                var activeTitles = {};
                if (_thereCanBeOnlyOne) {

                    // close previously opened accordions
                    activeTitles = _cnt.find('.'+_name+'-title.open');
                }

                if ($(this).hasClass('open')) {

                    // check this accordion as active if mehr opened available
                    if (!_thereCanBeOnlyOne) {
                        activeTitles = $(this);
                    }
                } else {

                    // open accordion
                    $(this).addClass('open');

                    var contentDiv = $(this).nextAll('.'+_name+'-content');
                    contentDiv = contentDiv.first();

                    contentDiv.stop()
                        .css('height', contentDiv.height())
                        .addClass('open');

                    var height = _getHeight(contentDiv);
                    contentDiv.animate(
                        {'height' : height + 'px'},
                        {
                            'duration' : 200,
                            'complete' : function() {
                                $(this).css('height', 'auto');
                                $(window).trigger('resize');
                            }
                        }
                    );
                }

                //close accordions
                $(activeTitles).each(function(k, ttl) {

                    $(ttl).removeClass('open');

                    var contentDiv = $(ttl).nextAll('.'+_name+'-content');
                    contentDiv = contentDiv.first();

                    contentDiv.stop()
                        .css('height', contentDiv.height())
                        .removeClass('open')
                        .animate(
                            {'height' : 0},
                            {
                                'duration' : 200,
                                'complete' : function() {
                                    $(window).trigger('resize');
                                }
                            }
                        );
                });

                return false;
            });
        });
    };

    /**
     * Init accordion
     *
     * @public
     * @this ffbAccordion
     * @param {object} container
     * @param {boolean} thereCanBeOnlyOne Autohide previous opened
     * @param {string} name of accordion to be used
     *      defaults to accordion
     */
    _.init = function(container, thereCanBeOnlyOne, name) {

        // check container
        if (typeof container !== 'object') {
            container = $('#' + container);
        }
        _cnt = $(container);
        if (_cnt.length === 0) return;

        if (_cnt.hasClass('active')) return;

        // check callback
        if (thereCanBeOnlyOne !== undefined) {
            _thereCanBeOnlyOne = thereCanBeOnlyOne;
        }

        //
        if (name !== undefined) {
            _name = name;
        }

        _initTitles();

        _cnt.addClass('active');
    }

    _.init(container, thereCanBeOnlyOne, name);
}
