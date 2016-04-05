/*jshint -W117 */
"use strict";

/**
 * File upload control + lightbox + list
 *
 * This allows for uploading a multiple files.
 *
 * @class
 * @param {string|select} element
 */
var FilesUpload = function(element, onRender, onChange) {

    var _ = this;

    /**
     * File Upload object
     *
     * @var {ffbFileUpload} _fileUpload
     */
    var _fileUpload = null;

    /**
     * Uploads requests counter
     *
     * @var {integer} _uploads
     */
    var _uploads = 0;

    /**
     * Ajax request
     *
     * @type {object}
     */
    var _request = null;

    /**
     * Input element
     *
     * @var {object} _.el
     *
     */
    _.el      = null;

    /**
     * ffbLightbox
     *
     * @var {object} _.lb
     *
     */
    _.lb      = null;

    /**
     * Options from el value attribute
     *
     * @var {object} _.options
     */
    _.options = {
        'uploadFormUrl'      : null,
        'uploadFileUrl'      : null,
        'deleteFileUrl'      : null,
        'updateFileUrl'      : null,
        'onRender'           : onRender !== undefined ? onRender:null,
        'onChange'           : onChange !== undefined ? onChange:null,
        'withGallery'        : true,
        'files'              : [],
        // {images, documents, excel, pdf}
        'type'               : null,
        'destination'        : null,
        // {list, table} view type of uploaded files
        'viewType'           : 'table',
        'tokenInput'         : 'token',
        'referenceTypeInput' : 'referenceType',
        'idInput'            : 'id',
        'destinationInput'   : 'destination',
        'confirmRemove'      : true
    };

    /**
     * Init file upload
     *
     * @public
     * @this FilesUpload
     * @param {object} element
     */
    _.init = function(element) {

        //check element
        _.el = $(element);
        if (_.el.length === 0) {
            return null;
        }

        //get options parse them
        var options = ajax.isJSON(_.el.val());
        if (!options) {
            return null;
        }



        //fill options
        if (options && typeof options === 'object') {
            for (var key in options) {
                if (!options.hasOwnProperty(key)) continue;
                _.options[key] = options[key];
            }
        }

        if (!_.options.destination) {
            _.options.destination = _.options.type;
        }

        //create view and edit html
        _render();
    };

    // sets files for FilesUpload control and redraws the list
    /**
     * @public
     * @this FilesUpload
     * @param {array} files
     * @returns {undefined}
     */
    _.setFiles = function(files) {
        _.options.files = files;
        _redrawFilesList();
    }

    /**
     * Render inplace editor
     *
     * @private
     * @this FilesUpload
     */
    var _renderInplace = function() {

        //render standard controls for edit area
        _renderStandard();
        var parent = _.el.parents('.row.editable').find('.editable.value');

        //render view controls for inplace editor
        if (_.options.type === 'images') {

            _renderGallery(parent);
        }
        if (_.options.type === 'documents' || _.options.type === 'contracts' ) {

            _renderFilesList(parent);
        }

        if (_.options.onRender) {
            _.options.onRender(_);
        }
    };

    /**
     * Render standard logic
     *
     */
    var _renderStandard = function() {

        if (_.options.type === 'images') {

            _renderGallery();

            //_renderFilesList();
        }
        if (_.options.type === 'documents' || _.options.type === 'contracts' ) {

            _renderInputButton();

            _renderFilesList();
        }

        if (_.options.onRender) {
            _.options.onRender(_);
        }
    };

    /**
     * Render gallery html and init listeners
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _renderGallery = function(container) {

        var isView = false;
        if (container) {
            isView = true;
            container.html('');
        } else {
            //get parent and remove previously list
            var parent = _.el.parents('.fileupload-wrapper');
            parent.find('.fileupload-gallery')
                .remove();
            container = parent;
        }

        var gal         = $('<div class="fileupload-gallery"></div>');
        var canvas      = $('<div class="canvas"></div>');
        var isActiveImg = false;
        var images      = [];
        $(_.options.files).each(function(i, file) {

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
        if (_.options.files.length === 0) {
            canvas.append('<div class="empty"><span>' + ffbTranslator.translate('LBL_LOCATION_NO_IMAGES') + '</span></div>');
        }

        //add arrows
        if (_.options.files.length > 1) {

            canvas.append(
                $('<div class="arrow prev hide"></div>')
                .on('click', function() {

                    var galP = _.el.parents('.row');

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

                    var galP = _.el.parents('.row');

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

        var totalText = ffbTranslator.translate('LBL_TOTAL_IMAGES');
        var isSprintf = totalText.search(/%s/gm);
        if (isSprintf >= 0) {
            totalText = totalText.replace(/%s/m, _.options.files.length);
        } else {
            totalText = _.options.files.length + ' ' + totalText;
        }
        var total = $('<span class="total"></span>');
            total.html(totalText);
        var button = $('<div class="button white next"></div>');
            button.html(ffbTranslator.translate('BTN_GALLERY_EDIT'));
        var infoText = $('</p>').addClass('info')
                .html(ffbTranslator.translate('MSG_OPTIMAL_IMAGE_SIZE') + '<br />'
                    + ffbTranslator.translate('MSG_ALLOWED_IMAGE_FORMATS'));

        button.on('click', function(e) {

            _openUploadForm();
            return false;
        });

        controls.append(total)
            .append(button)
            .append(infoText);

        //if there are file, add list to parent
        if (!isView) {
            gal.append(controls);
        }

        container.append(gal);
    };

    /**
     * Add html with input button to open upload lighbox
     *
     * @private
     * @this FilesUpload
     */
    var _renderInputButton = function() {

        var cnt = _.el.parents('.fileupload-wrapper');
        var inp = $('<input>')
            .attr('type', 'text')
            .attr('name', _.el.attr('name') + '_control')
            .attr('readonly', 'readonly')
            .addClass('fileupload-selectfile')
            .val(ffbTranslator.translate('PLH_FILE_UPLOAD'));

        inp.on('mousedown', function() {
            return false;
        });
        inp.on('selectstart', function() {
            return false;
        });
        inp.on('click', _openUploadForm);

        cnt.append(inp);
    };

    /**
     * Render control elements, based on view type
     *
     * @private
     * @this FilesUpload
     */
    var _render = function() {

        //create wrapper
        var cnt = $('<div class="fileupload-wrapper"></div>');

        if (!_.options.withGallery) {
            cnt.addClass('no-gallery');
        }

        _.el.replaceWith(cnt);
        cnt.append(_.el);


        //check view type
        var parent = _.el.parents('.row');
        if (parent.hasClass('editable')) {
            _renderInplace();
        } else {
            _renderStandard();
        }
    };

    var _callAjaxDelete = function(span, closeLightbox) {

        if (_request) {
            _request.abort();
            _request = null;
        }

        var delAjax = new ffbAjax();
        _request = delAjax.call(
            _.options.deleteFileUrl,
            function(data) {

                //parse result
                var result = ajax.isJSON(data);

                //if json and ok
                if (result && result.state === 'ok') {

                    //remove deleted file from list
                    var newFiles = [];
                    $(_.options.files).each(function(k, f) {

                        if (f.id && f.id !== parseInt(span.attr('data-id'))) {
                            newFiles.push(f);
                        } else if (!f.id && f.name !== span.attr('data-name')) {
                            newFiles.push(f);
                        }
                    });

                    //save result and close lb
                    _.options.files = newFiles;

                    //redraw list
                    _redrawFilesList();

                    //redraw uploaded list
                    _createUploadedListHTML(span.parents('.lightbox'));

                    if (closeLightbox) {
                        ffbLightbox.close();
                    }

                    // on change callback
                    if (_.options.onChange) {
                        _.options.onChange(_);
                    }

                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_DELETE_FILE'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });
                }
            },
            {
                'accepts' : 'json',
                'type'   : 'post',
                'data'   : {
                    'fileId'        : span.attr('data-id'),
                    'fileName'      : span.attr('data-name'),
                    'fileTempname'  : span.attr('data-tempname'),
                    'referenceId'   : span.parents('form').find('input[name="' + _.options.idInput + '"]').val(),
                    'token'         : span.parents('form').find('input[name="' + _.options.tokenInput + '"]').val(),
                    'destination'   : _.options.destination
                }
            }
        );

    };

    /**
     * Delete file request
     *
     * @private
     * @this FilesUpload
     * @param {object] element
     */
    var _deleteFile = function(element) {

        //confirm deleting
        var span = $(element);

        if (_.options.confirmRemove) {

            // DERTRA-757
            // remove confirmation layer
            ffbLightbox.showModal({
                'title'    : ffbTranslator.translate('TTL_CONFIRM_FILE_DELETE'),
                'text'     : '<p>' + span.attr('data-name') + '</p>',
                'okAction'    : {
                    'caption'  : ffbTranslator.translate('BTN_DELETE'),
                    'callBack' : function() {

                        ffbLightbox.showProgress({
                            'title' : ffbTranslator.translate('TTL_FILE_DELETE'),
                            'text'  : '<p>' + ffbTranslator.translate('MSG_FILE_DELETING') + '</p>'
                        });

                        //call delete request
                        _callAjaxDelete(span, true);
                    }
                },
                'hideCancelButton' : true
            });
        } else {

            //call delete request
            _callAjaxDelete(span, false);
        }
    };

    /**
     * Update file data (decription, rank)
     *
     * @private
     * @this FilesUpload
     * @param {object} element
     * @param {string} type
     */
    var _updateFile = function(element, type) {

        element = $(element);

        ffbLightbox.showProgress({
            'title' : ffbTranslator.translate('TTL_FILE_UPDATE'),
            'text'  : '<p>' + ffbTranslator.translate('MSG_FILE_UPDATING') + '</p>'
        });

        //call update request
        var updAjax = new ffbAjax();
        updAjax.call(
            _.options.updateFileUrl,
            function(data) {

                //parse result
                var result = ajax.isJSON(data);

                //if jsona and ok
                if (result && result.state === 'ok') {

                    //update rank in files array
                    $(_.options.files).each(function(k, f) {

                        if (f.id && f.id === parseInt(element.attr('data-id'))) {

                            // get by id
                            switch (type) {
                                case 'rank':
                                    f[type] = parseInt(element.val());
                                    break;
                                case 'description':
                                    f[type] = element.val();
                                    break;
                            }

                        } else if (!f.id && f.name === element.attr('data-name')) {

                            // get by name
                            switch (type) {
                                case 'rank':
                                    f[type] = parseInt(element.val());
                                    break;
                                case 'description':
                                    f[type] = element.val();
                                    break;
                            }

                        } else {

                            // update other
                            switch (type) {
                                case 'rank':
                                    f[type] = 0;
                                    break;
                                case 'description':
                                    break;
                            }

                        }
                    });

                    //redraw list
                    _redrawFilesList();

                    //redraw uploaded list
                    _createUploadedListHTML(element.parents('.lightbox'));

                    //show success message
                    ffbLightbox.close();
//                    ffbLightbox.showInfo({
//                        'title'     : ffbTranslator.translate('TTL_FILE_UPDATED'),
//                        'className' : 'success',
//                        'text'      : ffbTranslator.translate('MSG_FILE_UPDATED')
//                    });

                    // on change callback
                    if (_.options.onChange) {
                        _.options.onChange(_);
                    }
                } else {

                    var errorData = ajax.parseError(data);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_UPDATE_FILE'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });
                }
            },
            {
                'accepts' : 'json',
                'type'   : 'post',
                'data'   : {
                    'fileId'        : element.attr('data-id'),
                    'fileName'      : element.attr('data-name'),
                    'referenceId'   : element.parents('form').find('input[name="' + _.options.idInput + '"]').val(),
                    'referenceType' : element.parents('form').find('input[name="' + _.options.referenceTypeInput + '"]').val(),
                    'token'         : element.parents('form').find('input[name="' + _.options.tokenInput + '"]').val(),
                    'name'          : type,
                    'value'         : element.val(),
                    'destination'   : _.options.destination
                }
            }
        );
    };

    /**
     * Redraw files list, and redraw inplace view if needed
     *
     * @private
     * @this FilesUpload
     */
    var _redrawFilesList = function() {

        var parent = _.el.parents('.row.editable').find('.editable.value');
        if (_.options.type === 'images') {

            //redraw gallery and update files count
            _renderGallery();
            if (parent.length > 0 ) {
                _renderGallery(parent);
            }
        }
        if (_.options.type === 'documents' || _.options.type === 'contracts') {

            //render files list
            _renderFilesList();
            if (parent.length > 0 ) {
                _renderFilesList(parent);
            }
        }

        if (_.options.onRender) {
            _.options.onRender(_);
        }
    };

    /**
     * Render files list, init delete links
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _renderFilesList = function(container) {

        if (container) {
            container.html('');
        } else {
            //get parent and remove previously list
            var parent = _.el.parents('.fileupload-wrapper');
            parent.find('.fileupload-list')
                .remove();
            container = parent;
        }
        //generate new list
        var ul = $('<ul class="fileupload-list"></ul>');
        $(_.options.files).each(function(i, file) {

            //generate span and add delete request
            var delSpan = $('<span>')
                .attr('data-url', _.options.deleteFileUrl)
                .attr('data-id', file.id)
                .attr('data-name', file.name)
                .attr('title', ffbTranslator.translate('TTL_DELETE'))
                .addClass('fileupload-delete');

            delSpan.on('click', function(e) {
                _deleteFile(this);
            });

            //create link to file
            var fLink = $('<a>')
                .attr('href', file.url)
                .attr('target', '_blank')
                .attr('title', file.name)
                .addClass('fileupload-file')
                .html(file.name);
            var li = $('<li></li>')
                .append(delSpan)
                .append(fLink);
            ul.append(li);
        });

        //if there are file, add list to parent
        if (_.options.files.length > 0) {
            container.append(ul);
        }
    };

    /**
     * Create files list html in upload lightbox
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _createUploadedListHTML = function(container) {

        //cleare files list
        container.find('.files-list')
            .addClass(_.options.type)
            .addClass('view-type-' + _.options.viewType)
            .html('');

        // init (fake) save button
        // save button only clicks on close button
        container.find('.row.buttons .button.ok').on('click', function() {
            container.find('.menu .close.button').trigger('click');
        });

        //check files list
        if (_.options.files.length === 0) {
            return;
        }

        // create uploaded files html
        switch(_.options.viewType) {
            case 'list':
                _createUploadedListHTMLAsList(container);
                break;
            case 'table':
                _createUploadedListHTMLAsTable(container);
                break;
            default:
                _createUploadedListHTMLAsTable(container);
                break;
        }

        // init del links
        container.find('.files-list .link-delete').each(function(i, lnk) {
            $(lnk).on('click', function() {
                _deleteFile(this);
                return false;
            });
        });

        // init rank links
        container.find('.files-list input[type="radio"]').each(function(i, lnk) {
            $(lnk).on('click', function() {
                _updateFile(this, 'rank');
            });
        });

        // init rank links
        container.find('.inplace.description').each(function(i, span) {

            var defValue;

            $(span).on('click', function() {
                $(this).addClass('hide');
                defValue = $(this).next('.edit').find('textarea').val();
                $(this).next('.edit')
                    .removeClass('hide');
                $(this).next('.edit').find('textarea').focus();
            });

            $(span).next('.edit').find('textarea').on('blur', function(e) {
                $(this).parent().addClass('hide');
                $(this).parent().prev('.inplace').removeClass('hide');

                var target = $(e.originalEvent.explicitOriginalTarget);
                if (target.hasClass('cancel')) {
                    $(this).val(defValue);
                } else {

                }

                if ($(this).val() !== defValue) {
                    _updateFile(this, 'description');
                }
            });
        });

        ffbLightbox.updatePosition(container.attr('id'));
    };

    /**
     * Create files list html as list in upload lightbox
     *
     * @private
     * @this {FilesUpload}
     * @param {JQuery} container
     */
    var _createUploadedListHTMLAsList = function(container) {

        var ul = $('<ul>')
                .addClass('fileupload-list');

        //draw files
        $(_.options.files).each(function(i, file) {

            //for images
//            if (_.options.type === 'images') {
//
//                // TODO: implement the list view for images
//
//            }

            //for documents
            if (_.options.type === 'documents' || _.options.type === 'contracts' ) {

                var li = $('<li>');
                // link delete
                li.append($('<a>')
                        .addClass('link-delete')
                        .attr('href', '#')
                        .attr('title', ffbTranslator.translate('BTN_DELETE'))
                        .attr('data-id', file.id)
                        .attr('data-name', file.name));

                // link title
                var name = ffbTranslator.translate('LBL_UPLOAD_PREFIX') + ' ' + (i + 1) + ': ' + file.name;
                li.append($('<a>')
                        .attr('href', file.url)
                        .attr('title', name)
                        .attr('target', "_blank")
                        .addClass('link-title')
                        .text(name));

                ul.append(li);
            }
        });

        var columnLeft  = $('<div>').addClass("column left");
        var columnRight = $('<div>').addClass("column right");
        columnLeft.append(ul);

        container.find('.files-list')
                .append(columnLeft)
                .append(columnRight);
    };

    /**
     * Create files list html as table in upload lightbox
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _createUploadedListHTMLAsTable = function(container) {

        var table   = $('<table>')
            .addClass('table-default table-fileslist');
        var headers = $('<thead>');
        var body    = $('<tbody>');
        var tr      = $('<tr>');

        if (_.options.type === 'images') {

            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILEPREVIEW') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_IMAGENAME') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_IMAGESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_STARTIMAGE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DELETE_FILE') + '</span></th>');
            headers.append(tr);
        }
        if (_.options.type === 'documents' || _.options.type === 'contracts') {

            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILENAME') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DESCRIPTION') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DELETE_FILE') + '</span></th>');
            headers.append(tr);
        }
        table.append(headers);

        //draw files
        $(_.options.files).each(function(i, file) {

            //for images
            if (_.options.type === 'images') {

                var img = $('<tr class="image">');
                img.append('<td><div class="img-wrapper"><img src="' + file.urlView + '" alt="' + file.name + '" /></div></td>');
                img.append('<td><a href="' + file.url + '" title="' + file.name + '" target="_blank">' + file.name + '</a></td>');
                img.append('<td>' + file.size + '</td>');
                img.append('<td>' + file.resolution + ' ' + ffbTranslator.translate('LBL_PIXEL') + ' </td>');
                var rankInput = $('<input name="rank" type="radio" value="1" />')
                    .attr('data-id', file.id)
                    .attr('data-name', file.name);
                if (file.rank === 1) {
                    rankInput.attr('checked', 'checked');
                }
                var rankTd = $('<td>')
                    .append(rankInput);
                img.append(rankTd);
                img.append('<td><a data-id="' + file.id + '" data-name="' + file.name + '" class="link-delete" href="#" title="' + ffbTranslator.translate('BTN_DELETE') + '"></a></td>');
                body.append(img);

            }

            //for documents
            if (_.options.type === 'documents' || _.options.type === 'contracts' ) {

                var doc = $('<tr class="document">');
                doc.append('<td><a href="' + file.url + '" title="' + file.name + '" target="_blank">' + file.name + '</a></td>');
                var descSpan = $('<span class="inplace description"></span>');
                var descTxt = $('<textarea name="description"></textarea>')
                    .attr('data-id', file.id)
                    .attr('data-name', file.name);
                if (file.description) {
                    descTxt.html(file.description);
                    descSpan.html(file.description.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2'));
                }
                var descTd = $('<td>')
                    .append(descSpan)
                    .append($('<span class="editable edit hide">')
                        .append(descTxt)
                        .append($('<span class="editable-button ok">' + ffbTranslator.translate('BTN_OK') + '</span>'))
                        .append($('<span class="editable-button cancel">' + ffbTranslator.translate('BTN_CLOSE') + '</span>'))
                    );
                doc.append(descTd);
                doc.append('<td>' + file.size + '</td>');
                doc.append('<td><a data-id="' + file.id + '" data-name="' + file.name + '" class="link-delete" href="#" title="' + ffbTranslator.translate('BTN_DELETE') + '"></a></td>');
                body.append(doc);
            }
        });
        table.append(body);
        container.find('.files-list').append(table);
    };

    /**
     * Init upload form
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _initUploadForm = function(container) {

        var values = ffbForm.getValues(container.find('form'));

        //Init file upload
        _fileUpload = new ffbFileUpload({
            'area'       : container.find('.drop-area'),
            'button'     : container.find('.button.file input'),
            'iframe'     : container.find('iframe'),
            'progress'   : null,
            'form'       : container.find('form'),
            'autoUpload' : true,
            'onFileSelect' : function(fl) {

                if (fl && fl.length >= 1) {
                    container.find('input[name="filepath"]').val(fl[0].name);
                }
                _fileUpload.setFormData(values);
            },
            'uploadUrl'  : function() {

                // default upload url
                var url = container.find('form').attr('action');

                // custom upload url
                if (typeof _.options.uploadFileUrl !== 'undefined' && 0 < _.options.uploadFileUrl.length) {
                    url = _.options.uploadFileUrl;
                }

                return url;
            },
            'onUploadStart' : function(fileInfo) {

                _uploads++;

                if (_.options.type === 'images') {
                    _initImageUploadProgress(fileInfo, container);
                }
                if (_.options.type === 'documents' || _.options.type === 'contracts') {
                    _initDocumentUploadProgress(fileInfo, container);
                }
            },
            'onProgress' : function(fileName, progress) {

//                $('.upload-progress .value').css('width', parseInt(progress) + '%');
            },
            'onSuccess' : function(json, options) {

                _uploads--;

                //show 100%, remove cancel link
                if (json && json.state === 'ok') {

                    if (_.options.type === 'images') {
                        _onSuccessImageUpload(json, options, container);
                    }
                    if (_.options.type === 'documents' || _.options.type === 'contracts') {
                        _onSuccessDocumentUpload(json, options, container);
                    }

                } else {

                    var errorData = ajax.parseError(json);
                    ffbLightbox.showInfo({
                        'title'     : ffbTranslator.translate('TTL_FILE_LOADING'),
                        'className' : 'error',
                        'text'      : errorData.message
                    });
                }
            },
            'onError' : function(xhr, options) {

                _uploads--;

                var errorData = ajax.parseError(xhr.responseText);
                ffbLightbox.showInfo({
                    'title'     : ffbTranslator.translate('TTL_FILE_LOADING'),
                    'className' : 'error',
                    'text'      : errorData.message
                });

            }
        });

        //set is fileapi supported
        if (_fileUpload.isIE && !_fileUpload.isNewIE) {
            container.find('.drop-area').addClass('hide');

            container.find('.upload-info p').addClass('hide');
            container.find('.upload-info p.old').removeClass('hide');
        }

        _createUploadedListHTML(container);
    };

    /**
     * Show documents upload progress
     *
     * @private
     * @this FilesUpload
     * @param {array} fileInfo
     * @param {object} container
     */
    var _initDocumentUploadProgress = function(fileInfo, container) {

        ffbLightbox.showProgress({
            'title' : ffbTranslator.translate('TTL_FILE_LOADING'),
            'text'  : '<p>' + ffbTranslator.translate('MSG_FILE_LOADING') + '</p>'
        });
    };

    /**
     * Show images upload progress
     *
     * @private
     * @this FilesUpload
     * @param {object} fileInfo
     * @param {object} container
     */
    var _initImageUploadProgress = function(fileInfo, container) {

        ffbLightbox.showProgress({
            'title' : ffbTranslator.translate('TTL_FILE_LOADING'),
            'text'  : '<p>' + ffbTranslator.translate('MSG_FILE_LOADING') + '</p>'
        });

//        //prepare filesize if exist
//        var fileSize = '';
//        if (fileInfo.size > 0) {
//            fileSize = ' (' + parseInt(fileInfo.size / 1000) + ' Kb)';
//        }
//
//        var div = $('<div class="file-progress"></div>')
//            .append('<span>' + fileInfo.name  + fileSize + '</span>')
//            .append('<div class="progress-bar"><div class="progress-value"></div></div>');
//
//        container.find('.upload-progress')
//            .append(div);

    };

    /**
     * Callback for success image upload
     *
     * @private
     * @this FilesUpload
     * @param {object} json
     * @param {object} Uploader options
     * @param {object} Dom contaniner
     */
    var _onSuccessImageUpload = function(json, options, container) {

        //close progress
        if (_uploads === 0) {
            ffbLightbox.close();
        }

        _.options.files.push(json.file);
        _createUploadedListHTML(container);

        // on change callback
        if (_.options.onChange) {
            _.options.onChange(_);
        }
    };

    /**
     * Callback for success image upload
     *
     * @private
     * @this FilesUpload
     * @param {object} json
     * @param {object} Uploader options
     * @param {object} Dom contaniner
     */
    var _onSuccessDocumentUpload = function(json, options, container) {

        //close progress
        if (_uploads === 0) {
            ffbLightbox.close();
        }

        _.options.files.push(json.file);
        _createUploadedListHTML(container);

        // on change callback
        if (_.options.onChange) {
            _.options.onChange(_);
        }
    };

    /**
     * Open upload lightbox
     *
     * @private
     * @this FilesUpload
     */
    var _openUploadForm = function() {

        //check upload url
        if (!_.options.uploadFormUrl) {
            return;
        }

        //prepare data
        var data = {
            'referenceId'   : _.el.parents('form').find('input[name="' + _.options.idInput + '"]').val(),
            'referenceType' : _.el.parents('form').find('input[name="' + _.options.referenceTypeInput + '"]').val(),
            'token'         : _.el.parents('form').find('input[name="' + _.options.tokenInput + '"]').val(),
            'destination'   : _.options.destination,
            'uploadType'    : _.options.type
        };

        //open lightbox and init upload form
        ffbLightbox.showAjax(
            _.options.uploadFormUrl,
            {
                'className' : 'modal upload ' + _.options.type,
                'title'     : ffbTranslator.translate('TTL_ADD_FILES'),
                'method'    : 'post',
                'data'      : data,
                'callBack'  : function(lb) {

                    // set lb for callback
                    _.lb = lb;

                    // on change callback
                    if (_.options.onChange) {
                        _.options.onChange(_);
                    }

                    _initUploadForm(_.lb);
                },
                'onClose'   : function(lbId, fadeId) {

                    ffbLightbox.remove(lbId);
                    _redrawFilesList();
                }
            }
        );
    };

    _.init(element);
};
