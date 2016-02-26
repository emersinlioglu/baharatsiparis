"use strict";

/**
 * Element SimpleGallery init
 *
 * @class
 * @constructor
 * @this SimpleGallery
 */
var SimpleGallery = function(container, options) {

    var _               = this;
    var _container      = null;
    var _options        = {
        'files': '',
        'type': '' // [file|image]
    };

    /**
     * Renders files list
     * @returns {this}
     */
    _.renderFilesList = function() {

        //generate new list
        var ul = $('<ul class="fileupload-list"></ul>');
        $(_options.files).each(function(i, file) {

            //generate span and add delete request
            var delSpan = $('<span>')
                //.attr('data-url', _options.deleteFileUrl)
                .attr('data-id', file.id)
                .attr('data-name', file.name)
                .attr('title', ffbTranslator.translate('TTL_DELETE'))
                .addClass('fileupload-delete');

//            delSpan.on('click', function(e) {
//                _deleteFile(this);
//            });

            //create link to file
            var fLink = $('<a>')
                .attr('href', file.url)
                .attr('target', '_blank')
                .attr('title', file.name)
                .addClass('fileupload-file')
                .html(file.name);
            var li = $('<li></li>')
                //.append(delSpan)
                .append(fLink);
            ul.append(li);
        });

        //if there are file, add list to parent
        if (_options.files.length > 0) {
            _container.append(ul);
        }
    };

    _.renderGallery = function() {

        var gal         = $('<div class="fileupload-gallery"></div>');
        var canvas      = $('<div class="canvas"></div>');
        var isActiveImg = false;
        var images      = [];
        $(_options.files).each(function(i, file) {

            var img = $('<img>')
                .attr('src', file.urlGallery)
                .attr('alt', file.name);

            if (file.rank === 1) {

                img.addClass('active');
                isActiveImg = true;
                canvas.append(img);
            } else {
                images.push(img);
            }
        });

        //set active image first
        for (var i = 0; i < images.length; i++) {
            canvas.append(images[i]);
        }

        //if no images add empty
        if (_options.files.length === 0) {
            canvas.append('<div class="empty"><span>' + ffbTranslator.translate('LBL_LOCATION_NO_IMAGES') + '</span></div>');
        }

        //add arrows
        if (_options.files.length > 1) {

            canvas.append(
                $('<div class="arrow prev hide"></div>')
                .on('click', function() {

                    var galP = _container;

                    var active = galP.find('img.active');
                    if (active.first().prev('img').length > 0) {
                        active.removeClass('active');
                        active.prev('img').addClass('active');
                    }

                    //show/hide arrows
                    galP.find('.arrow.next').removeClass('hide');
                    if (active.prev('img').prev('img').length === 0) {
                        galP.find('.arrow.prev').addClass('hide');
                    }

                    return false;
                })
            );
            canvas.append(
                $('<div class="arrow next"></div>')
                .on('click', function() {

                    var galP = _container;

                    var active = galP.find('img.active');
                    if (active.first().next('img').length > 0) {
                        active.removeClass('active');
                        active.next('img').addClass('active');
                    }

                    //show/hide arrows
                    galP.find('.arrow.prev').removeClass('hide');
                    if (active.next('img').next('img').length === 0) {
                        galP.find('.arrow.next').addClass('hide');
                    }

                    return false;
                })
            );
        }

        gal.append(canvas);

        //check active image
        if (!isActiveImg) {
            gal.find('img').first().addClass('active');
        }

        var controls = $('<div class="controls"></div>');

        if (_options.files.length > 1) {
            gal.append(controls);
        }

        _container.append(gal);

    }

    /**
     * Renders the files by type
     *
     * @returns {this}
     */
    _.render = function() {

        if (_options.type == 'image') {

            _.renderGallery();

        } else if (_options.type == 'file') {

            _.renderFilesList();
        }
    }

    /**
     * Init SimpleGallery
     *
     * @this {SimpleGallery}
     * @param {Object} container
     */
    _.init = function(container, options) {

        // get select
        _container = $(container);

        // check if inited
        if (_container.hasClass('inited')) {
            return;
        }

        // get files
        _options = _container.data('files');

        // render gallery
        _.render();

        _container.addClass('inited');
    }

    _.init(container, options);
}
