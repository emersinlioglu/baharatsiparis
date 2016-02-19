/*jshint -W117 */
"use strict";

/**
 * ---=== Wizard elements ===---
 */

/**
 * Plugins panel
 *
 * @this PanelPlugins
 * @param {HTMLELement} parentCnt
 */
var PanelPlugins = function(parentCnt) {

    this.parentCnt = parentCnt;
    this.formWiz   = null;

    /**
     * Create Elements bar HTML
     *
     * @public
     * @this PanelPlugins
     * @param {array} items
     * @param {array} templates
     * @return {HTMLElement} cnt
     */
    this.getElementsButtonsHTML = function(items, templates) {

        // add translation suffix from options
        var title = 'TTL_SIMPLE_ELEMENTS';
        if (this.formWiz.options.translationSuff) {
            title += this.formWiz.options.translationSuff;
        }

        //create html for bar
        var cnt   = $('<div>').addClass('bar elements');
        var label = $('<div>')
            .addClass('title')
            .html(ffbTranslator.translate(title));

        //crete html for buttons
        var list     = $('<ul>');
        var fElement = new FormElement();
        for (var i = 0; i < items.length; i++) {

            var btn = null;
            var el  = null;
            switch (fElement[items[i]]) {
                case fElement.ELEMENT_TYPE_TEXT:

                    el = new FormTextElement();
                    break;
                case fElement.ELEMENT_TYPE_TEXTAREA:

                    el = new FormTextareaElement();
                    break;
                case fElement.ELEMENT_TYPE_SELECT:

                    el = new FormSelectElement();
                    break;
                case fElement.ELEMENT_TYPE_CHECKBOX:

                    el = new FormCheckboxgroupElement();
                    break;
                case fElement.ELEMENT_TYPE_RADIO:

                    el = new FormRadiogroupElement();
                    break;
                case fElement.ELEMENT_TYPE_HEADLINE:

                    el = new FormHeadlineElement();
                    break;
                case fElement.ELEMENT_TYPE_EXTENDED_SELECT:

                    el = new FormExtendedSelectElement();
                    break;
                case fElement.ELEMENT_TYPE_SEPARATOR:

                    el = new FormSeparatorElement();
                    break;
                case fElement.ELEMENT_TYPE_MULTIPLE_SELECT:

                    el = new FormMultipleSelectElement();
                    break;
                case fElement.ELEMENT_TYPE_NOTE_TEXT:

                    el = new FormNoteTextElement();
                    break;
            }
            if (el) {
                el.formWiz = this.formWiz;
                btn = el.getBarButton();
            }

            if (btn) {
                var j = i + 1;
                var beforeTemplate = '';
                if (j >= items.length) {
                    beforeTemplate = 'class="before-template"';
                }
                list.append($('<li ' + beforeTemplate + '>').append(btn));
                
                if (beforeTemplate !== '') {
                    
                    // add titel for templates if exist translation
                    var titleTpls = 'TTL_SIMPLE_ELEMENTS_TEMPLATES';
                    if (this.formWiz.options.translationSuff) {
                        titleTpls += this.formWiz.options.translationSuff;
                    }                

                    if (ffbTranslator.translate(titleTpls) !== titleTpls) {
                        list.append($('<li class="title">').html(ffbTranslator.translate(titleTpls)));
                    }
                }
            }
        }

        //add element templates
        for (i = 0; i < templates.length; i++) {

            var template = new FormTemplate();
            template.setData(templates[i]);
            
            var firstTemplate = '';
            if (i === 0) {
                firstTemplate = 'class="first-template"';
            }
            list.append($('<li ' + firstTemplate + '>').append(template.getBarButton()));
        }

        if (list.length > 0) {
            cnt.append(label);
            cnt.append(list);
        }

        return cnt;
    };

    /**
     * Create Groups bar HTML
     *
     * @public
     * @this PanelPlugins
     * @param {array} items
     * @param {array} templates
     * @return {HTMLElement} cnt
     */
    this.getGroupsButtonsHTML = function(items, templates) {

        // add translation suffix from options
        var title = 'TTL_GROUPS';
        if (this.formWiz.options.translationSuff) {
            title += this.formWiz.options.translationSuff;
        }

        //create html for bar
        var cnt   = $('<div>').addClass('bar groups');
        var label = $('<div>')
            .addClass('title')
            .html(ffbTranslator.translate(title));

        //crete html for buttons
        var list     = $('<ul>');
        var fElement = new FormGroup();
        for (var i = 0; i < items.length; i++) {

            var btn  = null;
            switch (fElement[items[i]]) {
                case fElement.GROUP_TYPE_DEFAULT:

                    var el = new FormGroupDefault();
                    el.formWiz = this.formWiz;
                    btn = el.getBarButton();
                    break;
            }

            if (btn) {
                list.append($('<li>').append(btn));
            }
        }

        //add group templates
        for (i = 0; i < templates.length; i++) {

            var template = new FormTemplate();
            template.setData(templates[i]);

            var firstTemplate = '';
            if (i === 0) {
                firstTemplate = 'class="first-template"';
            }
            list.append($('<li ' + firstTemplate + '>').append(template.getBarButton()));
        }

        if (list.children().length > 0) {
            cnt.append(label);
            cnt.append(list);
        }

        return cnt;
    };

    /**
     * Create Layouts bar HTML
     *
     * @public
     * @this PanelPlugins
     * @param {array} items
     * @param {array} templates
     * @return {HTMLElement} cnt
     */
    this.getLayoutsButtonsHTML = function(items, templates) {

        // add translation suffix from options
        var title = 'TTL_LAYOUTS';
        if (this.formWiz.options.translationSuff) {
            title += this.formWiz.options.translationSuff;
        }

        //create html for bar
        var cnt   = $('<div>').addClass('bar layouts');
        var label = $('<div>')
            .addClass('title')
            .html(ffbTranslator.translate(title));

        //crete html for buttons
        var list     = $('<ul>');
        var fElement = new FormLayout();
        for (var i = 0; i < items.length; i++) {

            var btn = null;
            switch (fElement[items[i]]) {
                case fElement.LAYOUT_TYPE_DEFAULT:

                    var el = new FormLayoutDefault();
                    el.formWiz = this.formWiz;
                    btn = el.getBarButton();
                    break;
            }

            if (btn) {
                list.append($('<li>').append(btn));
            }
        }

        //add group templates
        for (i = 0; i < templates.length; i++) {

            var template = new FormTemplate();
            template.setData(templates[i]);

            var firstTemplate = '';
            if (i === 0) {
                firstTemplate = 'class="first-template"';
            }
            list.append($('<li ' + firstTemplate + '>').append(template.getBarButton()));
        }

        if (list.children().length > 0) {
            cnt.append(label);
            cnt.append(list);
        }

        return cnt;
    };

    /**
     * On Button drag
     *
     * @public
     * @this PanelPlugins
     * @param {Event} event
     * @param {object} ui
     */
    this.onButtonDrag = function(event, ui) {

        var hovers = this.parentCnt.find('.hover');
        if (hovers.length === 0) {
            return;
        }

        // get last hovered
        var hovered = hovers.last();

        // clear all
        this.parentCnt.find('.hover, .before, .after').removeClass('hover before after');

        // set current as hovered
        hovered.addClass('hover');

        // check hovered type
        if (hovered.hasClass('workspace')) {

        } else if ((hovered.hasClass('group') || hovered.hasClass('element')) && hovered.length === 1) {

            //get before/after
            var hT = hovered.offset().top;
            var hH = parseInt(hovered.height() / 2);
            var bT = ui.offset.top;
            var diff = bT - hT;

            if (diff <= hH) {
                hovered
                    .addClass('before');
            } else {
                hovered
                    .addClass('after');
            }
        }
    };

    /**
     * Create HTMLElement
     *
     * @public
     * @this PanelPlugins
     * @param {object} plugins
     * @param {object} templates
     * @return {HTMLElement} cnt;
     */
    this.getHTML = function(plugins, templates) {

        var cnt = $('<div>')
            .addClass('panel tools');

        cnt.append(this.getGroupsButtonsHTML(plugins.groups, templates.groups));
        cnt.append(this.getLayoutsButtonsHTML(plugins.layouts, templates.layouts));
        cnt.append(this.getElementsButtonsHTML(plugins.elements, templates.elements));

        var self = this;

        //init DnD for buttons
        //http://api.jqueryui.com/draggable/
        cnt.find('.button')
            .draggable({
                addClasses        : false, //true
//                appendTo          : 'parent',
                axis              : false, //x, y
//                cancel            : "input,textarea,button,select,option",
//                connectToSortable : false,
                containment       : this.parentCnt, //false
                cursor            : 'auto',
                cursorAt          : false, //{top, left, right, bottom}
                delay             : 0,
//                disabled          : false,
//                distance          : 1,
//                grid              : false,
                handle            : false,
                helper            : 'clone', //original, clone
//                iframeFix         : false,
                opacity           : false,
                refreshPositions  : false,
                revert            : true,
//                revertDuration    : 500,
                scope             : 'fwbuttons',
                scroll            : true,
                scrollSensitivity : 100, //20
                scrollSpeed       : 20, //20
//                snap              : false,
//                snapMode          : 'both', //inner, outer, both
//                snapTolerance     : 20,
//                stack             : false,
                zIndex            : 101, //false
                drag              : function(event, ui) {//ui.helper, ui.position, ui.offset

                    self.onButtonDrag(event, ui);
                },
                start             : function(event, ui) {
                    // remove all existed hover classes
                    $('.hover, .after, .before').removeClass('hover after before');
                },
                stop              : function(event, ui) {
                    // remove all existed hover classes
                    $('.hover, .after, .before').removeClass('hover after before');
                }
            });

        return cnt;
    };
};

/**
 * Canvas panel
 *
 * @this PanelCanvas
 * @param {HTMLELement} parentCnt
 */
var PanelCanvas = function(parentCnt) {

    this.parentCnt = parentCnt;

    /**
     * Create HTMLElement
     *
     * @public
     * @this PanelPlugins
     * @return {HTMLElement} cnt
     */
    this.getHTML = function() {

        var cnt = $('<div>')
            .addClass('panel canvas');

        var info = $('<div>')
            .addClass('info')
            .append(
                $('<span>')
                    .html(ffbTranslator.translate('MSG_DND_AREA'))
            );

        var workspace = $('<div>')
            .addClass('workspace');

        cnt.append(info);
        cnt.append(workspace);

        return cnt;
    };
};
