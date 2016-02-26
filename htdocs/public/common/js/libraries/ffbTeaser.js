"use strict";

/**
 * Gallery, teaser
 *
 * @class
 * @param {object} stage
 * @param {string} type
 */
var ffbTeaser = function(container, type, options) {

    this.buttons  = null;
    this.counter  = 0;
    this.cnv      = null;
    this.curr     = 0;
    this.delay    = 10;       //-1 to turn animation off
    this.direct   = 1;        //Default direction 1 - left to right, -1 - right to left
    this.isAnim   = false;
    this.isOver   = false;
    this.isStop   = false;
    this.margin   = 0;
    this.maxWidth = 0;
    this.slides   = [];
    this.stage    = null;
    this.offset   = 0;
    this.options  = {
        'opacity' : {
            'type'     : 'fadeOut', //fadeOutFadeIn, fadeOut
            'onChange' : null       //slide change callback
        },
        'move' : {
            'adjustToCanvas' : false,    //adjust slides width to canvas by resize
            'alignLast'      : false,    //align last slide by animation to right
            'isCarousel'     : false,    //move as carousel
            'moveOffset'     : null      //animation move moveOffset
        }
    }
    this.timer    = null;
    this.touch    = {
        'x' : 0,
        'y' : 0
    }
    this.type     = 'move'; //opacity, move

    /**
     * Change slide with animation from options
     *
     * @public
     * @this ffbTeaser
     * @param {integer} index
     */
    this.changeSlide = function(index) {

        //change slide
        if (this.type === 'move') {
            this.moveCanvas(index);
        } else if (this.type === 'opacity') {
           this.showSlide(index);
        }
    }

    /**
     * Get next slide by direction
     *
     * @private
     * @this ffbTeaser
     * @param {integer} direction
     */
    this.getNextSlideIndex = function(direction) {

        var next      = this.curr + direction;
        var lastIndex = this.slides.length - 1;

        if (next < 0) {
            next = lastIndex;
        } else if (next > lastIndex) {
            next = 0;
        }

        return next;
    }

    /**
     * Init gallery interval
     *
     * @private
     * @this ffbTeaser
     */
    this.initInterval = function() {

        var _ = this;

        //call animation by counter and delay
        this.timer = setInterval(function() {

            //don't update counter during animation or mouseover
            if (!_.isOver && !_.isAnim && !_.isStop) _.counter++;

            if (_.delay > 0 && _.counter >= _.delay) {

                //get next slide
                var next = _.getNextSlideIndex(_.direct);

                //change slide
                _.changeSlide(next);
            }
        }, 1000);
    }

    /**
     * Init navigation elements, buttons and arrows
     *
     * @private
     * @this ffbTeaser
     */
    this.initNavigation = function() {

        var _ = this;

        this.stage.find('> .arrow, > .arrows > .arrow').each(function(i, link) {

            $(link).unbind()
                .click(function(e) {

                    var nextIndex = null;
                    if ($(this).hasClass('next')) {
                        nextIndex = _.getNextSlideIndex(1);
                    } else {
                        nextIndex = _.getNextSlideIndex(-1);
                    }

                    //change slide
                    _.changeSlide(nextIndex);

                    return false;
                });
        });

        //init buttons, reset navi
        var navi = this.stage.find('> .navi');
        var _    = this;
        navi.html('');
        navi.append($('<ul>'));

        //create navi links
        this.slides.each(function(i, slide) {

            var link = $('<a>')
                .attr('href', '#')
                .attr('data-order', i)
                .unbind()
                .click(function(e) {

                    var nextIndex = parseInt($(this).attr('data-order'));

                    //change slide
                    _.changeSlide(nextIndex);

                    return false;
                });
            var li = $('<li>')
                .append(link);

            if (i === _.curr) {
                li.addClass('active');
            }

            navi.find('ul').append(li);
        });

        //cache buttons
        this.buttons = this.stage.find('> .navi li a');

        //Init stage and touch events
        this.initStage();
    }

    /**
     * find the max image width
     *
     * @private
     * @this ffbTeaser
     * @param {string/jObject} selector
     */
    this.findMaxImageWidth = function(selector) {
        if (typeof selector == 'string') {
            selector = $(selector);
        }
        return Math.max.apply(null, selector.map(function () {
            var i = new Image();
            i.src = $(this).attr('src');
            return i.width;
        }).get());
    }

    /**
     * Reinit base values and slides by resize if needed
     *
     * @private
     * @this ffbTeaser
     */
    this.initOnResize = function() {
        var _ = this;

        _.stage.find('> .canvas ul.slides > li img').on('load', function() {
            _.maxCanvasWidth = _.findMaxImageWidth(_.stage.find('> .canvas ul.slides > li img'));
            _.onResizeEvent();
        });

        $(window).on('resize', function() {
            _.onResizeEvent();
        });
    }

    /**
     * resize event method
     *
     * @private
     * @this ffbTeaser
     */
    this.onResizeEvent = function() {
        //update stage height for opacioty animation
        if (this.type === 'opacity') {
            this.updateStageHeight();
        }

        //update slides to new size if needed
        if (this.type === 'move') {

            var canvasWidth = this.stage.width();

            if (canvasWidth > this.maxCanvasWidth) {
                canvasWidth = this.maxCanvasWidth;
            }

            this.stage.find('> .canvas').css('width', canvasWidth+'px');

            var newWidth = null;
            newWidth = parseInt(canvasWidth);

            if (newWidth !== this.offset) {
                //reinit gallery
                this.prepareSlides(true);

                //calc last position
                var newMargin = -1 * this.offset * this.curr;

                var slidesElement = this.stage.find('> .canvas >.slides');

                //stop animation on resize
                if (this.isAnim) {
                    slidesElement.stop();
                }

                //set last position after resize
                slidesElement.css('margin', '0 0 0 '+newMargin+'px');
            }
        }
    }

    /**
     * Init stage listeners, touch
     *
     * @private
     * @this ffbTeaser
     */
    this.initStage = function() {

        this.stage.unbind();

        var _ = this;

        //stop slides change by mouse over
        this.stage.on('mouseover', function(e) {
            _.isOver = true;
        });

        this.stage.on('mouseleave', function(e) {
            _.isOver = false;
        });

        //Save start position by touch start
        this.stage.on('touchstart', function(event) {

            if (event.originalEvent !== undefined) event = event.originalEvent;

            _.touch.x = event.targetTouches[0].pageX;
            _.touch.y = event.targetTouches[0].pageY;
        });

        //Move content
        this.stage.on('touchmove', function(event) {

            if (event.originalEvent !== undefined) event = event.originalEvent;

            var posX = event.targetTouches[0].pageX;
            var posY = event.targetTouches[0].pageY;
            var diffX = _.touch.x - posX;
            var diffY = _.touch.y - posY;

            var nextIndex = null;
            if (diffX > 0) {
                nextIndex = _.getNextSlideIndex(1);
            } else {
                nextIndex = _.getNextSlideIndex(-1);
            }

            if (Math.abs(diffX) > 100) {

                _.touch.x = posX;

                //change slide
                _.changeSlide(nextIndex);
            }

            //if touch is vertical, scroll
            if (Math.abs(diffY) > 25) {
                window.scrollBy(0, diffY);
            }

            return false;
        });
    }

    /**
     * Change slide with move animation
     *
     * @public
     * @this ffbTeaser
     * @param {integer} newIndex
     */
    this.moveCanvas = function(newIndex) {

        //if animation proceed return
        if (this.isAnim) return;

        //check carousel
        var nextItem = false;

        if (this.options.move.isCarousel &&
            (this.curr === this.slides.length - 1 && newIndex === 0) ||
            (this.curr === 0 && newIndex === this.slides.length - 1)
        ) {

            if (this.curr === 0) {
                //get last slide, clone it
                nextItem = this.cnv.children().last().clone();

                //prepend
                this.cnv.prepend(nextItem);

                //set canvas to second item
                this.cnv.css('margin', '0 0 0 -'+this.offset+'px');

                var newMargin = 0;

            } else {
                //get first slide, clone it
                nextItem = this.cnv.children().first().clone();

                //add to end
                this.cnv.append(nextItem);

                //get new margin
                var newMargin = -1 * this.offset * this.slides.length;
            }

            //update width
            this.cnv.css('width', this.cnv.children().length * this.offset);

            //get new max width
            var maxWidth = this.offset * ( this.slides.length + 1);

        } else {

            //calc new margin
            var newMargin = -1 * this.offset * newIndex;
            var maxWidth = this.maxWidth;
        }

        //check new margin if alignLast is true
        if (this.options.move.alignLast && newIndex === this.slides.length - 1) {

            var canvaWidth = parseInt(this.stage.find('> .canvas').width());
            newMargin = newMargin + (canvaWidth - this.offset);
        }

        //check borders
        if (newMargin <= 0 && Math.abs(newMargin) < maxWidth) {

            //start animation
            this.isAnim = true;

            //set active button
            $(this.buttons[this.curr]).parent().removeClass('active');
            $(this.buttons[newIndex]).parent().addClass('active');

            //update current margin and slide
            this.margin = newMargin;
            this.curr   = newIndex;

            var _ = this;

            //move canvas
            this.cnv.animate(
                {
                    'margin' : '0 0 0 ' + this.margin + 'px'
                },
                {
                    'duration' : 800,
                    'easing'   : 'swing',//swing, linear
                    'complete' : function() {

                        _.isAnim = false;

                        //if carousel and last element
                        if (_.options.move.isCarousel && nextItem) {

                            if (nextItem.index() == 0) {
                                //move to last item
                                _.margin = -1 * _.offset * (_.slides.length - 1);

                                //set margin to last
                                _.cnv.css('margin', '0 0 0 '+_.margin+'px');
                            }
                            else {
                                //move to start
                                _.margin = 0;

                                //set margin to 0
                                _.cnv.css('margin', _.margin);
                            }

                            //remove element
                            nextItem.remove();
                            nextItem = false;

                            //remove element
                            //_.cnv.children().last().remove();

                            //set width back
                            _.cnv.css('width', this.maxWidth);
                        }
                        //if (_goNext !== false) _.moveCanvas(_goNext);
                    }
                }
            );
            //_refreshNavigation();
        }

        //reset counter
        this.counter = 0;
    }

    /**
     * Get sliders, get offset, maxWidth, set sliders order
     *
     * @public
     * @this ffbTeaser
     */
    this.prepareSlides = function(isRefresh, isRestart) {

        //get canvas width
        this.offset = parseInt(this.stage.find('> .canvas').width());

        //set offset from options
        if (this.options.move.moveOffset !== null) {
            this.offset = parseInt(this.options.move.moveOffset);
        }

        //init start values
        if (!isRefresh) {

            //reset margin and current slide
            this.margin = 0;
            this.curr   = 0;

            //get slides
            this.slides = this.stage.find('> .canvas > .slides > .slide');

        } else {

            this.margin = -1 * this.curr * this.offset;
        }

        //calc max width
        this.maxWidth = this.slides.length * this.offset;

        //init stage height for opacity
        if (this.type === 'opacity') {

            this.updateStageHeight();
            this.slides.css('opacity', 0).first().css('opacity', 1);
        }

        //update max width for canvas, for move animation
        if (this.type === 'move') {

            if (this.options.move.adjustToCanvas) {
                this.stage.addClass('responsive');
                this.slides.css('width', this.offset + 'px');
            }

            var css = {
                'width' : this.maxWidth + 'px'
            };
            if (isRefresh || isRestart) {
                css['margin'] = '0 0 0 ' + this.margin + 'px';
            }
            this.cnv.css(css);
        }
    }

    /**
     * Reinit all sliders and margins to 0
     *
     * @public
     * @this ffbTeaser
     */
    this.restart = function() {

        this.stop();
        this.prepareSlides(false, true);
        this.initNavigation();
        this.start();
    }

    /**
     * Change slide with opacity animation
     *
     * @public
     * @this ffbTeaser
     * @param {integer} newIndex
     */
    this.showSlide = function(newIndex) {

        //don't animate if animation proceed or wrong index
        if (this.isAnim                       ||
            newIndex < 0                      ||
            newIndex > this.slides.length - 1 ||
            newIndex === this.curr
        ) {
            return;
        }

        //animation is started
        this.isAnim = true;

        //prepare new slide, set to top
        $(this.slides[newIndex]).css({
            'opacity' : 0,
            'zIndex' : 3
        });

        //current slide set under
        $(this.slides[this.curr]).css('zIndex', 2);

        //set active button
        $(this.buttons[this.curr]).parent().removeClass('active');
        $(this.buttons[newIndex]).parent().addClass('active');

        var _ = this;

        //select animation type
        switch (this.options.opacity.type) {

            case 'fadeOutFadeIn':

                //animate opacity
                $(this.slides[_.curr]).animate(
                    {
                        'opacity' : 0
                    },
                    {
                        'duration' : 400,
                        'complete' : function() {

                            //animate opacity
                            $(_.slides[newIndex]).animate(
                                {
                                    'opacity' : 1
                                },
                                {
                                    'duration' : 400,
                                    'complete' : function() {

                                        //wenn is done, set index for old slide to 1
                                        $(_.slides[_.curr]).css('zIndex', 1);

                                        //update current index
                                        _.curr   = newIndex;

                                        //callback
                                        if (_.options.opacity.onChange) {
                                            _.options.opacity.onChange(this, _.curr);
                                        }

                                        //animation is over
                                        _.isAnim = false;
                                    }
                                }
                            );
                        }
                    }
                );

                break;

            case 'fadeOut':
            default:

                //animate opacity
                $(this.slides[newIndex]).animate(
                    {
                        'opacity' : 1
                    },
                    {
                        'duration' : 400,
                        'complete' : function() {

                            //wenn is done, set index for old slide to 1
                            $(_.slides[_.curr]).css('zIndex', 1);

                            //update current index
                            _.curr   = newIndex;

                            //callback
                            if (_.options.opacity.onChange) {
                                _.options.opacity.onChange(this, _.curr);
                            }

                            //animation is over
                            _.isAnim = false;
                        }
                    }
                );

                break;
        }

        //reset counter
        this.counter = 0;
    }

    /**
     * Start animation
     *
     * @public
     * @this ffbTeaser
     */
    this.start = function() {

        this.isStop = false;
    }

    /**
     * Stop animation
     *
     * @public
     * @this ffbTeaser
     */
    this.stop = function() {

        this.isStop = true;
    }

    /**
     * Update stage height to slide height
     *
     */
    this.updateStageHeight = function() {

        var stageHeight = this.stage.height();
        var slideHeight = $(this.slides[this.curr]).height();
        if (stageHeight !== slideHeight) {
            this.stage.css('height', slideHeight + 'px');
        }
    }

    /**
     * Init gallery
     *
     * @public
     * @this ffbTeaser
     * @param {object} stage
     * @param {string} type
     */
    this.init = function(container, type, options) {

        //check stage
        if (typeof container === 'object') {
            this.stage = $(container);
        } else {
            this.stage = $('#' + container);
        }
        if (this.stage.length === 0) return;

        this.cnv = this.stage.find('> .canvas > .slides');

        //save animtype
        if (type) this.type = type;

        //parse options
        if (options !== undefined) {
            for (var key in options) {
                if (!options.hasOwnProperty(key)) continue;

                if (this.options[key] !== undefined) {
                    this.options[key] = options[key];
                }
            }
        }

        this.stage.addClass(this.type);

        //prepare slides
        this.prepareSlides();

        //if no slides, return
        if (this.slides.length === 0) return;

        //init navi elenments and touch events
        this.initNavigation();

        //start interval
        this.initInterval();

        //init resize actions
        this.initOnResize();

        //set gallery active
        this.stage.addClass('active');
    }

    //init gallery
    this.init(container, type, options);
}
