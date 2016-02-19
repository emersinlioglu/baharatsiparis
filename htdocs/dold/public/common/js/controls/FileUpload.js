/*jshint -W117 */
"use strict";

/**
 * File upload control for direct upload.
 *
 * This allows for uploading a single file.
 *
 * @class
 * @param {string|select} element
 */
var FileUpload = function(element, callBack, deleteCallBack) {

    var _ = this;

    /**
     * On Upload callBack
     *
     * @type {function}
     */
    var _callBack = callBack !== undefined ? callBack:null;

    /**
     * File Upload object
     *
     * @var {ffbFileUpload} _fileUpload
     */
    var _fileUpload = null;

    /**
     * On Delete callBack
     *
     * @type {function}
     */
    var _deleteCallBack = deleteCallBack !== undefined ? deleteCallBack:null;

    /**
     * Uploads requests counter
     *
     * @var {integer} _uploads
     */
    var _uploads = 0;

    /**
     * Input element
     *
     * @var {object} _.el
     *
     */
    _.el = null;


    /**
     * Show link or preview for images
     *
     * @var {boolean} _.isPreview
     */
    _.isPreview = false;

    /**
     * Options from el value attribute
     *
     * @var {object} _.options
     */
    _.options = {
        'uploadFileUrl'      : null,
        'deleteFileUrl'      : null,
        'files'              : [],
        // {images, documents, excel, pdf}
        'type'               : null,
        'destination'        : null,
        'multiple'           : true,
        'tokenInput'         : 'token',
        'referenceTypeInput' : 'referenceType',
        'idInput'            : 'id',
        'destinationInput'   : 'destination',
        'translations'       : {
            'uploadButton' : 'PLH_FILE_UPLOAD'
        }
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
                _.options[key] = options[key];
            }
        }

        if (!_.options.destination) {
            _.options.destination = _.options.type;
        }

        //check is preview
        if (_.el.hasClass('preview')) {
            _.isPreview = true;
        }

        //create view and edit html
        _render();
    };

    /**
     * Render standard logic
     *
     */
    var _renderStandard = function() {

        _renderInputButton();

        _initFileUpload(_.el.parents('.fileupload-wrapper'));

        _renderFilesList();
    };

    /**
     * Add html with input button to open upload lighbox
     *
     * @private
     * @this FilesUpload
     */
    var _renderInputButton = function() {

        var cnt = _.el.parents('.fileupload-wrapper');
        var button = $('<div>')
            .addClass('fileupload-button');
        var inp = $('<input value="' + ffbTranslator.translate(_.options.translations.uploadButton) + '" />')
            .attr('type', 'text')
            .attr('name', _.el.attr('name') + '_control')
            .attr('readonly', 'readonly')
            .addClass('fileupload-selectfile');
        inp.on('mousedown', function() {
            return false;
        });
        inp.on('selectstart', function() {
            return false;
        });
        //inp.on('click', _openUploadForm);

        var file = $('<input>')
            .attr('type', 'file')
            .attr('name', 'upload')
            .addClass('fileupload-browse');
        button.append(inp);
        button.append(file);

        var area = $('<div>')
            .addClass('drop-area');
        var iframe = $('<iframe>')
            .attr('name', _.el.attr('name') + '_extUploadFormIframe');

        cnt.append(button);
        cnt.append(area);
        cnt.append(iframe);
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
        _.el.replaceWith(cnt);
        cnt.append(_.el);

        //check view type
        _renderStandard();
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
                    var delAjax = new ffbAjax();
                    delAjax.call(
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

                                _renderFilesList();

                                ffbLightbox.close();

                                //call callBack if exist
                                if (_deleteCallBack) {
                                    _deleteCallBack(_, result);
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
                                'referenceId'   : span.parents('form').find('input[name="' + _.options.idInput + '"]').val(),
                                'token'         : span.parents('form').find('input[name="' + _.options.tokenInput + '"]').val(),
                                'destination'   : _.options.destination
                            }
                        }
                    );
                }
            },
            'cancelAction' : {
                'caption'  : ffbTranslator.translate('BTN_CLOSE')
            }
        });
    };

    /**
     * Render files list, init delete links
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _renderFilesList = function(container) {

        //get parent and remove previously list
        var parent = _.el.parents('.fileupload-wrapper');
        parent.find('.fileupload-list')
            .remove();
        container = parent;

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

            //show file name or image
            var linkContent = file.name;
            if (_.isPreview === true && _.options.type === 'images') {
                linkContent = $('<img>')
                    .attr('src', file.url)
                    .attr('alt', file.name)
                    .addClass('fileupload-image-preview');
            }

            //create link to file
            var fLink = $('<a>')
                .attr('href', file.url)
                .attr('target', '_blank')
                .attr('title', file.name)
                .addClass('fileupload-file')
                .html(linkContent);
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
     * Init upload form
     *
     * @private
     * @this FilesUpload
     * @param {object} container
     */
    var _initFileUpload = function(container) {

        var parentForm = container.parents('form').first();
        var formValues = ffbForm.getValues(parentForm);

        var values = {
            'uploadType'  : _.options.type,
            'destination' : _.options.destination
        };

        //get values from form
        if (formValues[_.options.tokenInput] !== undefined) {
            values.token = formValues[_.options.tokenInput];
        }
        if (formValues[_.options.referenceTypeInput] !== undefined) {
            values.referenceType = formValues[_.options.referenceTypeInput];
        }
        if (formValues[_.options.idInput] !== undefined) {
            values.referenceId = formValues[_.options.idInput];
        }
        if (formValues[_.options.destinationInput] !== undefined) {
            values.destination = formValues[_.options.destinationInput];
        }

        //Init file upload
        _fileUpload = new ffbFileUpload({
            'area'       : container.find('.drop-area'),
            'button'     : container.find('.fileupload-browse'),
            'iframe'     : container.find('iframe'),
            'progress'   : null,
            'form'       : parentForm,
            'autoUpload' : true,
            'onFileSelect' : function(fl) {

                if (_.options.multiple === false && _.options.files.length) {

                    ffbLightbox.showInfo({
                        'title' : ffbTranslator.translate('TTL_FILE_LOADING'),
                        'text'  : '<p>' + ffbTranslator.translate('MSG_ONLY_ONE_FILE_ALLOWED') + '</p>'
                    });
                    return false;
                }

//                if (fl && fl.length >= 1) {
//                    container.find('input[name="filepath"]').val(fl[0].name);
//                }
                _fileUpload.setFormData(values);
            },
            'uploadUrl'  : function() {

                return _.options.uploadFileUrl;
            },
            'onUploadStart' : function(fileInfo) {

                _uploads++;
                ffbLightbox.showProgress({
                    'title' : ffbTranslator.translate('TTL_FILE_LOADING'),
                    'text'  : '<p>' + ffbTranslator.translate('MSG_FILE_LOADING') + '</p>'
                });

                container.find('.fileupload-browse').addClass('uploading');
            },
            'onProgress' : function(fileName, progress) {

//                $('.upload-progress .value').css('width', parseInt(progress) + '%');
            },
            'onSuccess' : function(json, options) {

                _uploads--;

                container.find('.fileupload-browse').removeClass('uploading');

                //show 100%, remove cancel link
                if (json && json.state === 'ok') {

                    //close progress
                    if (_uploads <= 0) {
                        ffbLightbox.close();
                    }

                    //set file to element if in answer only
                    if (   typeof json.file !== 'undefined'
                        && json.file
                    ) {
                        _.options.files.push(json.file);

                        _renderFilesList();
                    }

                    //call callBack if exist
                    if (_callBack) {
                        _callBack(_, json);
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

                container.find('.fileupload-browse').removeClass('uploading');

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
    };

    _.init(element);
};
