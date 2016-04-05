"use strict";

/**
 * Add vertical scroll to left sidebar
 *
 * @param {HTMLElement} container
 * @this ffbVerticalScroll
 */
var ffbVerticalScroll = function(container) {

    var _             = this;
    var _isAnim       = false;
    var _cnt          = null; // container div
    var _isOverDown   = false;
    var _isOverUp     = false;
    var _wheelStep    = 5;
    var _wheelCounter = null;

    /**
     *  init scroll container height
     * @private
     */
    var _initContainer = function() {

        if (!_cnt.hasClass('inited')) {

            $(window).resize(function() {

                _.updateScrollHeight();
            });
        }

        _.updateScrollHeight();
    }


    /**
     * init events
     * @private
     */
    var _initEvents = function() {

        // init wheel
        _cnt.find('.scroll').on('DOMMouseScroll mousewheel', function(e) {

            var delta = typeof e.originalEvent.wheelDelta !== 'undefined' ? -1 * e.originalEvent.wheelDelta : e.originalEvent.detail;            
            
            if (delta > 0) {

                // scroll down
                _isOverDown   = true;
                _isOverUp     = false;
            } else {

                // scroll up
                _isOverUp     = true;
                _isOverDown   = false;
            }

            // set steps
            _wheelCounter = _wheelStep;
        });

        // init controls
        _cnt.find('.scroll-up')
            .mouseup(function() {

                _isOverUp = false;
            })
            .mousedown(function() {

                _isOverUp     = true;
                _isOverDown   = false;
                _wheelCounter = 0;
            });
        _cnt.find('.scroll-down')
            .mouseup(function() {

                _isOverDown = false;
            })
            .mousedown(function() {
                _isOverDown   = true;
                _isOverUp     = false;
                _wheelCounter = 0;
            });

        // start interval
        setInterval(function() {

            if (!_isAnim && (_isOverUp || _isOverDown)) {
                _.anim();
            }
        }, 100);
    }

    /**
     * Update scroll height, show/hide controls
     *
     */
    _.updateScrollHeight = function() {

        var elements = _cnt.find('> span');
        var elsHeight = 0;
        elements.each(function(i, el) {

            elsHeight += $(el).outerHeight(true);
        });

        var parent  = _cnt.parent();
        var parentH = _cnt.closest('.panemanager-pane').height();

        if (parent.length > 0) {

            // update height
            _cnt.find('.scroll').height(parentH - parent.position().top - parent.find('> .controls').outerHeight() - elsHeight);
        }
    }

    /**
     * animate
     */
    _.anim = function() {

        // start animation
        _isAnim = true;

        // init variables
        var duration = 200;
        var list     = _cnt.find('.scroll > ul');
        var param    = parseInt(list.css('top'));

        // fix for safari and chrome
        if (isNaN(param)) {
            param = 0;
        }

        // calculate
        var max = parseInt(_cnt.find('.scroll').height());
        if (_isOverDown) {

            if (-1 * param < list.height() - max) {
                param = param - 40 + 'px';
            }
        }
        if (_isOverUp) {

            if (param < 0) {
                param = param + 40 + 'px';
            }
        }        

        // animate
        $(list).animate(
            {'top' : param},
            {
                'duration' : duration,
                'easing'   : 'linear',
                'complete' : function() {

                    _isAnim = false;

                    _wheelCounter--;

                    // _wheelCounter
                    if (_wheelCounter === 0) {
                        _isOverUp   = false;
                        _isOverDown = false;
                    }

                    if (_isOverUp || _isOverDown || _wheelCounter > 0) {
                        _.anim();
                    }
                }
            }
        );
    }

    /**
     *
     * @param container
     * @returns {ffbVerticalScroll}
     */
    _.init = function(container) {

        _cnt = $(container);
        if (_cnt.length === 0 || _cnt.hasClass('inited')) {
            return;
        }

        _initEvents();
        _initContainer();

        _cnt.addClass('inited');
    }

    _.init(container);
}
