/*jshint -W117 */
"use strict";

/**
 * This class is responsible for initializing forms.
 * It helps to keep these initializations DRY.
 */
var FormInitializer = function(form, table) {

    var _          = this;
    var _form      = form;
    var _table     = table;
    var _dbg       = false;

    /**
     * Init select boxes as dropdowns with single or multiple selection.
     *
     * Furthermore this method initializes special select boxes
     * for colorpicker, usersearch, locationsearch & teaserselect.
     *
     * @public
     * @this {FormInitializer}
     * @param {string} selector
     * @param {object} options
     */
    _.initDropdowns = function(selector, options) {

        // set default values for optional parameters
        selector = 'undefined' === typeof selector ? 'select' : selector;
        options = 'undefined' === typeof options ? {} : options;

        var dropdowns = {};

        var selects = [];
        if (typeof selector === 'string') {
            selects = _form.find(selector);
        } else {
            selects = selector;
        }

        selects.each(function(i, sel) {

            //select boxes with classname "self-init" or already inited are not being initialized
            if (   $(sel).hasClass('self-init')
                || (   typeof $(sel).attr('id') !== 'undefined'
                    && $('#' + $(sel).attr('id') + '-custom').length > 0
                   )
            ) {
                return;
            }

            // check select types
            if ($(sel).attr('data-cpchange') === 'cpchange' && $(sel).attr('multiple') === 'multiple') {

                dropdowns[$(sel).attr('name')] = new MultipleSelect(sel, {
                    'onSelect': options.colorPickerMultipleOnSelect
                });

            } else if ($(sel).attr('data-cpchange') === 'cpchange') {

                dropdowns[$(sel).attr('name')] = new ffbDropdown(sel, {
                    'postFunc' : options.colorPickerPostFunc,
                    'onSelect' : function(el, index, value) {

                        var parent = $(el).parents('.row');
                        if (parent.hasClass('editable')) {
                            parent.find('.editable-button.ok').trigger('click');
                        }
                    }
                });
            } else if ($(sel).hasClass('usersearch')) {

                dropdowns[$(sel).attr('name')] = new UserSearch(sel);
            } else if ($(sel).hasClass('locationsearch')) {

                dropdowns[$(sel).attr('name')] = new LocationSearch(sel);
            } else if ($(sel).hasClass('teaserselect')) {

                dropdowns[$(sel).attr('name')] = new TeaserSelect(sel);
            } else {

                if (undefined === $(sel).attr('multiple')) {

                    // check default li height
                    if (typeof options.liHeight === 'undefined') options.liHeight = 25;

                    // check default onSelect
                    if (typeof options.onSelect === 'undefined') {

                        options.onSelect = function(el, index, value) {

                            var parent = $(el).parents('.row');
                            if (parent.hasClass('editable')) {
                                parent.find('.editable-button.ok').trigger('click');
                            }
                        };
                    }

                    dropdowns[$(sel).attr('name')] = new ffbDropdown(sel, options);
                } else {

                    dropdowns[$(sel).attr('name')] = new MultipleSelect(sel);
                }
            }
        });

        return dropdowns;
    }

    /**
     * Init files uploads.
     *
     * @public
     * @this {FormInitializer}
     * @param {function} onRenderCallback
     * @param {function} onChangeCallback
     * @returns {Array} initialized file uploads
     */
    _.initFileuploads = function(onRenderCallback, onChangeCallback) {

        var fileuploads = [];
        _form.find('input.fileupload').each(function(i, inp) {
            fileuploads.push(new FilesUpload(inp, onRenderCallback, onChangeCallback));
        });

        return fileuploads;
    };

    /**
     * Init tool tips.
     *
     * @public
     * @returns {Array} initialized tool tips
     * @this {FormInitializer}
     */
    _.initTooltips = function() {

        var tootltips = [];

        _form.find('.icon-tooltip').each(function(i, inp) {
            tootltips = new ffbTooltip(inp);
        });

        return tootltips;
    };

    /**
     * Init direct file uploads.
     *
     * @public
     * @this {FormInitializer}
     * @param {function} callBack
     * @param {function} deleteCallback
     */
    _.initDirectFileuploads = function(callBack, deleteCallback) {

        _form.find('input.directfileupload').each(function(i, inp) {
            new FileUpload(inp, callBack, deleteCallback);
        });
    };

    /**
     * Init inplace editors.
     *
     * @public
     * @this {FormInitializer}
     */
    _.initInplaceEditors = function(onSave, onError, multiple, autosave, container, beforeSend) {

        return new ffbInPlaceEditor(_form, onSave, onError, _table, multiple, autosave, container, beforeSend);
    }

    /**
     * Init placeholders.
     *
     * @public
     * @this {FormInitializer}
     */
    _.initPlaceholders = function(dataPlacehodlersOnly) {

        ffbForm.initPlaceholders(_form, dataPlacehodlersOnly);
    }

    /**
     * Init Rating stars
     *
     * @public
     * @this {FormInitializer}
     */
    _.initRatingStars = function() {

        _form.find('.rating-stars .reset').unbind().on('click', function(e) {

            var parent = $(this).parent();
            parent.find('.active').removeClass('active');
            parent.prev('input[type="hidden"]').val(0);

            return false;
        });

        _form.find('.rating-stars span').unbind().on('click', function(e) {

            if ($(this).hasClass('active')) {
                //$(this).removeClass('active');
                $(this).nextAll('.rating-stars span').removeClass('active');
            } else {
                $(this).addClass('active');
                $(this).prevAll('.rating-stars span').addClass('active');
            }

            var count = 0;
            $('.rating-stars span').each(function() {
                if ($(this).hasClass('active')) {
                    count += 1;
                }
            });

            $(this).parent().prev('input[type="hidden"]').val(count);

            return false;
        });
    }

    /**
     * Init inplace editors.
     *
     * @public
     * @this {FormInitializer}
     * @param {string} selector Elements selector string
     * @param {function} onShow Callback
     * @param {function} onSelect Callback
     * @param {function} onMove Callback
     * @param {array} options Options array
     */
    _.initDatePicker = function(selector, onShow, onSelect, onMove, withTime, options) {

        if (typeof selector === 'undefined' || selector === null) {
            selector = '.datepicker';
        }
        if (onShow === undefined) {
            onShow = null;
        }
        if (onSelect === undefined) {
            onSelect = null;
        }
        if (onMove === undefined) {
            onMove = null;
        }
        if (withTime === undefined) {
            withTime = false;
        } else {
            withTime = true;
        }

        // get format form locale
        var formats = {
            'de' : 'd.m.y',
            'en' : 'm/d/y'
        };
        var locale = myApp.locale;

        var readonly = true;
        if (options) {
            if (options.hasOwnProperty('readonly')) {
                readonly = options.readonly;
            }
        }

        _form.find(selector).each(function(i, cal) {
            // init calendar
            new ffbCalendar($(cal),  {
                'type'       : 'popup',
                'className'  : 'red-white',
                'format'     : formats[locale],                                   //Date format
                'timeFormat' : 'h:i:s',                                           //Time format
                'startDay'   : 'm',                                               //Week start day [m,s]
                'weeks'      : true,                                             //Show week position [true, false]
                'time'       : withTime,
                'readonly'   : false,                                            //Set readonly for input
                'days'       : ffbTranslator.translate('VAL_CALENDAR_DAYS'),      //Days names
                'months'     : ffbTranslator.translate('VAL_CALENDAR_MONTHS'),    //Month names
                'minDate'    : null,                                             //Min date value
                'maxDate'    : null,                                             //Max date value
                'onShow'     : onShow,
                'onSelect'   : onSelect,
                'onMove'     : onMove,
                'cw'         : ffbTranslator.translate('VAL_CALENDAR_WEEKS'),
                'weekends'   : 'enabled',                                         //Allow click for weekends [enabled, disabled]);
                'autoClose'  : true                                              //Close Calendar by outter click
            });
        });
    };

    /**
     * Init colorpicker.
     * 
     * @param selector filter for classes
     * @param class to set for colorPicker
     *
     * @public
     * @this {FormInitializer}
     */
    _.initColorPicker = function(colors, defaultColor, selector, cssClass) {

        var defaultColors = [
            'A4DCFF', '72BCD7', '006CD0', 'A7299A', 'FF7BCC', 'FF734D', 'FF2F2F', 'B90000',
            '72DA26', '468C42', '969478', 'FFC066', 'FFD3B0', 'CA8222', 'B3B3B3', '494949',

            // extra
            'ffffff', 'a70016', '334b7a', 'f2f2f2'
        ];
        
        if (typeof defaultColor !== 'undefined') {
            $.fn.colorPicker.defaults.pickerDefault = defaultColor;
        }
        
        if (typeof colors !== 'undefined') {
            $.fn.colorPicker.defaults.colors = colors;
        }

        if (typeof cssClass !== 'undefined') {
            $.fn.colorPicker.defaults.cssClass = cssClass;
        } else {
            $.fn.colorPicker.defaults.cssClass = '';
        }

        if (typeof selector !== 'undefined') {
            _form.find('input.colorpicker' + selector).each(function(){
                $(this).colorPicker();
            });
        } else {
            _form.find('input.colorpicker').each(function(){
                $(this).colorPicker();
            });
        }  
    };

    /**
     * Init tabs
     *
     * @public
     * @this {FormInitializer}
     */
    _.initTabs = function(container, onSelect) {

        if (!container) {
            container = _form;
        }

        if (container.find('.tabs').length > 0 && typeof ffbTabs !== 'undefined') {
            new ffbTabs(container, onSelect);
        }
    };

    /**
     * Init smart tabs
     *
     * @public
     * @this {FormInitializer}
     */
    _.initSmartTabs = function(container, onSelect, options) {

        if (!container) {
            container = _form;
    }

        if (container.find('.smart-tabs').length > 0 && typeof ffbSmartTabs !== 'undefined') {
            new ffbSmartTabs(container, onSelect, options);
        }
    };

    /**
     *
     * @param {string} field_name
     * @param {string} url
     * @param {string} type
     * @param {object} win
     */
    var _fileBrowser = function(field_name, url, type, win) {

        _dbg && console.log(this, field_name, url, type, win); // debug/testing

        // get upload data
        var el      = tinyMCE.activeEditor.getElement();
        var data    = ajax.isJSON($(el).attr('data-uploadconf'));
        var form    = null;

        // no config return
        if (!data) {
            return;
        }

        // open lightbox
        // title 	String 	Window title.
        // file 	String 	URL of the file to open in the window.
        // width 	Number 	Width in pixels.
        // height 	Number 	Height in pixels.
        // resizable 	Boolean 	Specifies whether the popup window is resizable or not.
        // maximizable 	Boolean 	Specifies whether the popup window has a "maximize" button and can get maximized or not.
        // inline 	Boolean 	Specifies whether to display in-line (set to 1 or true for in-line display; requires inlinepopups plugin).
        // popup_css 	String/Boolean 	Optional CSS to use in the popup. Set to false to remove the default one.
        // translate_i18n 	Boolean 	Specifies whether translation should occur or not of i18 key strings. Default is true.
        // close_previous 	String/bool 	Specifies whether a previously opened popup window is to be closed or not (like when calling the file browser window over the advlink popup).
        // scrollbars 	String/bool 	Specifies whether the popup window can have scrollbars if required (i.e. content larger than the popup size specified).
        var modal = tinyMCE.activeEditor.windowManager.open({
            /*file           : data.uploadFormUrl,*/
            title          : ffbTranslator.translate('TTL_SELECT_FILE'),
            width          : 990,
            height         : 400,
            resizable      : 'yes',
            /*inline       : 'yes',  // This parameter only has an effect if you use the inlinepopups plugin!*/
            close_previous : 'no',
            buttons: [
                {
                    text    : ffbTranslator.translate('BTN_SELECT_FILE'),
                    classes : 'widget btn primary',
                    onclick : function(e) {

                        // init rank links
                        var selected = form.find('.files-list input[type="radio"]:checked');
                        if (selected.length > 0) {
                            win.document.getElementById(field_name).value = selected.attr('data-url');
                        }
                        modal.close();
                    }
                }
            ]
            /*body : 'test',*/
        }, {
            window : win,
            input  : field_name
        });

        var postData = {
            'destination'   : data.destination,
            'referenceId'   : data.referenceId,
            'referenceType'	: data.referenceType,
            /*'token'	        : *///no need user is exist allways
            'uploadType'	: data.type
        };

        if (type === 'image') {
            postData.uploadType = 'images';
        } else {
            postData.uploadType = 'documents';
        }

        // load form
        ajax.call(
            data.uploadFormUrl,
            function(html) {

                // set form
                var cnt = $(modal.getEl());
                cnt.find('> .mce-reset > .mce-container-body').html(html);

                // init form
                form = cnt.find('#upload-form');

                // generate options
                var options = {
                    'uploadFileUrl'      : data.uploadFileUrl,
                    'deleteFileUrl'      : data.deleteFileUrl,
                    'files'              : data.files,
                    'type'               : postData.uploadType,
                    'destination'        : data.destination,
                    'referenceId'        : data.referenceId,
                    'referenceType'      : data.referenceType,
                    'selectType'         : type
                };

                form.find('input[type="file"]').each(function(i, inp) {
                    new EditorFileUpload(
                        inp,
                        options,
                        function(upload) {
                            // save current setting to object
                            data.files = upload.options.files;
                            $(el).attr('data-uploadconf', JSON.stringify(data));
                        },
                        function(upload) {
                            // save current setting to object
                            data.files = upload.options.files;
                            $(el).attr('data-uploadconf', JSON.stringify(data));
                        }
                    );
                });
            },
            {
                'accepts' : 'partial',
                'data'    : postData,
                'type'    : 'post'
            }
        );
    };

    /**
     *
     * @param {HTMLElement} element
     */
    _.initTiny =  function(element) {

        /*
        plugins: [
            "advlist autolink lists link image charmap print preview hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "insertdatetime media nonbreaking save table contextmenu directionality",
            "emoticons template paste textcolor colorpicker textpattern"
        ],
        //plugins: "visualblocks",
        toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        toolbar2: "print preview media | forecolor backcolor emoticons",
        image_advtab: true,
        style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ],
//        style_formats: [
//            {title: 'Headers', items: [
//                {title: 'h1', block: 'h1'},
//                {title: 'h2', block: 'h2'},
//                {title: 'h3', block: 'h3'},
//                {title: 'h4', block: 'h4'},
//                {title: 'h5', block: 'h5'},
//                {title: 'h6', block: 'h6'}
//            ]},
//
//            {title: 'Blocks', items: [
//                {title: 'p', block: 'p'},
//                {title: 'div', block: 'div'},
//                {title: 'pre', block: 'pre'}
//            ]},
//
//            {title: 'Containers', items: [
//                {title: 'section', block: 'section', wrapper: true, merge_siblings: false},
//                {title: 'article', block: 'article', wrapper: true, merge_siblings: false},
//                {title: 'blockquote', block: 'blockquote', wrapper: true},
//                {title: 'hgroup', block: 'hgroup', wrapper: true},
//                {title: 'aside', block: 'aside', wrapper: true},
//                {title: 'figure', block: 'figure', wrapper: true}
//            ]}
//        ],
        formats: {
            alignleft: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left'},
            aligncenter: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center'},
            alignright: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right'},
            alignfull: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full'},
            bold: {inline: 'span', 'classes': 'bold'},
            italic: {inline: 'span', 'classes': 'italic'},
            underline: {inline: 'span', 'classes': 'underline', exact: true},
            strikethrough: {inline: 'del'},
            customformat: {inline: 'span', styles: {color: '#00ff00', fontSize: '20px'}, attributes: {title: 'My custom format'}}
        },
        templates: [
            {title: 'Test template 1', content: 'Test 1'},
            {title: 'Test template 2', content: 'Test 2'}
        ],
//        relative_urls: true,
//        document_base_url: 'http://www.tinymce.com/tryit/',

//        visualblocks_default_state: true,
//        end_container_on_empty_block: true

        tinymce.init({
            selector: "textarea",
            toolbar: "mybutton",
            setup: function(editor) {
                editor.addButton('mybutton', {
                    text: 'My button',
                    icon: false,
                    onclick: function() {
                        editor.insertContent('Main button');
                    }
                });

                editor.addButton('mybutton', {
                    type: 'menubutton',
                    text: 'My button',
                    icon: false,
                    menu: [
                        {text: 'Menu item 1', onclick: function() {editor.insertContent('Menu item 1');}},
                        {text: 'Menu item 2', onclick: function() {editor.insertContent('Menu item 2');}}
                    ]
                });

                editor.addButton('mybutton', {
                    type: 'splitbutton',
                    text: 'My button',
                    icon: false,
                    onclick: function() {
                        editor.insertContent('Main button');
                    },
                    menu: [
                        {text: 'Menu item 1', onclick: function() {editor.insertContent('Menu item 1');}},
                        {text: 'Menu item 2', onclick: function() {editor.insertContent('Menu item 2');}}
                    ]
                });

                editor.addButton('mybutton', {
                    type: 'listbox',
                    text: 'My listbox',
                    icon: false,
                    onselect: function(e) {
                        editor.insertContent(this.value());
                    },
                    values: [
                        {text: 'Menu item 1', value: 'Some text 1'},
                        {text: 'Menu item 2', value: 'Some text 2'},
                        {text: 'Menu item 3', value: 'Some text 3'}
                    ],
                    onPostRender: function() {
                        // Select the second item by default
                        this.value('Some text 2');
                    }
                });

                editor.addMenuItem('myitem', {
                    text: 'My menu item',
                    context: 'tools',
                    onclick: function() {
                        editor.insertContent('Some content');
                    }
                });

            }
        });
        */

        // prepare default values
        var plugins      = [
            'autolink link image'
        ];
        var toolbar      = [
            'undo redo',
            'styleselect',
            'bold italic',
            'link image',
            'alignleft aligncenter alignright'
        ];
        var textcolorMap = [
            '000000', 'Black',
            '993300', 'Burnt orange',
            '333300', 'Dark olive',
            '003300', 'Dark green',
            '003366', 'Dark azure',
            '000080', 'Navy Blue',
            '333399', 'Indigo',
            '333333', 'Very dark gray',
            '800000', 'Maroon',
            'FF6600', 'Orange',
            '808000', 'Olive',
            '008000', 'Green',
            '008080', 'Teal',
            '0000FF', 'Blue',
            '666699', 'Grayish blue',
            '808080', 'Gray',
            'FF0000', 'Red',
            'FF9900', 'Amber',
            '99CC00', 'Yellow green',
            '339966', 'Sea green',
            '33CCCC', 'Turquoise',
            '3366FF', 'Royal blue',
            '800080', 'Purple',
            '999999', 'Medium gray',
            'FF00FF', 'Magenta',
            'FFCC00', 'Gold',
            'FFFF00', 'Yellow',
            '00FF00', 'Lime',
            '00FFFF', 'Aqua',
            '00CCFF', 'Sky blue',
            '993366', 'Red violet',
            'FFFFFF', 'White',
            'FF99CC', 'Pink',
            'FFCC99', 'Peach',
            'FFFF99', 'Light yellow',
            'CCFFCC', 'Pale green',
            'CCFFFF', 'Pale cyan',
            '99CCFF', 'Light sky blue',
            'CC99FF', 'Plum'
        ];
        var fontsizeFormats = '8px 10px 12px 14px 18px 24px 36px';

        // get variable from global if exist
        if ('_gProjectConfig' in window) {

            var tinyConf = window._gProjectConfig.tinymce;

            if (tinyConf.fontsize_formats !== undefined) {
                fontsizeFormats = tinyConf.fontsize_formats;
            }

            if (tinyConf.plugins !== undefined) {
                plugins = tinyConf.plugins;
            }

            if (tinyConf.toolbar !== undefined) {
                toolbar = tinyConf.toolbar;
            }

            if (tinyConf.textcolor_map !== undefined) {

                //translate
                var extraColors = tinyConf.textcolor_map;
                for (var i = 1; i < extraColors.length; i += 2) {
                    extraColors[i] = ffbTranslator.translate(extraColors[i]);
                }

                //concat
                textcolorMap = extraColors.concat(textcolorMap);
            }
        }

        tinymce.remove();

        // get elements
        var els = _form.find(element);
        els.each(function(i, el) {

            // check upload conf
            var uplconf     = ajax.isJSON($(el).attr('data-uploadconf'));
            var fileBrowser = null;
            if (uplconf) {
                fileBrowser = _fileBrowser;
            }

            if (typeof $(el).attr('id') === 'undefined') {
                $(el).attr('id', 'edt' + parseInt((1 + Math.random() * 10000)).toString(16));
            }

            // init editor
            tinymce.init({
                /*content_css         : 'css/content.css',*/
                relative_urls         : false,
                remove_script_host    : false,
                /*document_base_url   : 'http://www.tinymce.com/tryit/',*/
                elements              : el.id,
                file_browser_callback : fileBrowser,
                fontsize_formats      : fontsizeFormats,
                textcolor_map         : textcolorMap,
                inline                : false,
                language              : 'de',
                menubar               : false,
                mode                  : 'exact',
                plugins               : plugins,
                /*selector            : el,*/
                theme                 : 'modern',
                toolbar               : toolbar.join(' | '),
                toolbar_items_size    : 'small'
            });
        });
    };
};
