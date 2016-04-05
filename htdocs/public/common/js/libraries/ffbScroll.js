"use strict";

//Design scroll
var ffbScroll = function(element, height, onMove, isSmoothMove, isEventsOnly) {

    var _ = this;

    //Check for element
    _.container     = $(element);
    if (_.container.length === 0) return false;

    _.browser       = {
        'name'    : null,
        'version' : null
    }
    _.currentY      = null;
    _.handler       = null;
    _.handlerTop    = 1;
    _.handlerHeight = 77;
    _.handlerMaxTop = 0;
    _.fullHeight    = _.container.height();
    _.height        = height ? height : _.fullHeight;
    _.interval      = null;
    _.isEventsOnly  = isEventsOnly !== null ? isEventsOnly:false;
    _.isTouch       = false;
    _.isMove        = false;
    _.isOn          = false;
    _.isSmoothMove  = isSmoothMove !== null ? isSmoothMove:false;
    _.onMove        = onMove;
    _.renderBlocks  = true;     //rende top/bottom elements in handler and column
    _.targetTop     = 0;
    _.touchStartX   = null;
    _.touchStartY   = null;
    _.inited        = false;

    _.init = function() {

        //Check browser
        _.browser = _.getBrowser();

        //Check if scroller needed
        if (_.height >= _.fullHeight) {

            _.isOn = false;

            //Check if scroll exist to hide
            if (_.isExist()) {

                _.container.parent().addClass('off');
            }
            return _;
        }

        //Init interval for smooth move
        if (_.isSmoothMove) _.initSmoothInterval();

        if (!_.isEventsOnly) {

            //Put container to scroll div
            _.initScroller();
        }

        //Check iPad
        if ($.support.touch || navigator.userAgent.match(/iPad/i) != null) {
            _.isTouch = true;
        }

        //Init handler actions
        setTimeout(function() {

            if (!_.isEventsOnly) {
                _.initHandler();
                _.initColumn();
            }

            if (_.isTouch) _.initTouch();
            else _.initWheel();

            _.inited = true;
        }, 0);

        _.isOn = true;

        return _;
    }

    _.getBrowser = function() {

        var ua  = navigator.userAgent;
        var tem = null;
        var M   = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*([\d\.]+)/i) || [];

        if(/trident/i.test(M[1])) {

            tem = /\brv[ :]+(\d+(\.\d+)?)/g.exec(ua) || [];
            return 'IE '+ (tem[1] || '');
        }

        M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];

        tem = ua.match(/version\/([\.\d]+)/i);
        if (tem !== null) {
            M[2]= tem[1];
        }

        return {'name' : M[0], 'version' : M[1]};
    }

    //Put container to scroll div
    _.initScroller = function() {

        //Get handler height
        var diff        = _.fullHeight - _.height;
        _.handlerHeight = parseInt((1 - (diff / _.fullHeight)) * _.height);

        if (_.handlerHeight < 10) _.handlerHeight = 10;

        //Check if scroll divs exist and update them
        var isExist = _.isExist();
        if (isExist) {

            var scrollColumn  = _.container.parent().find('.column');
            var scrollHandler = scrollColumn.children().last();
            _.container.parent().removeClass('off');
            _.container.parent().css('height', _.height + 'px');
            scrollColumn.css('height', _.height - 2 + 'px');
            scrollHandler.css('top', '1px');
        } else {

            //Create new divs
            //Create main div
            var mainDivClass = ['ffb-scroll'];
            var oldIe        = false;
            if ((_.browser.name && _.browser.name === 'IE') || _.renderBlocks === true) {

                if (_.browser.version === 9) mainDivClass.push('ie9');
                if (_.browser.version < 9) {

                    mainDivClass.push('ie8');
                    oldIe = true;
                }
            }

            var scrollDiv = window.document.createElement('div');
            scrollDiv.className    = mainDivClass.join(' ');
            scrollDiv.style.height = _.height + 'px';

            //Create vertical column div
            var scrollColumn = window.document.createElement('div');
            scrollColumn.className    = 'column';
            scrollColumn.style.height = _.height - 2 + 'px';
            if (oldIe) {
                var divTop          = window.document.createElement('div');
                divTop.className    = 'c-top';
                scrollColumn.appendChild(divTop);
                var divCenter       = window.document.createElement('div');
                divCenter.className = 'c-center';
                divCenter.style.height = _.height - 8 + 'px';//2-6
                scrollColumn.appendChild(divCenter);
                var divBottom       = window.document.createElement('div');
                divBottom.className = 'c-bottom';
                scrollColumn.appendChild(divBottom);
            }

            //Create handler
            var scrollHandler = window.document.createElement('div');
            scrollHandler.className   = 'handler';
            scrollHandler.style.top   = '1px';
            if (oldIe) {
                var divTop          = window.document.createElement('div');
                divTop.className    = 'h-top';
                scrollHandler.appendChild(divTop);
                var divCenter       = window.document.createElement('div');
                divCenter.className = 'h-center';
                divCenter.style.height = (_.handlerHeight - 7) + 'px';//1-6
                scrollHandler.appendChild(divCenter);
                var divBottom       = window.document.createElement('div');
                divBottom.className = 'h-bottom';
                scrollHandler.appendChild(divBottom);
            }
        }

        //Get container parent
        //var containerParent = _.container.parent();

        //Set top to 0
        _.container.css({
            'top'      : '0px',
            'position' : 'absolute'
        });

        if (!isExist) {

            //Add main div before container
            $(scrollDiv).insertBefore(_.container);

            //Put childs into main div
            $(scrollColumn).append(scrollHandler);
            $(scrollDiv).append(_.container);
            $(scrollDiv).append(scrollColumn);
        }

        //Set handler
        _.handler              = $(scrollHandler);
        _.handlerTop           = 1;
        _.handler.css({'height' : (_.handlerHeight - 1) + 'px'});
        _.handlerMaxTop        = _.height - 2 - _.handlerHeight;

        return true;
    }

    //Init handler actions
    _.initHandler = function() {

        //Add Events
        _.handler = _.container.next('.column').find('.handler');

        _.handler.unbind();
        _.handler.on('mousedown', function(event) {

            if (event.originalEvent !== undefined) {
                event = event.originalEvent;
            }

            _.isMove = true;
            if (_.currentY === null) {
                _.currentY = event.clientY;
            }

            return false;
        });

        if (!_.inited) $(document).on('mouseup', function(event) {

            if (_.isMove) {
                _.isMove = false;
                _.currentY = null;
                return false;
            }
        });

        if (!_.inited) $(document).on('mousemove', function(event) {

            if (_.handler) {

                if (_.isMove) {

                    if (event.originalEvent !== undefined) event = event.originalEvent;

                    var y = event.clientY;
                    if (_.currentY > y && _.handlerTop > 0) {
                        //Move up
                        _.handlerTop -= _.currentY - y;
                        if (_.handlerTop < 0) _.handlerTop = 0;
                        _.handler.css({'top' : _.handlerTop + 'px'});

                    } else if (_.currentY < y && _.handlerTop < _.handlerMaxTop) {
                        //Move down
                        _.handlerTop += y - _.currentY;
                        if (_.handlerTop > _.handlerMaxTop) _.handlerTop = _.handlerMaxTop;
                        _.handler.css({'top' : _.handlerTop + 'px'});
                    }
                    _.currentY = y;

                    //Move inner content
                    _.moveContent();

                    return false;
                }
            }
        });
    }

    _.initColumn = function() {

        //Get column
        var column = _.handler.closest('.column');

        //Add onClick eent
        column.unbind();
        column.on('click', function(event) {

            if (event.originalEvent !== undefined) event = event.originalEvent;

            var target = event.target || event.explicitOriginalTarget || event.srcElement;

            //Check if target is column, not handler
            if (target !== _.handler) {

                //Get event mosue position in column
                var y = 0;
                if (_.browser.name === 'msie') {
                    y = event.y;
                } else if (_.browser.name === 'safari') {
                    y = Math.max(event.layerY, event.offsetY);
                } else {
                    y = event.layerY || event.y;
                }

                //Get 1/2 from handler height
                var hPart = (_.handlerHeight - 1) / 2;

                //Check if y < 1/2 habdler or mehr
                if (y <= hPart) {
                    _.handlerTop = 0;
                } else if (y >= _.height - hPart) {
                    _.handlerTop = _.height - 2 - _.handlerHeight;
                } else {
                    _.handlerTop = y - hPart;
                }

                //Drop move options, update top position
                _.currentY = null;

                _.handler.css({'top' : _.handlerTop + 'px'});

                _.moveContent();
            }

            return false;
        });
    }

    //Init scroll by mousewell
    _.initWheel = function() {

        var onWheel = function(event) {

            if (!_.isOn) return;

            if (event.originalEvent !== undefined) event = event.originalEvent;

            //Get wheel direction
            var offset = 0;
            if (event.detail) {

                //Firefox -3Up/3Down
                if (event.detail > 0) offset = 1;
                if (event.detail < 0) offset = -1;
            } else if (event.wheelDelta) {
                //Chrome 120Up/-120Down

                if (event.wheelDelta > 0) offset = -1;
                if (event.wheelDelta < 0) offset = 1;
            }

            var offsetDelta = 10;

            //Update hander position
            _.handlerTop += offset*offsetDelta;

            //Check min/max values
            if (_.handlerTop < 0) _.handlerTop = 0;
            if (_.handlerTop > _.handlerMaxTop) _.handlerTop = _.handlerMaxTop;

            //Drop move options, update top position
            _.currentY = null;

            if (!_.isEventsOnly) {

                _.handler.css({'top' : _.handlerTop + 'px'});
            }

            _.moveContent(offset);

            return false;
        }

        _.container.unbind();
        _.container.on('mousewheel', onWheel);
        _.container.on('DOMMouseScroll', onWheel);
    }

    _.initTouch = function() {

        _.container.unbind();

        //Save start position by touch start
        _.container.on('touchstart', function(event) {

            if (!_.isOn) return;

            if (event.originalEvent !== undefined) event = event.originalEvent;

            _.touchStartX = event.targetTouches[0].pageX;
            _.touchStartY = event.targetTouches[0].pageY;
        });

        //Move content
        _.container.on('touchmove', function(event) {

            if (!_.isOn) return;

            if (event.originalEvent !== undefined) event = event.originalEvent;

            var posY = event.targetTouches[0].pageY;
            var diff = _.touchStartY - posY;

            //Update hander position
            _.handlerTop += diff;

            //Check min/max values
            if (_.handlerTop < 0) _.handlerTop = 0;
            if (_.handlerTop > _.handlerMaxTop) _.handlerTop = _.handlerMaxTop;

            //Drop move options, update top position
            _.currentY = null;

            if (!_.isEventsOnly) {
                _.handler.css({'top' : _.handlerTop + 'px'});
            }

            _.moveContent(diff);

            return false;
        });
    }

    //Move content
    _.moveContent = function(direction) {

        if (!_.isEventsOnly) {

            var diff = _.fullHeight - _.height;
            var r = diff / _.handlerMaxTop;

            //Make smooth move or direct
            if (_.isSmoothMove) {

                _.targetTop = parseInt(_.handlerTop*r);

                _.initSmoothInterval();
            } else {

                _.container.css({
                    'top' : '-' + parseInt(_.handlerTop*r) + 'px'
                });
            }
        }

        var func = null;
        if (_.onMove) func = _.getFunctionsParts(_.onMove);
        if (func) func(_.container, _.handlerTop, _.fullHeight, _.height, direction);
    }

    //Move to
    _.scrollTo = function(y) {

        if (!isNaN(y) && _.isOn) {

            if (y < 0) y = 0;
            if (y > _.handlerMaxTop) y = _.handlerMaxTop;

            _.handlerTop = y;
            _.handler.css({'top' : _.handlerTop + 'px'});

            _.moveContent();
        }
    }

    //Parser for function name, to get Object and Method
    _.getFunctionsParts = function(functionName) {

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

    _.isExist = function() {

        return $(_.container.parent()).hasClass('ffb-scroll');
    }

    //Update scroller height
    _.updateHeight = function(height) {

        _.height     = height ? height : _.fullHeight;
        _.fullHeight = _.container.height();
        _.init();
    }

    //Move content by timer
    _.initSmoothInterval = function() {

        //Clear interval if exist
        if (_.interval) clearInterval(_.interval);

        //Get direction, set step size
        _.interval = setInterval(function() {

            //Dont move content if off
            if (!_.isOn) return;

            var offsetTop = _.container.position().top;
            if (_.targetTop === Math.abs(offsetTop)) clearInterval(_.interval);

            var stepSize = 1;
            var step     = 0;
            var targetT  = _.targetTop * -1;
            var diff     = Math.abs(offsetTop - targetT);
            var delta    = diff / _.fullHeight;
            stepSize     = 1 + parseInt(delta*100);

            if (offsetTop > targetT) {

                step = -1*stepSize;
            } else if (offsetTop < targetT) {

                step = 1*stepSize;
            }

            _.container.css({
                'top' : offsetTop + step + 'px'
            });

        }, 33);//33 - 30fps
    }

    _.init();
}
