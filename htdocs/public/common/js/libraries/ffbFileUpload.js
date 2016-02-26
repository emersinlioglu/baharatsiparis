"use strict";

/**
 * Fileupload
 *
 * @class
 * @param {object} options
 */
var ffbFileUpload = function(options) {

    var _ = this;

    var _isChrome = false;

    _.bigSizeFiles  = [];
    _.filesToUpload = [];
    _.isIE          = false;
    _.isNewIE       = false;
    _.options       = {
        'area'          : null,
        'autoUpload'    : true,
        'button'        : null,
        'formData'      : null,
        'iframe'        : null,
        'progress'      : null,
        'form'          : null,
        'uploadUrl'     : null,
        'onFileSelect'  : null,
        'onProgress'    : null,
        'onSuccess'     : null,
        'onUploadStart' : null,
        'onError'       : null,
        'maxFileSize'   : 5245329
    }
    _.xhr           = null;

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

    var _getUploadUrl = function() {

        var func = _.getFunctionsParts(_.options.uploadUrl);
        if (func) return func();
        else return _.options.uploadUrl;

    }

    //Show uploaded files info
    var _onFileSelect = function(filesList) {

        if (_.options.onFileSelect) {
            var func = _.getFunctionsParts(_.options.onFileSelect);
            if (func) {
                return func(filesList);
            }
        }

        return true;
    }

    //Show uploaded files info
    var _onUploadStart = function(filesInfo) {

        if (_.options.onUploadStart) {
            var func = _.getFunctionsParts(_.options.onUploadStart);
            if (func) func(filesInfo);
        }

    }

    //Show uploaded files info
    var _onProgress = function(fileName, progress) {

        if (_.options.onProgress) {
            var func = _.getFunctionsParts(_.options.onProgress);
            if (func) func(fileName, progress);
        }

    }

    //Files are uploaded, create files list, show message
    var _onSuccessUpload = function(json) {

        if (_.options.onSuccess) {
            var func = _.getFunctionsParts(_.options.onSuccess);
            if (func) func(json, options);
        }

    }

    //Error during ajax upload
    var _onUploadError = function() {

        if (_.options.onError) {
            var func = _.getFunctionsParts(_.options.onError);
            if (func) func(_.xhr, _.options);
        }

    }

    //Upload files per ajax
    var _uploadFilePerAjax = function(file) {

        //Modern Browsers, but not safari, send file per ajax
        var reader = new FileReader();

        reader.onload = function() {

            if (_.xhr) _.xhr.abort();
            _.xhr = new XMLHttpRequest();
            _.xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {

                    var progress = (e.loaded * 100) / e.total;
                    _onProgress(file.name, progress);

                }
            }, false);

            //.load, .error
            _.xhr.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status === 200) {

                        var result = $.parseJSON(this.responseText);
                        if (result) _onSuccessUpload(result);

                        //Send next file to upload
                        _.filesToUpload.pop();
                        if (_.filesToUpload.length > 0) _uploadFilePerAjax($(_.filesToUpload).last()[0]);

                    } else {

                        _onUploadError();
                    }
                }
            }

            var url = _getUploadUrl();

            _.xhr.open('POST', url);
            var boundary = 'xxxxxxxxx';

            //Set headers
            _.xhr.setRequestHeader('Accept', 'application/json');
            _.xhr.setRequestHeader('Content-Type', 'multipart/form-data, boundary=' + boundary);
            _.xhr.setRequestHeader('Cache-Control', 'no-cache');
            _.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            //convert form data to body
            var formData = null;
            if (_.options.formData) {

                formData = '';
                for (var key in _.options.formData) {
                    if (!_.options.formData.hasOwnProperty(key)) continue;

                    formData += "Content-Disposition: form-data; name='" + key + "'\r\n\r\n";
                    formData += _.options.formData[key] + "\r\n";
                    formData += '--' + boundary + "\r\n";

                }

            }

            //Create request body
            var body = '--' + boundary + "\r\n";
            if (formData) body += formData;
            body += "Content-Disposition: form-data; name='" + _.options.button.attr('name') + "'; filename='" + file.name + "'\r\n";
            body += "Content-Type: application/octet-stream\r\n\r\n";
            body += reader.result + "\r\n";
            body += '--' + boundary + '--';

            _.xhr.setRequestHeader('Content-length', body.length);

            if (_.xhr.sendAsBinary) {
                //Firefox
                _.xhr.sendAsBinary(body);
            } else {
                //Chrome
                _.xhr.send(body);
            }

        }

        //Read file
        reader.readAsBinaryString(file);

    }

    //Upload file with iframe
    var _uploadFilesWithFormDatauploadFilePerAjax = function(file) {

        //Create form data
        var fd = new FormData();
        fd.append(_.options.button.attr('name'), file);

        if (_.options.formData) {

            for (var key in _.options.formData) {
                if (!_.options.formData.hasOwnProperty(key)) continue;

                fd.append(key, _.options.formData[key]);
            }
        }

        if (fd) {

            if (_.xhr) _.xhr.abort();
            _.xhr = new XMLHttpRequest();
            _.xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {

                    var progress = (e.loaded * 100) / e.total;
                    _onProgress(file.name, progress);

                }
            }, false);

            _.xhr.onreadystatechange = function () {
                if (this.readyState === 4) {
                    if (this.status === 200) {

                        var result = $.parseJSON(this.responseText);
                        if (result) _onSuccessUpload(result);

                        //Send next file to upload
                        _.filesToUpload.pop();
                        if (_.filesToUpload.length > 0) _uploadFilesWithFormDatauploadFilePerAjax($(_.filesToUpload).last()[0]);

                    } else {

                        //Show error
                        _onUploadError();
                    }
                }
            }

            var url = _getUploadUrl();
            _.xhr.open('POST', url, true);
            _.xhr.setRequestHeader('Accept', 'application/json');
            _.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            _.xhr.send(fd);
        }
    }

    var _initControls = function() {

        var btn  = _.options.button;
        var area = _.options.area;
        var iframe = _.options.iframe;

        //Init upload for button
        btn.on('change', function(event) {

            var filesList = null;
            if (this.files) {
                filesList = this.files;
            }

            if (_onFileSelect(filesList) === false) return false;

            if (_.options.autoUpload) {
                _.uploadFiles(filesList);
            }
        });

        //Enable DD for modern browsers only
        if (!_.isIE || _.isNewIE === true) {

            //Init drag & drop upload
            area.on('dragenter', function(event) {

                event.preventDefault();
                $(this).addClass('highlighted');

            });
            area.on('dragover', function(event) {

                event.preventDefault();

            });
            area.on('dragleave', function(event) {

                event.preventDefault();
                $(this).removeClass('highlighted');

            });
            area.on('drop', function(event) {

                event.preventDefault();

                $(this).removeClass('highlighted');

                if (event.originalEvent !== undefined) event = event.originalEvent;
                var filesList = null;
                if (event.dataTransfer && event.dataTransfer.files) {
                    filesList = event.dataTransfer.files;
                }

                if (_onFileSelect(filesList) === false) return false;

                if (_.options.autoUpload) _.uploadFiles(filesList);
            });
        } else {



        }

        //Add onLoad action for fileupload iframe
        iframe.on('load', function(event) {

            //Show upload progress
            //$(_.options.progress).addClassName('hide');

            var iContent = this.contentDocument && this.contentDocument.body.innerHTML;
            if (!iContent) iContent = this.contentWindow.document.body.innerHTML;

            if (iContent) {

                //Result is json
                var result = $.parseJSON(iContent);
                if (result) _onSuccessUpload(result);
            }
        });
    }

    //Set form data
    _.setFormData = function(data) {

        //Init options
        if (data && typeof(data) === 'object') _.options.formData = data;
    }

    //Upload files on server
    _.uploadFiles = function(filesList) {

        //$(_.options.progress).removeClassName('hide');        

        //IE7 and IE8 - make submit
        if (!filesList) {

            var fName = _.options.button.val().split(/(\\|\/)/g).pop();

            var fileInfo = {
                'name' : fName,
                'size' : 0
            }

            _onUploadStart(fileInfo);

            //Get form and attributes
            var uForm = $('<form style="display: none;" />');

            //Set upload form attribs
            uForm.attr('action', _getUploadUrl());
            uForm.attr('enctype', 'multipart/form-data');
            uForm.attr('target', _.options.iframe.attr('name'));
            uForm.attr('method', 'post');

            //Set upload elements
            uForm.append($('<input name="token" type="hidden" value="' + _.options.formData.token + '" />'));
            uForm.append($('<input name="referenceType" type="hidden" value="' + _.options.formData.referenceType + '" />'));
            uForm.append($('<input name="referenceId" type="hidden" value="' + _.options.formData.referenceId + '" />'));
            uForm.append($('<input name="destination" type="hidden" value="' + _.options.formData.destination + '" />'));
            uForm.append($('<input name="uploadType" type="hidden" value="' + _.options.formData.uploadType + '" />'));

            //checks uploads count
            var uploads = _.options.form.find('input[name="upload"]');
            var upload  = null;
            if (uploads.length === 1) {
                upload = _.options.form.find('input[name="upload"]');
            } else {
                upload = _.options.form.find('input[name="upload"].uploading');
            }

            //get upload parent
            var uploadParent = upload.parent();

            //move upload in temp form
            uForm.append(upload);            

            //Upload
            $('body').append(uForm);
            uForm[0].submit();

            //move upload back
            uploadParent.append(upload);

            //remove temp form
            uForm.remove();
        } else {

            // hide max file size warning on next upload
            $('.max-file-size').addClass('hide');

            _.filesToUpload = [];

            for (var key in filesList) {
                //if (!filesList.hasOwnProperty(key) || filesList[key] == undefined || key == 'length') continue;
                if (typeof filesList[key] !== 'object') continue;

                var file = filesList[key];

                if (file.size > _.options.maxFileSize) {

                    _.bigSizeFiles.push(file);
                    continue;
                }

                _onUploadStart(file);

                _.filesToUpload.push(file);

            }

            /*if (window.FileReader && _.filesToUpload.length > 0 && !_isChrome) _uploadFilePerAjax($(_.filesToUpload).last()[0]);
            else*/ if (window.FormData && _.filesToUpload.length > 0) _uploadFilesWithFormDatauploadFilePerAjax($(_.filesToUpload).last()[0]);

            // show max file size warning
            if (_.bigSizeFiles.length > 0) {

                //var fileNames = $('<ul>');
                //for(var key in _.bigSizeFiles) {
                //    var file = _.bigSizeFiles[key];
                //    $('<li>').text(file.name).appendTo(fileNames);
                //}
                _.bigSizeFiles = [];

                $('.max-file-size').removeClass('hide');
            }
        }
    }

    //Init area, button and onSuccess
    _.init = function(options) {

        //Init options
        if (options && typeof(options) === 'object') {
            for (var key in options) {
                if (!options.hasOwnProperty(key)) continue;
                _.options[key] = options[key];
            }
        }

        var xhr = new XMLHttpRequest();
        if (!xhr.sendAsBinary) {

            _isChrome = true;
        }

        var m = navigator.userAgent.match(/MSIE+ +(\d*)/);
        if (m) {

            _.isIE = true;
            if (parseInt(m[1]) >= 10) {
                _.isNewIE = true;
            }
        }

        _initControls();
    }

    //Init uploader
    _.init(options);
}
