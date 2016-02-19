/*jshint -W117 */
"use strict";

/**
 * ---=== Form elements ===---
 */

/**
 * Form Group
 *
 * @this FormGroup
 */
var FormGroup = function() {

    this.ITEM_TYPE_GROUP    = 1;
    this.ITEM_TYPE_LAYOUT   = 2;
    this.ITEM_TYPE_ELEMENT  = 3;

    this.GROUP_TYPE_DEFAULT = 1;

    this.buttonClass = null;
    this.buttonTitle = 'BTN_GROUP_DEFAULT';
    this.cnt         = null;
    this.data        = [];
    this.formWiz     = null;
    this.id          = null;
    this.index       = null;  //index in array to html rendern
    this.label       = {};
    this.locale      = null;
    this.type        = null;
    this.properties  = [];

    //set default properties
    this.properties.push(new FormProperty('is-mandatory', false));
    this.properties.push(new FormProperty('is-editable' , true));
    this.properties.push(new FormProperty('is-visible'  , true));
    this.properties.push(new FormProperty('is-deletable', true));

    /**
     * Add item to canvas
     *
     * @public
     * @this FormGroup
     * @param {integer} itemType
     * @param {integer} elementType
     * @param {integer} index
     * @param {object} templateData
     */
    this.addItem = function(itemType, elementType, index, templateData) {

        //prepare data
        var el = null;

        switch (itemType) {
            case this.ITEM_TYPE_LAYOUT:

                //we have default layout only - 1
                //TODO update to new layouts later
                var layout = new FormLayoutDefault();
                layout.formWiz = this.formWiz;
                el = layout;

                break;
            case this.ITEM_TYPE_ELEMENT:

                var layout  = new FormLayoutDefault();
                layout.formWiz = this.formWiz;
                var element = null;

                var fElement = new FormElement();
                switch (elementType) {
                    case fElement.ELEMENT_TYPE_TEXT:

                        element = new FormTextElement();
                        break;
                    case fElement.ELEMENT_TYPE_TEXTAREA:

                        element = new FormTextareaElement();
                        break;
                    case fElement.ELEMENT_TYPE_SELECT:

                        element = new FormSelectElement();
                        break;
                    case fElement.ELEMENT_TYPE_CHECKBOX:

                        element = new FormCheckboxgroupElement();
                        break;
                    case fElement.ELEMENT_TYPE_RADIO:

                        element = new FormRadiogroupElement();
                        break;
                    case fElement.ELEMENT_TYPE_HEADLINE:

                        element = new FormHeadlineElement();
                        break;
                    case fElement.ELEMENT_TYPE_EXTENDED_SELECT:

                        element = new FormExtendedSelectElement();
                        break;
                    case fElement.ELEMENT_TYPE_SEPARATOR:

                        element = new FormSeparatorElement();
                        break;
                    case fElement.ELEMENT_TYPE_MULTIPLE_SELECT:

                        element = new FormMultipleSelectElement();
                        break;
                    case fElement.ELEMENT_TYPE_NOTE_TEXT:

                        element = new FormNoteTextElement();
                        break;
                }

                element.formWiz = this.formWiz;
                if (templateData !== null && templateData !== undefined) {
                    element.setData(templateData);
                }

                layout.addItemToColumn(element, 0);
                el = layout;

                break;
        }

        if (el === null) return;

        el.index = index;
        this.data.splice(el.index, 0, el);

        this.refresh();
    };

    /**
     * Add item direct to data
     *
     * @public
     * @this FormGroup
     * @param {FormLayout} element
     */
    this.addItemToData = function(element) {

        if (!element.index) element.index = this.getItemsCount();
        element.formWiz = this.formWiz;
        this.data.splice(element.index, 0, element);
    };

    /**
     * Create html for bar button
     *
     * @public
     * @this FormGroup
     * @return {HTMLElement}
     */
    this.getBarButton = function() {

        var btn = $('<div>')
            .addClass('button group')
            .addClass(this.buttonClass)
            .attr('data-type', this.type)
            .html(this.buttonTitle);
        return btn;
    };

    /**
     * Get data from elements
     *
     * @public
     * @this ffbFormWizard
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('> .title > .label > input[name="groupLabel"]').first();

        if (label.length > 0) {
            this.label[this.locale] = label.val();
        }

        var props = [];
        for (var p = 0; p < this.properties.length; p++) {

            var prop = this.properties[p];
            switch (prop.key) {
                case 'is-mandatory':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="groupRequired"]').prop('checked')
                    });
                    break;
                case 'is-visible':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="groupVisible"]').prop('checked')
                    });
                    break;
            }

            props.push(prop.getData());
        }

        var group = {
            id         : this.id,
            data       : [],
            label      : this.label,
            properties : props
        };

        for (var l = 0; l < this.getItemsCount(); l++) {

            group.data.push(this.getItem(l).getData());
        }

        return group;
    };

    /**
     * Get items count
     *
     * @public
     * @this FormGroup
     * @return {integer} count
     */
    this.getItemsCount = function() {

        return this.data.length;
    };

    /**
     * Get item by index
     *
     * @public
     * @this FormGroup
     * @param {integer} index
     * @return {FormGroup|FormLayout|FormElement} item
     */
    this.getItem = function(index) {

        if (index > this.data.length) return null;
        return this.data[index];
    };

    /**
     * Get group area html
     *
     * @public
     * @this FormGroup
     * @return {HTMLElement} cnt
     */
    this.getAreaHTML = function() {

        var cnt = $('<div>')
            .addClass('area');

        //fill with data
        for (var i = 0; i < this.data.length; i++) {

            cnt.append(this.data[i].getHTML(this));
        }

        var self = this;        

        //init droppable for group area
        //http://api.jqueryui.com/droppable/
        cnt
            .droppable({disabled : true})
            .droppable('destroy')
            .droppable({
                accept      : '.button.layout, .button.element',
                addClasses  : false, //true
                greedy      : true, //false
                hoverClass  : 'hover', //false
                scope       : 'fwbuttons', //default
                tolerance   : 'pointer', //intersect, fit, pointer, touch
                drop : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
                    self.onDrop($(event.target), ui.draggable);
                },
                out : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                },
                over : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                }
            });

        return cnt;
    };

    /**
     * Create html element
     *
     * @public
     * @this FormGroup
     * @return {HTMLElement}
     */
    this.getHTML = function() {

        var self    = this;
        this.locale = this.formWiz.options.locale;

        //prepare html
        this.cnt = $('<div>')
            .addClass('group')
            .attr('data-type', this.type)
            .attr('data-index', this.index)
            .append(
                this.getTitleHTML()
            )
            .append(
                this.getAreaHTML()
            );

        //init DnD for group sort
        //http://api.jqueryui.com/draggable/
        this.cnt
            .draggable({
                addClasses        : false, //true
//                appendTo          : 'parent',
                axis              : 'y', //x, y
//                cancel            : "input,textarea,button,select,option",
//                connectToSortable : false,
                containment       : this.formWiz.cnt.find('.workspace'), //false
//                cursor            : 'auto',
//                cursorAt          : false, //{top, left, right, bottom}
//                delay             : 0,
//                disabled          : false,
//                distance          : 1,
//                grid              : false,
                handle            : '> .title > .move',
                helper            : 'original', //original, clone
//                iframeFix         : false,
                opacity           : 0.35,
                refreshPositions  : false,
                revert            : true,
                revertDuration    : 0,
                scope             : 'fwbuttons',
                scroll            : true,
                scrollSensitivity : 100, //20
//                scrollSpeed       : 20, //20
//                snap              : false,
//                snapMode          : 'both', //inner, outer, both
//                snapTolerance     : 20,
                stack             : 'groups',
                zIndex            : 101, //false
                drag              : function(event, ui) {//ui.helper, ui.position, ui.offset

                    self.onGroupDrag(event, ui);
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

        //init droppable for group
        //http://api.jqueryui.com/droppable/
        this.cnt
            .droppable({disabled : true})
            .droppable('destroy')
            .droppable({
                accept      : '.button, .group',
                addClasses  : false, //true
                greedy      : true, //false
                hoverClass  : 'hover', //false
                scope       : 'fwbuttons', //default
                tolerance   : 'pointer', //intersect, fit, pointer, touch
                drop : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset                    
                    self.onDrop($(event.target), ui.draggable);
                },
                out : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
    
                },
                over : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                }
            });

        return this.cnt;
    };

    /**
     * Get group title html
     *
     * @publi
     * @this FormGroup
     * @return {HTMLElement} cnt
     */
    this.getTitleHTML = function() {

        var visible  = null;
        var required = null;
        var remove   = null;
        var self     = this;

        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];

            if (prop.key === 'is-deletable' && (prop.value === true || prop.value === 1)) {

                //remove
                remove = $('<div>')
                    .addClass('remove')
                    .on('click', function() {

                        self.formWiz.getData();
                        self.formWiz.removeItem(parseInt($(this).parents('.group').first().attr('data-index')));
                        return false;
                    });
            }

            if (prop.key === 'is-visible') {
                //check is-visible
                visible = $('<div>')
                    .addClass('visible')
                    .append(
                        $('<label>')
                            .append(
                                $('<input>')
                                    .attr('name', 'groupVisible')
                                    .attr('type', 'checkbox')
                                    .attr('value', true)
                                    .prop('checked', prop.value)
                            ).append(ffbTranslator.translate('LBL_VISIBLE')
                    ));
            }

            if (prop.key === 'is-mandatory') {

                //check all
                required = $('<div>')
                    .addClass('required')
                    .append(
                        $('<label>')
                            .append(
                                $('<input>')
                                    .attr('name', 'groupRequired')
                                    .attr('type', 'checkbox')
                                    .attr('value', 'all')
                                    .prop('checked', prop.value)
                                    .on('change', function(e) {

                                        $(this).parents('.group').first()
                                            .find('.element .title .required input[name="elementRequired"]')
                                                .prop('checked', $(this).prop('checked'));
                                    })
                            )
                            .append(ffbTranslator.translate('LBL_REQUIRED')
                      ));
            }

        }

        var cnt = $('<div>')
            .addClass('title');

        //toggle
        var toggle = $('<div>')
            .addClass('toggle')
            .on('click', function(e) {

                var parent = $(this).parents('.group').first();

                if (parent.hasClass('closed')) {
                    parent.removeClass('closed');
                } else {
                    parent.addClass('closed');
                }
            });

        //title
        var labelString = '';
        if (typeof this.label === 'object' && this.label[this.locale] !== undefined) {
            labelString = this.label[this.locale];
        }
        var title = $('<div>')
            .addClass('label')
            .append(
                $('<input>')
                    .attr('name', 'groupLabel')
                    .attr('type', 'text')
                    .attr('value', labelString)
            );

        //move
        var move = $('<div>')
            .addClass('move');

        //remove, code is above
//        var remove = $('<div>')
//            .addClass('remove')
//            .on('click', function() {
//
//                self.formWiz.getData();
//                self.formWiz.removeItem(parseInt($(this).parents('.group').first().attr('data-index')));
//                return false;
//            });

        cnt.append(toggle);
        cnt.append(title);
        if (remove) cnt.append(remove);
        cnt.append(move);
        if (required) cnt.append(required);
        if (visible) cnt.append(visible);

        return cnt;
    };

    /**
     * Check elements with values in column
     *
     * @public
     * @this FormGroup
     * @param {array} items
     * @return {bool}
     */
    this.hasElementsWithData = function(items) {

        var fields      = this.formWiz.options.fieldHasData;
        var needConfirm = false;

        if (fields !== null && fields.length > 0) {

            for (var l = 0; l < items.length; l++) {

                var layout = items[l];

                // search in layout
                needConfirm = layout.hasElementsWithData(layout.getAllElements());
                if (needConfirm === true) {
                    return needConfirm;
                }
            }
        }

        return needConfirm;
    };

    /**
     * On Group drop
     *
     * @public
     * @this FormGroup
     * @param {HTMLElement} target
     * @param {HTMLElement} button
     */
    this.onDrop = function(target, button) {        

        //group move
        if (!button.hasClass('button') && button.hasClass('group') && target.hasClass('group')) {

            var index = parseInt(target.attr('data-index'));
            if (target.hasClass('after')) {
                index++;
            }

            this.getData();
            this.formWiz.replaceItems(parseInt($(button).attr('data-index')), index);
            return;
        }

        //group or element insert after or before
        if (button.hasClass('button') && (target.hasClass('before') || target.hasClass('after'))) {

            this.formWiz.onDrop(target, button);
            return;
        }

        var itemType = null;

        //prepare data for addItem
        if (button.hasClass('layout')) {
            //TODO Update to constants
            itemType = 2;
        }
        if (button.hasClass('element')) {
            itemType = 3;
        }

        if (itemType === null) return;

        var tData   = null;
        var subtype = parseInt($(button).attr('data-type'));

        //prepare index
        if (target.hasClass('area')) {
            var index = this.getItemsCount();
        }

        //check for template
        if (button.hasClass('template') && button.attr('data-template') !== undefined) {

            //parse Json
            tData   = this.parseJSON(button.attr('data-template'))[0];
            subtype = tData.type;
        }

        this.formWiz.getData();
        this.addItem(itemType, subtype, index, tData);
    };

    /**
     * On Button drag
     *
     * @public
     * @this FormGroup
     * @param {Event} event
     * @param {object} ui
     */
    this.onGroupDrag = function(event, ui) {

        var hovers = this.cnt.parent().find('.hover');
        if (hovers.length === 0) return;

        // get last hovered
        var hovered = hovers.last();

        // clear all
        this.cnt.parent().find('.hover, .before, .after').removeClass('hover before after');

        // set current as hovered
        hovered.addClass('hover');

        // check hovered type
        if (hovered.hasClass('group') && hovered.length === 1) {

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
     * Parse Json
     *
     * @public
     * @this FormGroup
     * @param {string} json
     * @return {null|object} result
     */
    this.parseJSON = function(json) {

        var result;
        if (json.length === 0) return null;

        try {
            result = $.parseJSON(json);
        } catch(e) {
            result = null;
        }
        return result;
    };

    /**
     * Refresh layout indexes, render layouts
     *
     * @public
     * @this FormGroup
     */
    this.refresh = function() {

        // remove all existed hover classes
        $('.hover, .after, .before').removeClass('hover after before');

        //update indexes
        for (var i = 0; i < this.getItemsCount(); i++) {

            this.getItem(i).index = i;

            if (this.getItem(i).cnt) {

                var oldCnt = this.getItem(i).cnt;
                oldCnt.replaceWith(this.getItem(i).getHTML(this));
            } else {

                var html = this.getItem(i).getHTML(this);
                if (i > 0) {
                    this.getItem(i - 1).cnt.after(html);
                } else if (i === 0 && this.getItemsCount() > 1 && this.getItem(1).cnt !== null) {
                    this.getItem(1).cnt.before(html);
                } else {
                    this.cnt.find('.area').append(html);
                }
            }
        }
    };

    /**
     * Remove item from data
     *
     * @public
     * @this FormGroup
     * @param {integer} index
     */
    this.removeItem = function(index) {

        // check Ids with data to confirm removing
        var layout      = this.getItem(index);
        var needConfirm = this.hasElementsWithData([layout]);

        if (needConfirm) {

            var self = this;

            // show confirmation
            ffbLightbox.showModal({
                'title'    : ffbTranslator.translate('TTL_CONFIRM_SUBMITTING_CHANGES'),
                'text'     : '<p>' + ffbTranslator.translate('MSG_FORMWIZ_SAVED_DATA_BY_REMOVE_FIELDS') + '</p>',
                'okAction' : {
                    'caption'  : ffbTranslator.translate('BTN_OK'),
                    'callBack' : function () {

                        // remove field
                        layout.cnt.remove();
                        self.data.splice(index, 1);
                        self.refresh();

                        ffbLightbox.close();
                    }
                },
                'cancelAction': {
                    'caption': ffbTranslator.translate('BTN_CANCEL')
                }
            });

        } else  {

            // just remove
            layout.cnt.remove();
            this.data.splice(index, 1);
            this.refresh();
        }
    };

    /**
     * Set data
     *
     * @public
     * @this FormGroup
     * @param {object} groupData
     * @param {integer} index
     */
    this.setData = function(groupData, index) {

        this.id    = groupData.id;
        this.label = groupData.label;
        this.index = index;
//        this.properties =

        for (var l = 0; l < groupData.data.length; l++) {

            //TODO Add check by layout type
            var layoutData = groupData.data[l];
            var layout = new FormLayoutDefault();
            layout.formWiz = this.formWiz;
            layout.setData(layoutData, l);

            //add group to data
            this.data.splice(layout.index, 0, layout);
        }

        if (groupData.properties !== undefined) {

            for (var p = 0; p < groupData.properties.length; p++) {

                var propertyData = groupData.properties[p];

                //check if property exist
                var exist = false;
                for (var k = 0; k < this.properties.length; k++) {
                    if (this.properties[k].key === propertyData.key) {
                        this.properties[k].value = propertyData.value;
                        exist = true;
                    }
                }

                if (!exist) {
                    var property = new FormProperty();
                    property.setData(propertyData);

                    //add option to data
                    this.properties.push(property);
                }
            }
        }

    };
};

/**
 * Form Layout
 *
 * @this FormLayout
 */
var FormLayout = function() {

    this.ITEM_TYPE_GROUP   = 1;
    this.ITEM_TYPE_LAYOUT  = 2;
    this.ITEM_TYPE_ELEMENT = 3;
    this.LAYOUT_TYPE_DEFAULT = 1;

    this.buttonClass    = null;
    this.buttonTitle    = 'BTN_LAYOUT_DEFAULT';
    this.cnt            = null;
    this.columnsCount   = 0;
    this.columnsClasses = [];
    this.data           = [];
    this.formWiz        = null;
    this.id             = null;
    this.index          = null;     //layout index in array
    this.locale         = null;
    this.type           = null;
    this.properties     = null;

    /**
     * Add item to layout column
     *
     * @public
     * @this FormLayout
     * @param {integer} itemType
     * @param {integer} elementType
     * @param {integer} columnIndex
     * @param {integer} index
     * @param {object} templateData
     */
    this.addItem = function(itemType, elementType, columnIndex, index, templateData) {

        //prepare data
        var el = null;

        switch (itemType) {
            case this.ITEM_TYPE_ELEMENT:

                var fElement = new FormElement();
                switch (elementType) {
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

                break;
        }

        if (el === null) return;

        el.formWiz = this.formWiz;
        if (templateData !== null && templateData !== undefined) {
            el.setData(templateData);
        }

        el.index = index;
        this.data[columnIndex].splice(el.index, 0, el);

        this.refresh();
    };

    /**
     * Add item to column data
     *
     * @public
     * @this FormLayout
     * @param {FormElement} element
     * @param {integer} column
     */
    this.addItemToColumn = function(element, column) {

        element.index = this.getItemsCount(column);
        element.formWiz = this.formWiz;
        this.data[column].splice(element.index, 0, element);
    };

    /**
     * Get all Elemets from layout
     *
     * @public
     * @this FormLayout
     * @return [{HTMLElement}]
     */
    this.getAllElements = function() {

        var ids = [];

        for (var i = 0; i < this.columnsCount; i++) {
            for (var j = 0; j < this.getItemsCount(i); j++)  {

                ids.push(this.getItem(j, i));
            }
        }

        return ids;
    };

    /**
     * Create html for bar button
     *
     * @public
     * @this FormLayout
     * @return {HTMLElement}
     */
    this.getBarButton = function() {

        var btn = $('<div>')
            .addClass('button layout')
            .addClass(this.buttonClass)
            .attr('data-type', this.type)
            .html(this.buttonTitle);
        return btn;
    };

    /**
     * Get data from elements
     *
     * @public
     * @this FormLayout
     * @return {object} data
     */
    this.getData = function() {

        var layout = {
            id    : this.id,
            data  : [],
            type  : this.type
        };

        for (var c = 0; c < this.data.length; c++) {

            layout.data.push([]);

            for (var i = 0; i < this.getItemsCount(c); i++) {

                layout.data[c].push(this.getItem(i, c).getData());
            }
        }

        return layout;
    };

    /**
     * Get items count
     *
     * @public
     * @this FormLayout
     * @param {integer} column
     * @return {integer} count
     */
    this.getItemsCount = function(column) {

        return this.data[column].length;
    };

    /**
     * Get item by index
     *
     * @public
     * @this FormLayout
     * @param {integer} index
     * @param {integer} column
     * @return {FormGroup|FormLayout|FormElement} item
     */
    this.getItem = function(index, column) {

        if (index > this.data[column].length) return null;
        return this.data[column][index];
    };

    /**
     * Create columns HTML
     *
     * @public
     * @this FormLayout
     * @return {HTMLElement} cnt
     */
    this.getColumnsHTML = function() {

        var self = this;

        //prepare object
        this.cnt.append(
            $('<div>')
                .addClass('columns')
        );

        //create columns
        for (var i = 0; i < this.columnsCount; i++) {

            //create column object, add class if exist
            var column = $('<div>')
                .addClass('column')
                .attr('data-index', i);
            if (this.columnsClasses[i] !== undefined) column.addClass(this.columnsClasses[i]);

            //add html from elements
            for (var k = 0; k < this.data[i].length; k++) {

                column.append(this.data[i][k].getHTML(this));
            }

            //init droppable for layout
            //http://api.jqueryui.com/droppable/
            this.cnt
                .droppable({disabled : true})
                .droppable('destroy')
                .droppable({
                    accept      : '.button.element',
                    addClasses  : false, //true
                    greedy      : true, //false
                    hoverClass  : 'hover', //false
                    scope       : 'fwbuttons', //default
                    tolerance   : 'pointer', //intersect, fit, pointer, touch
                    drop : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
                        self.onDrop($(event.target), ui.draggable);
                    },
                    out : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                    },
                    over : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                    }
                });

            this.cnt.find('> .columns').append(column);
        }
    };

    /**
     * Get group title html
     *
     * @publi
     * @this FormLayout
     * @param {FormGroup} group
     * @return {HTMLElement} cnt
     */
    this.getTitleHTML = function(group) {

        var self = this;
        var cnt = $('<div>')
            .addClass('title');

        //title
        var labelString = '';
        if (typeof this.label === 'object' && this.label[this.locale] !== undefined) {
            labelString = this.label[this.locale];
        }
        var title = $('<div>')
            .addClass('label')
            .html(labelString);

        //move
        var move = $('<div>')
            .addClass('move');

        //remove
        var remove = $('<div>')
            .addClass('remove')
            .on('click', function(e) {

                self.formWiz.getData();
                group.removeItem(parseInt($(this).parents('.layout').first().attr('data-index')));
            });

        cnt.append(title);
        cnt.append(remove);
        cnt.append(move);

        return cnt;
    };

    /**
     * Create html element
     *
     * @public
     * @this FormLayout
     * @param {FormGroup} group
     * @return {HTMLElement}
     */
    this.getHTML = function(group) {

        this.locale = group.locale;

        //prepare html
        this.cnt = $('<div>')
            .addClass('layout')
            .attr('data-type', this.type)
            .attr('data-index', this.index)
            .append(
                this.getTitleHTML(group)
            );
        this.getColumnsHTML();

        return this.cnt;
    };

    /**
     * Check elements with values in column
     *
     * @public
     * @this FormLayout
     * @param {array} items
     * @return {bool}
     */
    this.hasElementsWithData = function(items) {

        var fields      = this.formWiz.options.fieldHasData;
        var needConfirm = false;

        if (fields !== null && fields.length > 0) {


            // check item id
            for (var i = 0; i < items.length; i++) {
                if ($.inArray(items[i].id, fields) >= 0) {

                    needConfirm = true;
                    break;
                }
            }
        }

        return needConfirm;
    };

    /**
     * On Element drop
     *
     * @public
     * @this FormLayout
     * @param {HTMLElement} target
     * @param {HTMLElement} button
     */
    this.onDrop = function(target, button) {        

        if (!button.hasClass('button') && button.hasClass('element') && target.hasClass('element')) {

            //element move
            var index = parseInt(target.attr('data-index'));
            if (target.hasClass('after')) {
                index++;
            }

            this.replaceItems(
                parseInt($(button).attr('data-index')),
                parseInt($(button).parents('.column').first().attr('data-index')),
                index,
                parseInt(target.parents('.column').first().attr('data-index'))
            );
            return;
        }

        var itemType = null;

        //prepare data for addItem
        if (button.hasClass('element')) {
            itemType = 3;
        }

        if (itemType === null) return;

        var tData   = null,
            subtype = parseInt(button.attr('data-type')),
            index   = null,
            columnIndex = null;

        //prepare index
        if (target.hasClass('column')) {
            columnIndex = parseInt(target.attr('data-index'));
            index = this.getItemsCount(columnIndex);
        } else if (target.hasClass('before')) {
            columnIndex = parseInt(target.parents('.column').first().attr('data-index'));
            index = parseInt(target.attr('data-index'));
        } else {
            columnIndex = parseInt(target.parents('.column').first().attr('data-index'));
            index = parseInt(target.attr('data-index')) + 1;
        }

        //check indexes
        if (!columnIndex) {
            columnIndex = 0;
        }
        if (!index) {
            index = 0;
        }

        //check for template
        if (button.hasClass('template') && button.attr('data-template') !== undefined) {

            //parse Json
            tData   = this.parseJSON(button.attr('data-template'))[0];
            subtype = tData.type;
        }

        this.formWiz.getData();
        this.addItem(itemType, subtype, columnIndex, index, tData);
    };

    /**
     * Parse Json
     *
     * @public
     * @this FormGroup
     * @param {string} json
     * @return {null|object} result
     */
    this.parseJSON = function(json) {

        var result;
        if (json.length === 0) return null;

        try {
            result = $.parseJSON(json);
        } catch(e) {
            result = null;
        }
        return result;
    };

    /**
     * Replace items
     *
     * @public
     * @this FormLayout
     * @param {integer} draggableIndex
     * @param {integer} draggableColumn
     * @param {integer} targetIndex
     * @param {integer} targetColumn
     */
    this.replaceItems = function(draggableIndex, draggableColumn, targetIndex, targetColumn) {

        this.formWiz.getData();

        var temp = $('<div>');
        temp.insertAfter(this.getItem(draggableIndex, draggableColumn).cnt);
        this.getItem(draggableIndex, draggableColumn).cnt.insertAfter(this.getItem(targetIndex, targetColumn).cnt);
        this.getItem(targetIndex, targetColumn).cnt.insertAfter(temp);
        temp.remove();

        var itemA = this.getItem(draggableIndex, draggableColumn);
        var itemB = this.getItem(targetIndex, targetColumn);

        itemA.index = targetIndex;
        itemB.index = draggableIndex;

        this.data[targetColumn][targetIndex] = itemA;
        this.data[targetColumn][draggableIndex] = itemB;

        this.refresh();
    };

    /**
     * Refresh columns index, render columns
     *
     * @public
     * @this FormLayout
     */
    this.refresh = function() {

        // remove all existed hover classes
        $('.hover, .after, .before').removeClass('hover after before');

        //update indexes
        for (var c = 0; c < this.data.length; c++)  {

            for (var i = 0; i < this.getItemsCount(c); i++) {

                //update indexes
                this.getItem(i, c).index = i;

                if (this.getItem(i, c).cnt) {

                    var oldCnt = this.getItem(i, c).cnt;
                    oldCnt.replaceWith(this.getItem(i, c).getHTML(this));
                } else {

                    var html = this.getItem(i, c).getHTML(this);
                    if (i > 0) {
                        this.getItem(i - 1, c).cnt.after(html);
                    } else if (i === 0 && this.getItemsCount(c) > 1 && this.getItem(1, c).cnt !== null) {
                        this.getItem(1, c).cnt.before(html);
                    } else {
                        this.cnt.find('.column[data-index="' + c + '"]').append(html);
                    }
                }
            }
        }
    };

    /**
     * Remove item from data
     *
     * @public
     * @this FormLayout
     * @param {integer} index
     * @param {integer} column
     */
    this.removeItem = function(index, column) {

        // check Ids with data to confirm removing
        var element     = this.getItem(index, column);
        var needConfirm = this.hasElementsWithData([element]);

        if (needConfirm) {

            var self = this;

            // show confirmation
            ffbLightbox.showModal({
                'title'    : ffbTranslator.translate('TTL_CONFIRM_SUBMITTING_CHANGES'),
                'text'     : '<p>' + ffbTranslator.translate('MSG_FORMWIZ_SAVED_DATA_BY_REMOVE_FIELDS') + '</p>',
                'okAction' : {
                    'caption'  : ffbTranslator.translate('BTN_OK'),
                    'callBack' : function () {

                        // remove field
                        element.cnt.remove();
                        self.data[column].splice(index, 1);
                        self.refresh();

                        ffbLightbox.close();
                    }
                },
                'cancelAction': {
                    'caption': ffbTranslator.translate('BTN_CANCEL')
                }
            });

        } else  {

            // just remove
            element.cnt.remove();
            this.data[column].splice(index, 1);
            this.refresh();
        }
    };

    /**
     * Set data
     *
     * @public
     * @this FormLayout
     * @param {object} layoutData
     * @param {integer} index
     */
    this.setData = function(layoutData, index) {

        this.id    = layoutData.id;
        //this.label = layoutData.label;
        this.index = index;
        //this.type  = layoutData.type; //in GroupInit, in TODO

        for (var c = 0; c < layoutData.data.length; c++) {

            var columnData = layoutData.data[c];
            for (var e = 0; e < columnData.length; e++) {

                var elementData = columnData[e];
                var el          = null;
                var fElement    = new FormElement();

                switch (parseInt(elementData.type)) {
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
                el.formWiz = this.formWiz;
                el.setData(elementData, e);

                //add group to data
                if (el) {
                    this.data[c].splice(el.index, 0, el);
                }
            }
        }
    };
};

/**
 * Form Element
 *
 * @this FormElement
 */
var FormElement = function() {

    this.ELEMENT_TYPE_TEXT     = 1;
    this.ELEMENT_TYPE_TEXTAREA = 2;
    this.ELEMENT_TYPE_SELECT   = 3;
    this.ELEMENT_TYPE_CHECKBOX = 4;
    this.ELEMENT_TYPE_RADIO    = 5;
    this.ELEMENT_TYPE_HEADLINE = 6;
    this.ELEMENT_TYPE_EXTENDED_SELECT = 7;
    this.ELEMENT_TYPE_SEPARATOR = 8;
    this.ELEMENT_TYPE_MULTIPLE_SELECT = 9;
    this.ELEMENT_TYPE_NOTE_TEXT = 10;

    this.buttonClass = null;
    this.buttonTitle = 'BTN_FORM_ELEMENT';
    this.cnt         = null;
    this.className   = null;
    this.formWiz     = null;
    this.id          = null;
    this.index       = null;
    this.label       = {};
    this.locale      = null;
    this.name        = null;
    this.options     = [];
    this.properties  = [];
    this.type        = null;
    this.value       = {};
    this.settings    = {
        setting1 : null,
        setting2 : null,
        setting3 : null,
        setting4 : null
    };

    //set default properties
    this.properties.push(new FormProperty('is-mandatory', false));
    this.properties.push(new FormProperty('is-editable' , true));
    this.properties.push(new FormProperty('is-visible'  , true));
    this.properties.push(new FormProperty('is-deletable', true));

    /**
     * Add Option to options
     *
     * @public
     * @this Form Element
     */
    this.addOption = function() {

        this.formWiz.getData();
        var option = new FormOption();
        option.formWiz = this.formWiz;
        this.options.splice(this.options.length, 0, option);
        this.refreshOptions();
    };

    /**
     * Create html for bar button
     *
     * @public
     * @this FormElement
     * @return {HTMLElement}
     */
    this.getBarButton = function() {

        var btn = $('<div>')
            .addClass('button element')
            .addClass(this.buttonClass)
            .attr('data-type', this.type)
            .html(this.buttonTitle);
        return btn;
    };

    /**
     * Get data from elements
     *
     * @public
     * @this FormElement
     * @return {object} data
     */
    this.getData = function() {

        var element = {
            id         : this.id,
            label      : this.label,
            options    : this.options,
            properties : this.properties,
            type       : this.type,
            value      : this.value,
            settings   : this.settings
        };

        return element;
    };

    /**
     * Get options HTML
     *
     * @public
     * @this FormElement
     * @return {HTMLElement}
     */
    this.getOptionsHTML = function() {

        var self = this;

        //options
        var options = $('<div>')
            .addClass('options');

        //add options buttons
        var row = $('<div>')
            .addClass('row');
        var input = $('<input>')
            .addClass('button gray plus')
            .attr('name', this.name + 'addOption')
            .attr('type', 'button')
            .attr('value', ffbTranslator.translate('BTN_ADD_OPTION'))
            .on('click', function(e) {

                self.addOption();
            });
        row.append(input);
        options.append(row);

        return options;
    };

    /**
     * Create html element wrapper
     *
     * @public
     * @this FormElement
     * @param {FormLayout} layout
     * @return {HTMLElement}
     */
    this.getWrapperHTML = function(layout) {

        this.locale = layout.locale;

        var cnt = $('<div>')
            .addClass('element')
            .addClass('type-' + this.type)
            .attr('data-index', this.index)
            .append(this.getTitleHTML(layout));

        if (this.className) {
            cnt.addClass(this.className);
        }

        //init droppable for element
        //http://api.jqueryui.com/droppable/
        cnt
            .droppable({disabled : true})
            .droppable('destroy')
            .droppable({
                accept      : '.element',
                addClasses  : false, //true
                greedy      : true, //false
                hoverClass  : 'hover', //false
                scope       : 'fwbuttons', //default
                tolerance   : 'pointer', //intersect, fit, pointer, touch
                drop : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
                    layout.onDrop($(event.target), ui.draggable);
                },
                out : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
    
                },
                over : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                }
            });

        return cnt;
    };

    /**
     * Get group title html
     *
     * @publi
     * @this FormElement
     * @param {FormLayout} layout
     * @return {HTMLElement} cnt
     */
    this.getTitleHTML = function(layout) {

        var visible     = null;
        var required    = null;
        var remove      = null;
        var self        = this;

        //title
        var title = $('<div>')
            .addClass('label')
            .html(this.buttonTitle);

        var deleteable = false;

        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];

            if (prop.key === 'is-deletable' && (prop.value === true || prop.value === 1)) {

                //remove
                remove = $('<div>')
                    .addClass('remove')
                    .on('click', function(e) {

                        self.formWiz.getData();
                        var parents = $(this).parents('.element, .column');
                        var index   = parseInt($(parents[0]).attr('data-index'));
                        var column  = parseInt($(parents[1]).attr('data-index'));
                        layout.removeItem(index, column);
                    });
            }

            if (prop.key === 'is-mandatory') {

                //check is-mandatory
                var required = $('<div>')
                    .addClass('required')
                    .append(
                        $('<label>')
                            .append(
                                $('<input>')
                                    .attr('name', 'elementRequired')
                                    .attr('type', 'checkbox')
                                    .attr('value', true)
                                    .prop('checked', prop.value)
                            )
                            .append(ffbTranslator.translate('LBL_REQUIRED')
                        )

                    );
            }

            if (prop.key === 'is-visible') {
                //check is-visible
                visible = $('<div>')
                    .addClass('visible')
                    .append(
                        $('<label>')
                            .append(
                                $('<input>')
                                    .attr('name', 'elementVisible')
                                    .attr('type', 'checkbox')
                                    .attr('value', true)
                                    .prop('checked', prop.value)
                            )
                            .append(ffbTranslator.translate('LBL_VISIBLE')
                            )

                    );
            }
        }

        var cnt = $('<div>')
            .addClass('title');

        //title
        var title = $('<div>')
            .addClass('label')
            .html(this.buttonTitle);

        //move
        var move = $('<div>')
            .addClass('move');

        cnt.append(title);
        if (remove) cnt.append(remove);
        cnt.append(move);
        if (required) cnt.append(required);
        if (visible) cnt.append(visible);

        return cnt;
    };    

    /**
     * Init drag and drop for elements in layout
     *
     * @public
     * @this FormElement
     * @param {FormLayout} layout
     * @return {HTMLElement}
     */
    this.initDnD = function(layout) {

        var self = this;

        //init DnD for elements sort
        //http://api.jqueryui.com/draggable/
        this.cnt
            .draggable({
                addClasses        : false, //true
//                appendTo          : 'parent',
                axis              : 'y', //x, y
//                cancel            : "input,textarea,button,select,option",
//                connectToSortable : false,
                containment       : layout.cnt.find('> .columns'), //false
//                cursor            : 'auto',
//                cursorAt          : false, //{top, left, right, bottom}
//                delay             : 0,
//                disabled          : false,
//                distance          : 1,
//                grid              : false,
                handle            : '> .title > .move',
                helper            : 'original', //original, clone
//                iframeFix         : false,
                opacity           : 0.35,
                refreshPositions  : false,
                revert            : true,
                revertDuration    : 0,
                scope             : 'fwbuttons',
                scroll            : true,
                scrollSensitivity : 100, //20
//                scrollSpeed       : 20, //20
//                snap              : false,
//                snapMode          : 'both', //inner, outer, both
//                snapTolerance     : 20,
                stack             : 'elementssort',
                zIndex            : 101, //false
                drag              : function(event, ui) {//ui.helper, ui.position, ui.offset

                    self.onElementDrag(event, ui);
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
    };

    /**
     * On Element drag
     *
     * @public
     * @this FormElement
     * @param {Event} event
     * @param {object} ui
     */
    this.onElementDrag = function(event, ui) {

        var hovers = this.cnt.parent().find('.hover');
        if (hovers.length === 0) return;

        // get last hovered
        var hovered = hovers.last();

        // clear all
        this.cnt.parent().find('.hover, .before, .after').removeClass('hover before after');

        // set current as hovered
        hovered.addClass('hover');

        // check hovered type
        if (hovered.hasClass('element') && hovered.length === 1) {

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
     * Refresh options
     *
     * @public
     * @this FormElement
     */
    this.refreshOptions = function() {

        //update indexes
        for (var i = 0; i < this.options.length; i++) {

            this.options[i].index = i;

            if (this.options[i].cnt) {
                this.options[i].cnt.remove();
                //this.options[i].refresh(this);
            }

            var html = this.options[i].getHTML(this);
            this.cnt.find('> .options > .row').last().before(html);

        }
    };

    /**
     * Remove option
     *
     * @public
     * @this FormElement
     * @param {integer} index
     */
    this.removeOption = function(index) {

        this.formWiz.getData();
        this.options[index].cnt.remove();
        this.options.splice(index, 1);
        this.refreshOptions();
    };

    /**
     * Set data
     *
     * @public
     * @this FormLayout
     * @param {object} elementData
     * @param {integer} index
     */
    this.setData = function(elementData, index) {

        this.id       = elementData.id;
        this.label    = elementData.label;
        this.index    = index;
        this.value    = elementData.value;
        this.settings = elementData.settings;

        if (elementData.options !== undefined) {

            for (var o = 0; o < elementData.options.length; o++) {

                var optionData = elementData.options[o];
                var option     = new FormOption();
                option.formWiz = this.formWiz;
                option.setData(optionData, o);

                //add option to data
                this.options.splice(option.index, 0, option);
            }
        }

        if (elementData.properties !== undefined) {

            for (var p = 0; p < elementData.properties.length; p++) {

                var propertyData = elementData.properties[p];

                //check if property exist
                var exist = false;
                for (var k = 0; k < this.properties.length; k++) {
                    if (this.properties[k].key === propertyData.key) {
                        this.properties[k].value = propertyData.value;
                        exist = true;
                    }
                }

                if (!exist) {
                    var property = new FormProperty();
                    property.setData(propertyData);

                    //add option to data
                    this.properties.push(property);
                }
            }
        }
    };
};

/**
 * Create html element
 *
 * @public
 * @this FormElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormElement.prototype.getHTML = function(layout) {

    this.locale = layout.locale;

    //get wrapper with title and controls
    this.cnt = this.getWrapperHTML(layout);

    //init DnD in layout
    this.initDnD(layout);

    return this.cnt;
};

/**
 * Form Option
 *
 * @this FormOption
 */
var FormOption = function() {

    this.cnt        = null;
    this.formWiz    = null;
    this.id         = null;
    this.index      = null;
    this.label      = {};
    this.locale     = null;
    this.name       = 'option' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.value      = null;
    this.properties = [];

    //set default properties
    this.properties.push(new FormProperty('is-mandatory', false));
    this.properties.push(new FormProperty('is-editable' , true));
    this.properties.push(new FormProperty('is-visible'  , true));
    this.properties.push(new FormProperty('is-deletable', true));
    this.properties.push(new FormProperty('has-comment', false));
    this.properties.push(new FormProperty('has-amount', ''));
    this.properties.push(new FormProperty('has-price', ''));

    /**
     * Get data from option
     *
     * @public
     * @this FormOption
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('input[name="' + this.name + 'option"]').val().trim();

        this.label[this.locale] = label;

        //check properties
        var props = [];
        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];
            switch (prop.key) {
                case 'is-visible':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="optionVisible"]').prop('checked')
                    });
                    break;
            }

            props.push(prop.getData());
        }

        var option = {
            id         : this.id,
            label      : this.label,
            properties : props,
            value      : this.value
        };

        return option;
    };

    /**
     * Create html element
     *
     * @public
     * @this FormOption
     * @param {HTMLElement} element
     * @return {HTMLElement}
     */
    this.getHTML = function(element) {

        this.locale = element.locale;

        var remove  = null;
        var visible = null;

        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];

            if (prop.key === 'is-visible') {

                //check is-visible
                visible = $('<div>')
                    .addClass('visible')
                    .append(
                        $('<label>')
                            .append(
                                $('<input>')
                                    .attr('name', 'optionVisible')
                                    .attr('type', 'checkbox')
                                    .attr('value', true)
                                    .prop('checked', prop.value)
                            )
                            .append(ffbTranslator.translate('LBL_VISIBLE')
                            )

                    );
            }

            if (prop.key === 'is-deletable' && (prop.value === true || prop.value === 1)) {

                remove = $('<div>')
                    .addClass('remove')
                    .on('click', function() {

                        element.removeOption(parseInt($(this).parents('.row').first().attr('data-index')));
                        return false;
                    });
            }
        }

        //inputs for label
        this.cnt = $('<div>')
            .attr('data-index', this.index)
            .addClass('row');
        var label = $('<label>')
            .attr('for', this.name + 'option')
            .html(ffbTranslator.translate('LBL_ELEMENT_OPTION') + ' ' + (this.index + 1) + ':');

        var labelString = '';
        if (typeof this.label === 'object' && this.label[this.locale] !== undefined) {
            labelString = this.label[this.locale];
        }
        var input = $('<input>')
            .attr('name', this.name + 'option')
            .attr('type', 'text')
            .attr('value', labelString);
        var remove = $('<div>')
            .addClass('remove')
            .on('click', function() {

                element.removeOption(parseInt($(this).parents('.row').first().attr('data-index')));
                return false;
            });
        this.cnt.append(label);
        this.cnt.append(input);
        if (visible) this.cnt.append(visible);
        if (remove) this.cnt.append(remove);

        return this.cnt;
    };

    /**
     * Refresh option html
     *
     * @public
     * @this FormOption
     * @param {object} element
     */
    this.refresh = function(element) {

//        var oldCnt = this.cnt;
//        oldCnt.replaceWith(this.getHTML(element));

//        this.cnt.find('> label').first().html(
//            ffbTranslator.translate('LBL_ELEMENT_OPTION') + ' ' + (this.index + 1) + ':'
//        );
//        this.cnt.attr('data-index', this.index);
    };

    /**
     * Set data
     *
     * @public
     * @this FormOption
     * @param {object} optionData
     * @param {integer} index
     */
    this.setData = function(optionData, index) {

        this.id    = optionData.id;
        this.label = optionData.label;
        this.index = index;
        this.value = optionData.value;

        if (optionData.properties !== undefined) {

            this.setProperties(optionData.properties);
        }
    };

    /**
     * Set options
     *
     * @public
     * @this FormOption
     * @param {object} optionData
     * @param {integer} index
     */
    this.setProperties = function(propertiesData) {

        for (var p = 0; p < propertiesData.length; p++) {

            var propertyData = propertiesData[p];

            //check if property exist
            var exist = false;
            for (var k = 0; k < this.properties.length; k++) {
                if (this.properties[k].key === propertyData.key) {
                    this.properties[k].value = propertyData.value;
                    exist = true;
                }
            }

            if (!exist) {
                var property = new FormProperty();
                property.setData(propertyData);

                //add option to data
                this.properties.push(property);
            }
        }
    };
};

/**
 * Form Property
 *
 * @this FormProperty
 * @param {string} key
 * @param {string} value
 */
var FormProperty = function(key, value) {

    this.key   = key !== undefined ? key:null;
    this.value = value !== undefined ? value:null;

    /**
     * Get data
     *
     * @public
     * @this FormProperty
     * @return {object} data
     */
    this.getData = function() {

        return {
            key   : this.key,
            value : this.value
        };
    };

    /**
     * Set data
     *
     * @public
     * @this FormProperty
     * @param {object} propertyData
     */
    this.setData = function(propertyData) {

        this.key   = propertyData.key;
        this.value = propertyData.value;
    };
};

/**
 * Form Template
 *
 * @this FormTemplate
 */
var FormTemplate = function() {

    this.TEMPLATE_TYPE_ELEMENT = 1;
    this.TEMPLATE_TYPE_GROUP   = 2;

    this.data    = [];
    this.index   = null;
    this.label   = null;
    this.type    = null;

    /**
     * Create html for bar button
     *
     * @public
     * @this FormTemplate
     * @return {HTMLElement}
     */
    this.getBarButton = function() {

        var btn = $('<div>')
            .addClass('button template')
            .attr('data-index', this.index)
            .attr('data-template', JSON.stringify(this.data))
            .html(this.label);
        if (this.type === this.TEMPLATE_TYPE_ELEMENT) {
            btn.addClass('element');
        }
        if (this.type === this.TEMPLATE_TYPE_GROUP) {
            btn.addClass('group');
        }
        return btn;
    };

    /**
     * Set data
     *
     * @public
     * @this FormTemplate
     * @param {object} templateData
     */
    this.setData = function(templateData) {

        this.data  = templateData.data;
        this.index = templateData.index;
        this.label = templateData.label;
        this.type  = templateData.type;
    };
};
