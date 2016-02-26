/*jshint -W117 */
"use strict";

/**
 * File upload control for wysiwyg editor modal window
 *
 * @class
 * @param {string|select} element
 */
var EditorFileUpload = function(element, options, callBack, deleteCallBack) {

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
     * Options from el value attribute
     *
     * @var {object} _.options
     */
    _.options = {
        'uploadFileUrl'      : null,
        'deleteFileUrl'      : null,
        'files'              : [],
        'type'               : null, //images,documents
        'token'              : '',
        'destination'        : null,
        'referenceId'        : null,
        'referenceType'      : null,
        'multiple'           : true,
        'selectType'         : 'file' //image
    };

    /**
     * Init file upload
     *
     * @public
     * @this EditorFileUpload
     * @param {object} element
     */
    _.init = function(element, options) {

        //check element
        _.el = $(element);
        if (_.el.length === 0) {
            return null;
        }

        //fill options
        if (options && typeof options === 'object') {
            for (var key in options) {
                _.options[key] = options[key];
            }
        }

        //create view and edit html
        _render();
    };

    /**
     * Render control elements, based on view type
     *
     * @private
     * @this EditorFileUpload
     */
    var _render = function() {

        var form = _.el.parents('.form-default');
        form.addClass('editor-upload');

        _initFileUpload(form);

        _renderFilesList();
    };

    /**
     * Delete file request
     *
     * @private
     * @this EditorFileUpload
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

                            //if jsona and ok
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

                                //call callBack if exist
                                if (_deleteCallBack) {
                                    _deleteCallBack(_, result);
                                }

                                ffbLightbox.close();

                                ffbLightbox.showInfo({
                                    'title'     : ffbTranslator.translate('TTL_FILE_DELETED'),
                                    'className' : 'success',
                                    'text'      : ffbTranslator.translate('MSG_FILE_DELETED')
                                });

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
                                'referenceId'   : _.options.referenceId,
                                'token'         : _.options.token,
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
     * @this EditorFileUpload
     * @param {object} form
     */
    var _renderFilesList = function(form) {

        if (form === undefined) {
            form = _.el.parents('.form-default');
        }

        //cleare files list
        form.find('.files-list')
            .addClass(_.options.type)
            .html('');

        //check files list
        if (_.options.files.length === 0) {
            return;
        }

        var table   = $('<table>').addClass('table-default table-fileslist');
        var headers = $('<thead>');
        var body    = $('<tbody>');
        var tr      = $('<tr>');

        if (_.options.selectType === 'image') {

            tr.append('<th><span class="title">&nbsp;</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILEPREVIEW') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_IMAGENAME') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_IMAGESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DELETE_FILE') + '</span></th>');
            headers.append(tr);
        }
        if (_.options.selectType === 'file') {

            tr.append('<th><span class="title">&nbsp;</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILENAME') + '</span></th>');
            //tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DESCRIPTION') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_FILESIZE') + '</span></th>');
            tr.append('<th><span class="title">' + ffbTranslator.translate('TTL_DELETE_FILE') + '</span></th>');
            headers.append(tr);

        }
        table.append(headers);

        //draw files
        $(_.options.files).each(function(i, file) {

            var selectInput = $('<input name="select" type="radio" value="1" />')
                .attr('data-id', file.id)
                .attr('data-name', file.name)
                .attr('data-url', file.url);
            var selectTd = $('<td>')
                .append(selectInput);

            //for images
            if (_.options.selectType === 'image' && file.resolution !== 'x') {

                var img = $('<tr class="image">');                
                img.append(selectTd);
                img.append('<td><img src="' + file.urlView + '" alt="' + file.name + '" /></td>');
                img.append('<td><a href="' + file.url + '" title="' + file.name + '" target="_blank">' + file.name + '</a></td>');
                img.append('<td>' + file.size + '</td>');
                img.append('<td>' + file.resolution + ' ' + ffbTranslator.translate('LBL_PIXEL') + ' </td>');
                img.append('<td><a data-id="' + file.id + '" data-name="' + file.name + '" class="link-delete" href="#" title="' + ffbTranslator.translate('BTN_DELETE') + '"></a></td>');
                body.append(img);
            }

            //for documents
            if (_.options.selectType === 'file') {

                var doc = $('<tr class="document">');                
                doc.append(selectTd);
                doc.append('<td><a href="' + file.url + '" title="' + file.name + '" target="_blank">' + file.name + '</a></td>');
//                var descSpan = $('<span class="inplace description"></span>');
//                var descTxt = $('<textarea name="description"></textarea>')
//                    .attr('data-id', file.id)
//                    .attr('data-name', file.name);
//                if (file.description) {
//                    descTxt.html(file.description);
//                    descSpan.html(file.description.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2'));
//                }
//                var descTd = $('<td>')
//                    .append(descSpan)
//                    .append($('<span class="editable edit hide">')
//                        .append(descTxt)
//                        .append($('<span class="editable-button ok">' + ffbTranslator.translate('BTN_OK') + '</span>'))
//                        .append($('<span class="editable-button cancel">' + ffbTranslator.translate('BTN_CLOSE') + '</span>'))
//                    );
//                doc.append(descTd);
                doc.append('<td>' + file.size + '</td>');
                doc.append('<td><a data-id="' + file.id + '" data-name="' + file.name + '" class="link-delete" href="#" title="' + ffbTranslator.translate('BTN_DELETE') + '"></a></td>');
                body.append(doc);
            }
        });
        table.append(body);
        form.find('.files-list').append(table);

        // init del links
        form.find('.files-list .link-delete').each(function(i, lnk) {
            $(lnk).on('click', function() {
                _deleteFile(this);
                return false;
            });
        });

        // init rank links
        form.find('.inplace.description').each(function(i, span) {

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

//                if ($(this).val() !== defValue) {
//                    _updateFile(this, 'description');
//                }
            });
        });
    };

    /**
     * Init upload form
     *
     * @private
     * @this EditorFileUpload
     * @param {object} form
     */
    var _initFileUpload = function(form) {

        var parentForm = form;
        var formValues = ffbForm.getValues(parentForm);

        var values = {
            'uploadType'    : _.options.type,
            'destination'   : _.options.destination,
            'token'         : _.options.token,
            'referenceType' : _.options.referenceType,
            'referenceId'   : _.options.referenceId
        };

        //Init file upload
        _fileUpload = new ffbFileUpload({
            'area'       : parentForm.find('.drop-area'),
            'button'     : parentForm.find('.row.fileupload .button.file input'),
            'iframe'     : parentForm.find('iframe'),
            'progress'   : null,
            'form'       : parentForm,
            'autoUpload' : true,
            'onFileSelect' : function(fl) {

                if (_.options.multiple === false && _.options.files.length >= 1) {

                    ffbLightbox.showInfo({
                        'title' : ffbTranslator.translate('TTL_FILE_LOADING'),
                        'text'  : '<p>' + ffbTranslator.translate('MSG_ONLY_ONE_FILE_ALLOWED') + '</p>'
                    });
                    return false;
                }

                if (fl && fl.length >= 1) {
                    parentForm.find('input[name="filepath"]').val(fl[0].name);
                }

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
            },
            'onProgress' : function(fileName, progress) {

//                $('.upload-progress .value').css('width', parseInt(progress) + '%');
            },
            'onSuccess' : function(json, options) {

                _uploads--;

                //show 100%, remove cancel link
                if (json && json.state === 'ok') {

                    //close progress
                    if (_uploads === 0) {
                        ffbLightbox.close();
                    }

                    //set file to element if in answer only
                    if (json.file !== undefined) {
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

                var errorData = ajax.parseError(xhr.responseText);
                ffbLightbox.showInfo({
                    'title'     : ffbTranslator.translate('TTL_FILE_LOADING'),
                    'className' : 'error',
                    'text'      : errorData.message
                });
            }
        });
    };

    _.init(element, options);
};
