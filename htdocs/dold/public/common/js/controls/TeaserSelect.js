/*jshint -W117 */
"use strict";

/**
 * Select with images
 *
 * @class
 * @param {string|select} element
 */
var TeaserSelect = function(element, onSelect) {   
   
    /**
     * Select element
     *
     * @var {object} el
     *
     */
    this.el = null;

    /**
     * Options from el value attribute
     *
     * @var {object} options
     */
    this.options = {
        'images' : {}
    };

    /**
     * ffbDropdown
     * 
     * @var {ffbDropdown} select
     */
    this.select = null;

    // init teaser
    this.init(element, onSelect);
};

/**
 * Init teaser select
 *
 * @public
 * @this TeaserSelect
 * @param {object} element
 */
TeaserSelect.prototype.init = function(element, onSelect) {

    //check element
    this.el = $(element);
    if (this.el.length === 0) {
        return null;
    }

    //get options parse them
    var images = ajax.isJSON(this.el.attr('data-images'));

    //fill options
    if (images && typeof images === 'object') {
        for (var key in images) {
            this.options.images[key] = images[key];
        }
    }

    //create view and edit html
    this.render();
};

/**
 * Render control elements, based on view type
 *
 * @public
 * @this TeaserSelect
 */
TeaserSelect.prototype.render = function() {

    //create wrapper
    var cnt = $('<div class="fileupload-wrapper teaserselect"></div>');

    //relace
    this.el.replaceWith(cnt);
    cnt.append(this.el);

    //check view type
    var parent = this.el.parents('.row');
    parent.addClass('teaserselect');

    if (parent.hasClass('editable')) {
        this.renderInplace();
    } else {
        this.renderStandard();
    }
};

/**
 * Render gallery html and init listeners
 *
 * @public
 * @this TeaserSelect
 * @param {object} container
 */
TeaserSelect.prototype.renderGallery = function(container) {

    var isView = false;
    if (container) {
        isView = true;
        container.html('');
    } else {
        //get parent and remove previously list
        var parent = this.el.parents('.fileupload-wrapper.teaserselect');
        parent.find('.fileupload-gallery')
                .remove();
        container = parent;
    }

    var self        = this;
    var gal         = $('<div class="fileupload-gallery"></div>');
    var canvas      = $('<div class="canvas"></div>');
    var isActiveImg = false;
    var images      = [];
    var options     = this.el.children('option');

    $(options).each(function(i, opt) {

        var img = $('<img>')
                .attr('src', self.options.images[$(opt).val()])
                .attr('alt', $(opt).text())
                .attr('data-value', $(opt).val());

        // DERTMS-829
        // order of images should be identical to order of themes in dropdown
        // if (self.el.val() === $(opt).val()) {
        //
        //    img.addClass('active');
        //     isActiveImg = true;
        //     canvas.append(img);
        // } else {
        //     images.push(img);
        // }

        if (self.el.val() === $(opt).val()) {
            img.addClass('active');
            isActiveImg = true;
        }
        images.push(img);

    });

    //set active image first
    for (var i = 0; i < images.length; i++) {
        canvas.append(images[i]);
    }

    //add arrows
    if (options.length > 1) {

        canvas.append(
            $('<div class="arrow prev hide"></div>')
                .on('click', function() {

                    var galP = self.el.parents('.row');

                    var active    = galP.find('img.active');
                    var newActive = active.prev('img');
                    if (active.first().prev('img').length > 0) {
                        active.removeClass('active');
                        newActive.addClass('active');
                    }

                    //show/hide arrows
                    galP.find('.arrow.next').removeClass('hide');
                    if (active.prev('img').prev('img').length === 0) {
                        galP.find('.arrow.prev').addClass('hide');
                    }

                    //select current theme
                    self.el.val(newActive.attr('data-value'));
                    self.select.setValue(newActive.attr('data-value'));
                    //galP.find('.total').html(newActive.attr('alt'));

                    return false;
                })
        );
        canvas.append(
            $('<div class="arrow next hide"></div>')
                .on('click', function() {

                    var galP = self.el.parents('.row');

                    var active    = galP.find('img.active');
                    var newActive = active.next('img');
                    if (active.first().next('img').length > 0) {
                        active.removeClass('active');
                        newActive.addClass('active');
                    }

                    //show/hide arrows
                    galP.find('.arrow.prev').removeClass('hide');
                    if (active.next('img').next('img').length === 0) {
                        galP.find('.arrow.next').addClass('hide');
                    }

                    //select current theme
                    self.el.val(newActive.attr('data-value'));
                    self.select.setValue(newActive.attr('data-value'));
                    //galP.find('.total').html(newActive.attr('alt'));

                    return false;
                })
        );
    }

    gal.append(canvas);

    //check active image
    if (!isActiveImg) {
        gal.find('img').first().addClass('active');
    }

    // compute if prev arrow should be shown
    if (0 < canvas.find('img.active').index()) {
        canvas.find('.arrow.prev').removeClass('hide');
    }

    // compute if next arrow should be shown
    if (canvas.find('.active').index() < canvas.find('img').length -1) {
        canvas.find('.arrow.next').removeClass('hide');
    }

    var controls = $('<div class="controls"></div>');
    var total = $('<span class="total"></span>');
    //total.html(gal.find('img.active').attr('alt'));
    total.html(ffbTranslator.translate('MSG_SELECT_TEASER_INFO'));

    controls.append(total);

    //if there are file, add list to parent
    if (!isView) {
        gal.append(controls);
    }

    container.append(gal);

    this.initSelect();
};

/**
 * Init select
 *
 * @public
 * @this TeaserSelect
 * @param {object} container
 */
TeaserSelect.prototype.initSelect = function() {

    var self = this;

    // on change
    this.select = new ffbDropdown(this.el.get(0), {
        'liHeight' : 25,
        'onSelect' : function(element, valueIndex, value) {
            
            var galP = self.el.parents('.row');

            // get current and new
            var active    = galP.find('img.active');
            var newActive = galP.find('img[data-value="' + value + '"]');

            // update active
            active.removeClass('active');
            newActive.addClass('active');

            // show/hide arrows
            galP.find('.arrow.prev').removeClass('hide');
            if (newActive.prev('img').length === 0) {
                galP.find('.arrow.prev').addClass('hide');
            }
            galP.find('.arrow.next').removeClass('hide');
            if (newActive.next('img').length === 0) {
                galP.find('.arrow.next').addClass('hide');
            }
        }
    });
};

/**
 * Render inplace editor
 *
 * @public
 * @this TeaserSelect
 */
TeaserSelect.prototype.renderInplace = function() {

    //render standard controls for edit area
    this.renderStandard();
    var parent = this.el.parents('.row.editable').find('.editable.value');
   
    this.renderGallery(parent);
};

/**
 * Render standard logic
 *
 * @public
 * @this TeaserSelect
 */
TeaserSelect.prototype.renderStandard = function() {

    this.renderGallery();
};
