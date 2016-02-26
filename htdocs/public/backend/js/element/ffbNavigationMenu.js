"use strict";

/**
 * Navigation menu
 *
 * @param {HTMLElement} container
 * @returns {ffbNavigationMenu}
 */
var ffbNavigationMenu = function(container, opt) {

    var _           = this;
    var _isAnim     = false;
    var _cnt        = null; // container div
    var _slideRight = false;
    var _slideLeft  = false;
    var _elem       = null;
    var _overlay    = null;
    var _request    = null;
    var _copy       = false;
    var _delete     = false;
    var _dialog     = null;
    var _opt        = {
        'copyCallBack': null,
        'deleteCallBack': null,
        'refreshNavigation': true
    };

    /**
     * init events
     *
     * @this {ffbNavigationMenu}
     * @private
     */
    var _initEvents = function() {

        // remove overlay on click on other event
        _cnt.find('.pane-navi-link').on('click', function(e) {
            e.preventDefault();

            // set active elements
            _cnt.find('.pane-navi-link-cnt.active').removeClass('active');
            _cnt.find('.pane-navi-link.selected').removeClass('selected');
            $(this).addClass('selected');
            $(this).closest('.pane-navi-link-cnt').addClass('active');

            if (_slideRight && ! _slideLeft) {

                _slideRight = false;
                _slideLeft = true;
                _.slideOut();
            }
        });

        // init actions overlay
        _cnt.find('.pane-navi-link-cnt .entry-action').on('click', function(e) {

            _elem       = $(this);
            _slideRight = true;
            _slideLeft  = false;

            if (_slideLeft && !_isAnim || _slideRight && !_isAnim ) {

                // calculate offset
                var top  = $(_elem).offset().top - $(_cnt).offset().top + $(_cnt).scrollTop();
                var left = _elem.offset().left + 240 - 60;

                // create overlay html
                var isCopyAvailable   = _cnt.find('.pane-navi-link.selected').attr('data-copy-url');
                var isDeleteAvailable = _cnt.find('.pane-navi-link.selected').attr('data-delete-url');

                var title = _elem.parent().find('.pane-navi-link').attr('title');
                var text  = _elem.parent().find('> .entry-name').text();

                var textSpan = _elem.parent().find('> .entry-name span.lang-' + myApp.langSwitcher.getActiveLanguageCode());
                if (textSpan.length > 0) {
                    text  = textSpan.text();
                }

                _overlay = $('<li class="overlay-wrapper" style="top:' + top + 'px; left:' + left + 'px">' +
                    '<span class="overlay delete' + (isDeleteAvailable === undefined ? ' disabled':'') + '"></span>' +
                    '<span class="overlay copy' + (isCopyAvailable === undefined ? ' disabled':'') + '"></span>' +
                    '<span class="overlay-title" title="' + title + '">' +
                        '<span class="overlay-title-text">' + text + '</span>' +
                    '</span>' +
                    '</li>');

                // init confirm events
                _.initConfirmEvents();

                // click delete
                _overlay.find('.overlay.delete').on('click', function() {

                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    // set state
                    _delete = true;
                    _copy   = false;

                    _overlay.css('height', '80px');

                    if (_dialog === null) {

                        $(_.getDialog()).appendTo(_overlay).hide().fadeIn(200);
                        _overlay.find('.dialog .dialog-confirm .arrow-up').removeClass('hide');
                        _overlay.find('.dialog .dialog-cancel .arrow-up').addClass('hide');
                        _.initConfirmEvents();
                    }
                });

                // click copy
                _overlay.find('.overlay.copy').on('click', function() {

                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    // set state
                    _delete = false;
                    _copy   = true;

                    _overlay.css('height', '80px');

                    if(_dialog === null) {

                        $(_.getDialog()).appendTo(_overlay).hide().fadeIn(200);
                        _overlay.find('.dialog .dialog-cancel .arrow-up').removeClass('hide');
                        _overlay.find('.dialog .dialog-confirm .arrow-up').addClass('hide');
                        _.initConfirmEvents();
                    }
                });

                _.anim();
            }
        });
    }

    /**
     * Create confirmation dialog html
     *
     * @this {ffbNavigationMenu}
     * @public
     * @returns {HTMLElement}
     */
    _.getDialog = function() {

        // msg for delete
        var title = ffbTranslator.translate('MSG_CONFIRM_DELETE');
        if (_copy) {
            // msg for copy
            title = ffbTranslator.translate('MSG_CONFIRM_COPY');
        }

        _dialog = $('<span class="dialog">' +
            '<span class="dialog-title">' + title + '</span>' +
            '<span class="dialog-confirm">' +
            '<span class="arrow-up hide"></span>' +
            '</span>' +
            '<span class="dialog-cancel">' +
            '<span class="arrow-up hide"></span>' +
            '</span>' +
            '</span>');

        return _dialog;
    }

    /**
     * Init event for confirmation dialog
     *
     * @this {ffbNavigationMenu}
     * @public
     * @returns {HTMLElement}
     */
    _.initConfirmEvents = function () {

        // remove overlay on cancel click
        _overlay.find('.dialog .dialog-cancel').on('click', function() {
            _.slideOut();
        });

        // remove overlay
        _overlay.find('.overlay-title').on('click', function() {
            _.slideOut();
        });

        _overlay.find('.dialog .dialog-confirm').on('click', function() {

            var newAjax = new ffbAjax();

            if (_copy && !_delete) {

                if (_request) {
                    _request.abort();
                }

                if (_cnt.find('.pane-navi-link.selected').attr('data-copy-url') === undefined) {
                    return;
                }

                _request = newAjax.call(
                    _cnt.find('.pane-navi-link.selected').attr('data-copy-url'),
                    function(data) {

                        var result = ajax.isJSON(data);

                        if (result.state == 'ok') {

                            var callBack = null;
                            if (_opt.hasOwnProperty('copyCallBack')) {
                                callBack = _opt.copyCallBack;
                            }

                            myApp.refreshNavigation(result.callBackUrl, callBack);

                        } else {

                            var errorData = ajax.parseError(data);
                            ffbLightbox.showInfo({
                                'title'     : ffbTranslator.translate('TTL_ERROR'),
                                'className' : 'error',
                                'text'      : errorData.message
                            });
                        }

                    },
                    {
                        'type'      : 'post',
                        'accepts'   : 'json'
                        //'indicator' : $('.panemanager .panemanager-pane.main-navi-pane .main-navi-list')
                    }
                );
            }

            if (!_copy && _delete) {

                if (_request) {
                    _request.abort();
                }

                var deleteUrl = _cnt.find('.pane-navi-link.selected').attr('data-delete-url');
                if (deleteUrl === undefined) {
                    return;
                }

                _request = newAjax.call(
                    deleteUrl,
                    function(data) {

                        var result = ajax.isJSON(data);

                        if (result.state == 'ok') {

                            var callBack = null;
                            if (_opt.hasOwnProperty('deleteCallBack')) {
                                callBack = _opt.deleteCallBack;
                            }

                            if (_opt.refreshNavigation) {
                                myApp.refreshNavigation(result.callBackUrl, callBack);
                            } else {
                                callBack();
                            }

                        } else {

                            var errorData = ajax.parseError(data);
                            ffbLightbox.showInfo({
                                'title'     : ffbTranslator.translate('TTL_ERROR'),
                                'className' : 'error',
                                'text'      : errorData.message
                            });
                        }
                    },
                    {
                        'type'      : 'post',
                        'accepts'   : 'json'
                        //'indicator' : $('.panemanager .panemanager-pane.main-navi-pane .main-navi-list')
                    }
                );
            }
        });
    }

    /**
     * slide out
     *
     * @this {ffbNavigationMenu}
     */
    _.slideOut = function() {

        _elem       = $(this);
        _slideRight = false;
        _slideLeft  = true;

        if (_slideLeft && !_isAnim || _slideRight && !_isAnim ) {
            _.anim(true);
        }
     }

    /**
     *
     * @this {ffbNavigationMenu}
     * @param resetSize
     */
    _.anim = function(resetSize) {

        _isAnim = true;
        var param;
        var duration = 500;

        if (_slideLeft) {
            param = 240;
        }

        if (_slideRight) {
            param = 0;
        }

        //dont animate if not opeened
        if (_slideLeft && _cnt.find('.overlay-wrapper').length === 0) {
            _isAnim = false;
            return;
        }

        _cnt.append(_overlay);

        $(_overlay).animate({
            'left' : param + 'px'
        }, {
            'duration' : duration,
            'easing'   : 'linear',
            'complete' : function() {

                if (resetSize) {

                    _overlay.find('.dialog .dialog-cancel .arrow-up').removeClass('hide');
                    _overlay.find('.dialog .dialog-confirm .arrow-up').removeClass('hide');
                    _overlay.remove();
                    //_dialog.remove();
                    _dialog = null;
                }
                _isAnim = false;
            }
        });
    }

    /**
     *
     * @this {ffbNavigationMenu}
     * @param container
     * @returns {ffbVerticalScroll}
     */
    _.init = function(container, opt) {

        _cnt = $(container);

        //Init options
        for (var key in opt) {
            if(_opt[key] !== undefined) _opt[key] = opt[key];
        }

        if (_cnt.length === 0) {
            return;
        }

        _initEvents();

        _cnt.addClass('dialog-inited');

        return _;
    }

    _.init(container, opt);

}