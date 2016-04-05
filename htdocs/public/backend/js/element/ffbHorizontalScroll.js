"use strict";

/**
 * Horizontal slider for continget roomstocks
 *
 * @param {HTMLELement container
 * @returns {this}
 */
var ffbHorizontalScroll = function(container) {

    var _            = this;
    var _isAnim      = false;
    var _cnt         = null; // container div
    var _isOverLeft  = false;
    var _isOverRight = false;

    /**
     *  init events
     * @private
     */
    var _initEvents = function() {

        var parent = _cnt.parent();

        parent.find('.scroll.left')
            .on('click', function(e) {
                return false;
            }).mouseup(function(e){
                _isOverRight = false;
                return false;
            }).mousedown(function(e){
                _isOverLeft  = false;
                _isOverRight = true;
                return false;
            });

        parent.find('.scroll.right')
            .on('click', function(e) {
                return false;
            })
            .mouseup(function(e){
                _isOverLeft = false;
                return false;
            })
            .mousedown(function(e){
                _isOverLeft  = true;
                _isOverRight = false;
                return false;
            });

        setInterval(function() {
            if (_isOverRight && !_isAnim || _isOverLeft && !_isAnim) {
                _.anim();
            }
        }, 100);
    }

    /**
     * animate
     */
    _.anim = function() {

        _isAnim = true;
        var duration = 100;

        // elem data
        var firstElem  = _cnt.find('.column').first();
        var countElems = _cnt.find('.column').length;
        var elemWidth  = firstElem.outerWidth(true);
        var list       = _cnt.find('.roomstocks');
        var param      = parseInt(list.css('left'));

        // fix for safari and chrome
        if (isNaN(param)) {
            param = 0;
        }

        var max = elemWidth * countElems;
        if (_isOverRight) {
            if (param < -10){
                param = param + elemWidth + 'px';
            }
        }

        if (_isOverLeft) {
            var calcParam = param;
            if (param < 0) {
                calcParam = (-1) * param;
            }
            if (calcParam < max - (3 * elemWidth)) {
                param = param - elemWidth + 'px';
            }
        }

        $(list).animate(
            {
                'left' : param
            },
            {
                'duration' : duration,
                'easing'   : 'linear',
                'complete' : function() {

                    _isAnim = false;
                    if (_isOverRight || _isOverLeft) {
                        _.anim();
                    }
                }
            }
        );
    }

    /**
     * Update list width
     *
     */
    _.updateWidth = function() {

        // init list width
        var width = 330;
        if (_cnt.find('.roomstocks .column').length > 3) {
            width = _cnt.find('.roomstocks .column').length * 110;
        }
        _cnt.find('.roomstocks').css('width', width);
    }

    /**
     *
     * @param container
     * @returns {ffbHorizontalScroll}
     */
    _.init = function(container) {

        _cnt = $(container);

        if (_cnt.length === 0 || _cnt.hasClass('inited')) {
            return;
        }

        _initEvents();
        _.updateWidth();

        _cnt.addClass('inited');
    }

    _.init(container);
}
