"use strict";

/**
 * Lightbox factory
 *
 * @class
 * @constructor
 * @this ffbLightbox
 * @return ffbLightbox
 */
var ffbLightbox = new function() {

    var _               = this;
    var _instances      = [];
    var _timers         = [];
    var _translate      = {
        'btnOk'     : ffbTranslator.translate('BTN_OK'),
        'btnCancel' : ffbTranslator.translate('BTN_CANCEL'),
        'btnClose'  : ffbTranslator.translate('BTN_CLOSE')
    }

    _.adjustInScreen = false;

    /**
     * Init global listeners for lightboxes
     *
     * @private
     * @this ffbLightbox
     */
    var _init = function() {

        //Init close by esc
        $(document).on('keyup', function(e) {

            var lb = $('.lightbox').last();

            if (e.keyCode === 27 && lb.length > 0) {

                if (!lb.hasClass('progress')) {
                    lb.find('.menu .close').trigger('click');
                }
                return false;
            }

            if (e.keyCode === 13) {

                if (lb.length > 0 && lb.hasClass('info')) {

                    lb.find('.buttons .ok').first().trigger('click');
                    return false;
                }

                if (lb.length > 0 && lb.hasClass('modal')) {

                    if (lb.find('.buttons .ok').length > 0) {
                        lb.find('.buttons .ok').first().trigger('click');
                        return false;
                    }
                }
            }
        });

        //Init scroll to follow
        $(window).on('scroll', function() {

            var lbs = $('.lightbox');

            if (lbs.length > 0) {

                lbs.each(function() {

                    if ($(this).hasClass('follow-scroll')) {

                        _.updatePosition($(this).attr('id'));
                    }
                });
            }
        });

        //Init update margin left by resize
        $(window).on('resize', function() {

            var lbs = $('.lightbox');
            if (lbs.length > 0) {

                lbs.each(function() {

                    _.updatePosition($(this).attr('id'));
                });
            }
        });
    }

    /**
     * Parse function name or call function
     *
     * @public
     * @this {ffbAjax}
     * @param {function|string}
     * @return {(function|false)}
     */
    _.getFunctionParts = function(functionName) {

        //Check, if function return
        if ($.isFunction(functionName) === true) return functionName;

        //If function name parse
        var func = null;

        if (functionName) {
            var parts = functionName.split('.');
            func      = window[parts[0]];
            var i     = 1;
            while(i < parts.length) {
                func = func[parts[i]];
                i++;
            }
        }

        //If function exist in window, return
        if ($.isFunction(func)) return func;
        else return false;
    }

    /**
     * Create lightbox and fade html, return object with elements
     *
     * @private
     * @this {ffbLightbox}
     * @param {object} options
     * @return {object} Id, Lightbox and fade divs
     */
    var _createLightbox = function(options) {

        //Create id
        var id         = 'lb' + new Date().getTime();
        var startIndex = 201;
        if (options.type === 'modal') {
            startIndex = 70001;
        }
        var zIndex = startIndex + $('.lightbox').length;

        //Create fade
        var fade = $('<div class="lightbox-fade"></div>')
                     .attr('id', id + 'fade')
                     .css('zIndex', zIndex);

        //Create lb
        var lbWrapper = $('<div class="wrapper"></div>')
            .append('<div class="menu"><div class="title"></div><div class="close button white">' + ffbTranslator.translate('BTN_CLOSE') + '</div></div>')
            .append('<div class="content">' + _.getLoadingAnimation() + '</div>');

        var lb = $('<div class="invisible lightbox"></div>')
            .attr('id', id)
            .css('zIndex', zIndex)
            .append(lbWrapper);

        return {'id' : id, 'lb' : lb, 'fade' : fade};
    }

    /**
     * Set event listeners
     *
     * @private
     * @this ffbLightbox
     * @param {object} lb
     * @param {object} options
     */
    var _setEventListeners = function(lb, options) {

        lb.find('.menu .close').on('click', function() {
            _.close();
        });
    }

    /**
     * Set options to lb
     *
     * @private
     * @this ffbLightbox
     * @param {object} lb
     * @param {object} options
     * @return {object} lb
     */
    var _setOptions = function(lb, options) {

        //Lightbox className
        var className = [];

        //Set options
        if (options) {

            //Set followScroll
            if (options.followScroll) className.push('follow-scroll');

            //Set lb className
            if (options.className) className.push(options.className);

            //Set lb title
            if (options.title) lb.find('.menu .title').html(options.title);

        }

      //Set className
      lb.addClass(className.join(' '));

      return lb;
    }

    /**
     * Show lightbox, add to instances
     *
     * @private
     * @this ffbLightbox
     * @param {string} id
     * @param {object} lb
     * @param {object} fade
     * @param {object} lb options
     */
    var _show = function(id, lb, fade, options) {

        _instances.push({
            'id'      : id,
            'type'    : options.type, //['base', 'modal', 'ajax', 'info', 'progress']
            'options' : options
        });

        fade.addClass('layer-' + _instances.length);
        lb.addClass(options.type);

        $('body')
            .append(fade)
            .append(lb);
    }

    /**
     * Close top lightbox
     *
     * @public
     * @this ffbLigthbox
     */
    _.close = function() {

        var data = _instances.pop();
        if (!data) return;

        //remove all timers
        while (_timers.length > 0) {
            var tId = _timers.pop();
            clearTimeout(tId);
        }

        if (data.options && data.options.onClose) {
            var func = _.getFunctionParts(data.options.onClose);
            if (func) func(data.id, data.id + 'fade');
        } else {
            _.remove(data.id);
        }
    }

    /**
     * Close lightboxe with delay
     *
     * @public
     * @this ffbLigthbox
     */
    _.closeAfter = function(delay) {

        delay = typeof delay !== 'undefined' ? delay : 2000;

        _timers.push(setTimeout(function() {
            _.close();
        }, delay));
    }

    /**
     * Close all opened lightboxes
     *
     * @public
     * @this ffbLigthbox
     */
    _.closeAll = function() {

        //remove all timers
        while (_timers.length > 0) {
            var tId = _timers.pop();
            clearTimeout(tId);
        }

        while (_instances.length > 0) {
            _.close();
        }
    }

    /**
     * Close all lightboxes with delay
     *
     * @public
     * @this ffbLigthbox
     */
    _.closeAllAfter = function(delay) {

        delay = typeof delay !== 'undefined' ? delay : 2000;

        _timers.push(setTimeout(function() {
            _.close();
        }, delay));
    }


    /**
     * Closes the last n(count) lightboxes
     * @param {int} count
     * @param {int} delay in miliseconds
     * @returns {void}
     */
    _.closeLast = function(count, delay) {

        count = typeof count !== 'undefined' ? count : 0;
        delay = typeof delay !== 'undefined' ? delay : 0;

        var lightboxes = $('.lightbox');

        //remove close buttons
        for (var i = lightboxes.length; i > 0; i--) {

            lightboxes.eq(i).find('.button.close, .button.ok').remove();
        }

        //close lightboxes
        setTimeout(function() {

            var cnt = 1;
            for (var i = lightboxes.length; i > 0; i--) {

                if (count >= cnt) {
                    ffbLightbox.close();
                    cnt++;
                } else {
                    break;
                }
            }

        }, delay);
    }

    /**
     * Create wait ajax animation
     *
     * @public
     * @this ffbLigthbox
     * @return {string} Loader Html
     */
    _.getLoadingAnimation = function() {

        return '<div class="lightbox-loading"></div>';
    }

    /**
     * Remove lightbox elements
     *
     * @public
     * @this ffbLigthbox
     * @param {string} lbId
     */
    _.remove = function(lbId) {

        if (!lbId) return;

        //remove from instances
        var newInstancec = [];
        for (var i = 0; i < _instances.length; i++) {
            if (_instances[i].id !== lbId) {
                newInstancec.push(_instances[i]);
            }
        }
        if (_instances.length !== newInstancec.length) {
            _instances = newInstancec;
        }

        $('#' + lbId).remove();
        $('#' + lbId + 'fade').remove();

        //add page classname to body
        if ($('.lightbox.page').length === 0) {
            $('body').removeClass('lb-page-opened');
        }
    }

    /**
     * Create content lightbox and insert content from ajax request returns the DOM element
     *
     * @param {string} url Url for Request
     * @param {object} options Element attributes object
     *                         [className, title, method, data, callBack, onClose, beforeAjax]
     *
     * @return {object}
     */
    _.showAjax = function(url, options) {

        if (!url) return null;
        if (!options) options = {};
        if (!options.type) options.type = 'ajax';

        //Show base
        var lb = _.showBase(options);

        //Ajax options
        var requestOptions = {
            'accepts' : 'partial',
            'error'   : function(xhr, statusText) {

                ffbLightbox.close();
                ffbLightbox.showInfo({
                    'title'     : ffbTranslator.translate('TTL_AJAX_ERROR'),
                    'className' : 'error',
                    'text'      : ffbTranslator.translate('MSG_AJAX_ERROR')
                });
            }
        };

        //Set options
        if (options) {

            //Set request options
            if (options.method) requestOptions['type'] = options.method;
            if (options.data) requestOptions['data'] = options.data;
        }

        lb.removeClass('invisible');

        //Callback
        if (options && options.beforeAjax) {
            var func = _.getFunctionParts(options.beforeAjax);
            if (func) func(lb);
        }

        //Update position to center
        _.updatePosition(lb.attr('id'));

        //Add loading class
        lb.addClass('loading');

        var getHTMLAjax = new ffbAjax();
        return getHTMLAjax.call(
            url,
            function(data) {

                lb.find('.content').html(data);

                //Add loading class
                lb.removeClass('loading');

                //Update position
                _.updatePosition(lb.attr('id'));

                //Callback
                if (options && options.callBack) {
                    var func = _.getFunctionParts(options.callBack);
                    if (func) func(lb);
                }
            },
            requestOptions
        );
    }

    /**
     * Create content lightbox returns the DOM elemnt
     *
     * @public
     * @this {ffbLightbox}
     * @param {object} options
     *                 Element attributes object [className, title, callBack, onClose, followScroll, type}
     * @return object Lightbox div
     */
    _.showBase = function(options) {

        if (!options) options = {};
        if (options && !options.type) options.type = 'base';

        //Create lb and fade
        var els = _createLightbox(options);

        //Set options
        els.lb = _setOptions(els.lb, options);

        //Set fade options
        if (options.className) {
            els.fade.addClass(options.className);
        }

        //add page classname to body
        if (els.lb.hasClass('page')) {
            $('body').addClass('lb-page-opened');
        }

        //Init listeners
        _setEventListeners(els.lb, options);

        //Show
        _show(els.id, els.lb, els.fade, options);

        //Show lightbox
        if (options.type === 'base') {

            //Show base html
            els.lb.removeClass('invisible');

            //Callback
            if (options && options.callBack) {
                var func = _.getFunctionParts(options.callBack);
                if (func) func(els.lb);
            }

            //Update position to center
            _.updatePosition(els.id);
        }

        return els.lb;
    }

    /**
     * Create modal lightbox and returns the DOM elemnt
     *
     * @param {object} options Element attributes object
     *                       {'className',
     *                        'title',
     *                        'text',
     *                        'okAction' : {'caption', 'className', 'callBack'},
     *                        'cancelAction' : {'caption', 'className', 'callBack']}
     *
     * @return object
     */
    _.showModal = function(options) {

        if (!options) {
            options = {};
        }

        if (!options.type) {
            options.type = 'modal';
        }

        // Check previously modal lightbox
        var data = _instances.slice(-1);
        if (data.length > 0 && data[0].type === 'modal') {
            _.close();
        }

        // Set options        
        if (   options.cancelAction
            && options.cancelAction.callBack
        ) {
            options.onClose = options.cancelAction.callBack;
        }

        // Show base
        var lb = _.showBase(options);

        // create ok button
        var okButton = $('<div>').attr('class', 'button ok').html(ffbTranslator.translate('BTN_OK'));

        // create cancel button
        var cancelButton = '';
        if (options.hideCancelButton || true !== options.hideCancelButton) {
            var cancelButton = $('<div>').attr('class', 'button cancel').html(ffbTranslator.translate('BTN_CANCEL'));
        }

        // Set lightbox text
        if (options.text) {
            lb.find('.content').html(options.text);
        }

        // Set okAction
        if (options.okAction) {
            if (options.okAction.caption) {
                okButton.html(options.okAction.caption);
            }
            if (options.okAction.className) {
                okButton.addClass(options.okAction.className);
            }
            if (options.okAction.callBack) {
                okButton.on('click', options.okAction.callBack);
            }
        }

        // Set cancelAction
        if (options.cancelAction && cancelButton) {

            if (options.cancelAction.caption) {
                cancelButton.html(options.cancelAction.caption);
            }
            if (options.cancelAction.className) {
                cancelButton.addClass(options.cancelAction.className);
            }
        }

        // Set cancel click
        if (   typeof options.cancelAction !== 'undefined'
            && typeof options.cancelAction.callBack !== 'undefined'
            && cancelButton
        ) {
            cancelButton.on('click', options.cancelAction.callBack);
        } else {
            cancelButton.on('click', function() {
                lb.find('.close').trigger('click');
            });
        }

        // Create buttons row
        var buttons = $('<div class="row buttons"></div>')
            .append(cancelButton)
            .append(okButton);

        lb.find('.content').append(buttons);
        lb.find('.close').addClass('button white');

        // Show lightbox
        lb.removeClass('invisible');

        // Update position
        _.updatePosition(lb.attr('id'));

        // call callback after display of lightbox
        if (options.callback) {
            options.callback(lb);
        }

        return lb;
    }

    /**
     * Create info lightbox and returns the DOM elemnt
     *
     * @public
     * @param {object} options Properties {'className', 'title', 'text', 'callBack', 'onClose', 'followScroll'}
     * @return object DOM Lightbox div
     */
    _.showInfo = function(options) {

        if (!options) options = {};
        if (options && !options.type) options.type = 'modal';

        //Show base
        var lb = _.showModal(options);
        lb.addClass('info');

        var okButton = $('<div>').attr('class', 'button ok').html(ffbTranslator.translate('BTN_OK'));

        //Set options
        if (options) {

            //Set lightbox text
            if (options.text) lb.find('.content').html(options.text);

            //Set okAction
            if (options.callBack) {
                okButton.on('click', options.callBack(lb));
            } else {
                okButton.on('click', function() {
                    lb.find('.close').trigger('click');
                });
            }
        }

        //Create buttons row
        var buttons = $('<div class="row buttons"></div')
            .append(okButton);

        lb.find('.content').append(buttons);
        lb.find('.close').addClass('button white');
        lb.find('.cancel').remove();

        //Show lightbox
        lb.removeClass('invisible');

        //Update position
        _.updatePosition(lb.attr('id'));

        //CallBack: After load
        if (options.afterLoad) {
            options.afterLoad(lb);
        }

        return lb;
    }

    /**
     * Create progress lightbox and returns the DOM elemnt
     *
     * @public
     * @param {object} options Properties {'className', 'title', 'text', 'followScroll'}
     * @return object DOM lightbox div
     */
    _.showProgress = function(options) {

        if (!options) options = {};
        if (options && !options.type) options.type = 'modal';

        //Show base
        var lb = _.showModal(options);
        lb.addClass('progress');

        //Set options
        if (options) {

            //Set lightbox text
            if (options.text) {
                var html = _.getLoadingAnimation() + '<div class="message"><p>' + options.text + '</p></div>';
                lb.find('.content').html(html);
            }
        }

        lb.find('.close').remove();

        //Show lightbox
        lb.removeClass('invisible');

        //Update position
        _.updatePosition(lb.attr('id'));

        return lb;
    }

    /**
     * Create fullscreen progress lightbox and returns the DOM elemnt
     *
     * @public
     * @param {object} options Properties {}
     * @return object DOM lightbox div
     */
    _.showFullscreenProgress = function(options) {

        if (!options) options = {};
        if (options && !options.type) options.type = 'modal';

        options['className'] = 'fullscreen';

        //Show base
        var lb = _.showModal(options);
        lb.addClass('progress');

        lb.find('.close').remove();

        lb
            .css('left', 0)
            .css('top', 0)
            .css('margin', 0);

        //Show lightbox
        lb.removeClass('invisible');

        return lb;
    }

    /**
     * Update lb position
     *
     * @public
     * @this {ffbLightbox}
     * @param {string} id
     */
    _.updatePosition = function(id) {

        //Update margin
        var lb = $('#' + id);
        if (lb.length === 0) return;

//        var lbs = $('.lightbox');
//        lbs.addClass('hide');
//        var scrollMaxX = window.scrollMaxX || document.documentElement.scrollLeftMax || ($(document).width() - $(window).width());
//        lbs.removeClass('hide');

        //Update lb position and limit content hight
        if (_.adjustInScreen) {

            var wH = $(window).height();
            var targetH = parseInt(wH * 0.8 - lb.find('> .wrapper > .menu').outerHeight());
            var content = lb.find('> .wrapper > .content');
            content.css('height', 'auto');
            var lbCH = content.height();

            if (lbCH > targetH) {
                content.css('height', targetH + 'px');
            }
        }

        var wScrollY = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop;
        var lbH = parseInt((-1 * lb.outerHeight() / 2) + wScrollY);
        var lbW = parseInt(lb.outerWidth() / 2);

        if (lb.outerHeight() > $(window).height()) {
            lbH = parseInt((-1 * $(window).height()*0.9 / 2) + wScrollY);
        }

        // check offset data from lb attr
        var offsetData = lb.attr('data-offset');
        if (offsetData !== undefined) {

            // get data '165 auto auto 50'
            var parsed = offsetData.split(' ');
            if (parsed.length === 4) {

                // get values
                var top  = parseInt(parsed[0]);
                var left = parseInt(parsed[3]);

                if (!isNaN(top)) {
                    lb.css('top', 0);
                    lbH = top;
                }
                if (!isNaN(left)) {
                    lb.css('left', 0);
                    lbH = left;
                }
            }
        }

        // check lb position and window width
        var windowWidth   = 'innerWidth' in window ? window.innerWidth : document.documentElement.clientWidth;
        var wrapperWidth = lb.find('> .wrapper').outerWidth(true);
        if (wrapperWidth > windowWidth) {
            lbW = 0;
            lb.css('left', 0);
        } else {
            lb.css('left', '50%');
        }

        lb.css('margin', lbH + 'px 0 0 -' + lbW + 'px');
    }

    //Init
    _init();
}
