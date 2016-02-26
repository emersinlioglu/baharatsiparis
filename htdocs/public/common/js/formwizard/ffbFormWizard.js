/*jshint -W117 */
"use strict";

/**
 * ---=== Form Wizard ===---
 */

/**
 * Form wizard
 *
 * @this ffbFormWizard
 * @param {HTMLElement} container
 */
var ffbFormWizard = function(container) {

    this.ITEM_TYPE_GROUP   = 1;
    this.ITEM_TYPE_LAYOUT  = 2;
    this.ITEM_TYPE_ELEMENT = 3;

    this.cnt       = null;
    this.data      = [];
    this.options   = {
        'defValues'       : null,
        'locale'          : null,
        'locales'         : null,
        'maxGroupsCount'  : null,
        'translationSuff' : null,
        'fieldHasData'    : null,
        'tmseventparts'   : null
    };
    this.plugins   = {
        elements : [],
        groups   : [],
        layouts  : []
    };
    this.templates = {
        elements : [],
        groups   : [],
        layouts  : []
    };

    /**
     * Init options logic if exist
     *
     * @private
     * @this ffbFormWizard
     */
    var _initOptions = function() {

        //don't show hover effect for group, don't allow add more as 1 group
        if (this.options.maxGroupsCount) {
            this.cnt.addClass('max-groups-on');
        }
    };

    /**
     * Parse data from json to elements
     *
     * @private
     * @this ffbFormWizard
     * @param {object} unparsedData
     */
    var _parseData = function(unparsedData) {

        var data = [];
        if (unparsedData === undefined) {
            return data;
        }

        for (var g = 0; g < unparsedData.length; g++) {

            var groupData = unparsedData[g];
            var group = new FormGroupDefault();
            group.formWiz = this;
            group.setData(groupData, g);

            //add group to data
            data.splice(group.index, 0, group);
        }

        return data;
    };

    /**
     * Parse options from data attributes
     *
     * @private
     * @this ffbFormWizard
     */
    var _parseOptions = function() {

        //parse options
        var dataOptions = this.cnt.attr('data-options');
        if (dataOptions !== undefined) {

            //parse Json
            var options = this.parseJSON(dataOptions);
            if (options) {

                for (var key in options) {
                    if (!options.hasOwnProperty(key)) continue;

                    //Set properties
                    switch (key) {
                        case 'data':
                        case 'plugins':
                        case 'templates':

                            this[key] = options[key];
                            break;
                        case 'options':

                            for (var k in this.options) {
                                if (!this.options.hasOwnProperty(k)) continue;

                                if (options.options[k] !== undefined) {
                                    this.options[k] = options.options[k];
                                }
                            }
                            break;
                    }
                }
            }
        }
    };

    /**
     * Parse templates
     *
     * @private
     * @this ffbFormWizard
     */
    var _parseTemplates = function() {

        if (this.templates.elements.length > 0) {

            for (var t = 0; t < this.templates.elements.length; t++) {
                this.templates.elements[t].index = t;
            }
        }
        if (this.templates.groups.length > 0) {

            for (var t = 0; t < this.templates.groups.length; t++) {
                this.templates.groups[t].index = t;
            }
        }
        if (this.templates.layouts.length > 0) {

            for (var t = 0; t < this.templates.layouts.length; t++) {
                this.templates.layouts[t].index = t;
            }
        }
    };

    /**
     * Render plugins panel
     *
     * @private
     * @this ffbFormWizard
     */
    var _renderPlugins = function() {

        var panel = new PanelPlugins(this.cnt);
        panel.formWiz = this;

        this.cnt.append(panel.getHTML(this.plugins, this.templates, this.options));
    };

    /**
     * Render plugins panel
     *
     * @private
     * @this ffbFormWizard
     */
    var _renderCanvas = function() {

        var panel = new PanelCanvas(this.cnt);
        var self  = this;
        panel.formWiz = this;

        this.cnt.append(panel.getHTML());

        //init droppable for form
        this.initDroppable();
    };

    /**
     * Add item to canvas
     *
     * @public
     * @this ffbFormWizard
     * @param {integer} itemType
     * @param {integer} elementType
     * @param {integer} index
     * @param {object} templateData
     */
    this.addItem = function(itemType, elementType, index, templateData) {

        //check maximal groups count
        if (this.options.maxGroupsCount &&
            this.getItemsCount() >= this.options.maxGroupsCount
        ) {
            return false;
        }

        //prepare data
        var el = null;

        switch (itemType) {
            case this.ITEM_TYPE_GROUP:

                //we have default groups only - 1
                var group = new FormGroupDefault();
                group.formWiz = this;

                if (templateData !== null && templateData !== undefined) {
                    group.setData(templateData);
                }
                el = group;

                break;
            case this.ITEM_TYPE_LAYOUT:

                //we have default layout only - 1
                //TODO update to new layouts later
                var group = new FormGroupDefault();
                group.formWiz = this;
                var layout = new FormLayoutDefault();
                layout.index = group.getItemsCount();

                if (templateData !== null && templateData !== undefined) {
                    layout.formWiz = this;
                    layout.setData(templateData);
                }
                group.addItemToData(layout);
                el = group;

                break;
            case this.ITEM_TYPE_ELEMENT:

                var group      = new FormGroupDefault();
                group.formWiz  = this;
                var layout     = new FormLayoutDefault();
                layout.formWiz = this;
                layout.index   = group.getItemsCount();
                var element    = null;

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

                if (templateData !== null && templateData !== undefined) {
                    element.formWiz = this;
                    element.setData(templateData);
                }

                layout.addItemToColumn(element, 0);
                group.addItemToData(layout);
                el = group;

                break;
        }

        if (el === null) return;

        el.index = index;
        this.data.splice(el.index, 0, el);

        this.refresh();
    };

    /**
     * Get data from elements
     *
     * @public
     * @this ffbFormWizard
     * @return {object} data
     */
    this.getData = function() {

        var data = [];
        for (var g = 0; g < this.getItemsCount(); g++) {

            data.push(this.getItem(g).getData());
        }

        return data;
    };

    /**
     * Get item by index
     *
     * @public
     * @this ffbFormWizard
     * @param {integer} index
     * @return {FormGroup|FormLayout|FormElement} item
     */
    this.getItem = function(index) {

        if (index > this.data.length) return null;
        return this.data[index];
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
     * Init droppable
     *
     * @public
     * @this FormGroup
     * @return {integer} count
     */
    this.initDroppable = function() {

        var self = this;

        //reinit workspace
        //http://api.jqueryui.com/droppable/
        this.cnt.find('.workspace')
            .droppable({disabled    : true})
            .droppable('destroy')
            .droppable({
                accept      : '.button',
//                activeClass : false,
                addClasses  : false, //true
//                disabled    : false,
                greedy      : true, //false
                hoverClass  : 'hover', //false
                scope       : 'fwbuttons', //default
                tolerance   : 'pointer', //intersect, fit, pointer, touch
//                activate    : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
//
//                },
//                deactivate  : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
//
//                },
                drop : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset
                    self.onDrop($(event.target), ui.draggable);
                },
                out : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                },
                over : function(event, ui) {//ui.draggable, ui.helper, ui.position, ui.offset

                }
            });
    }

    /**
     * Set data to formwiz
     *
     * @private
     * @this ffbFormWizard
     * @param {object} data
     */
    this.setData = function(data) {

        //parse existed data
        this.data = _parseData.call(this, data);

        //render plugins and canvas
        this.render();
    };

    /**
     * Set locale to formwiz
     *
     * @private
     * @this ffbFormWizard
     * @param {string} locale
     */
    this.setLocale = function(locale) {

        //save current locale
        this.getData();

        //parse existed data
        this.options.locale = locale;

        //render plugins and canvas
        this.refresh();
    };

    /**
     * Parse Json
     *
     * @public
     * @this ffbFormWizard
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
     * Refresh groups indexes, render groups
     *
     * @public
     * @this ffbFormWizard
     */
    this.refresh = function() {

        // remove all existed hover classes
        this.cnt
            .removeClass('hover after before')
            .find('.hover, .after, .before')
                .removeClass('hover after before');

        //update indexes, render elements
        for (var i = 0; i < this.getItemsCount(); i++) {

            this.getItem(i).index = i;

            if (this.getItem(i).cnt !== null && this.getItem(i).cnt !== undefined) {

                var oldCnt = this.getItem(i).cnt;
                oldCnt.replaceWith(this.getItem(i).getHTML(this));
            } else {

                var html = this.getItem(i).getHTML(this);
                if (i > 0) {
                    this.getItem(i - 1).cnt.after(html);
                } else if (i === 0 && this.getItemsCount() > 1 && this.getItem(1).cnt !== null) {
                    this.getItem(1).cnt.before(html);
                } else {
                    this.cnt.find('.panel.canvas .workspace').append(html);
                }
            }
        }

        // reinit droppable workspace
        this.initDroppable();
    };

    /**
     * Remove item from data
     *
     * @public
     * @this ffbFormWizard
     * @param {integer} index
     */
    this.removeItem = function(index) {

        // check Ids with data to confirm removing
        var item        = this.getItem(index);
        var needConfirm = false;

        // check layout and elements in
        needConfirm = item.hasElementsWithData(item.data);

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
                        item.cnt.remove();
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
            item.cnt.remove();
            this.data.splice(index, 1);
            this.refresh();
        }
    };

    /**
     * Render plugins and canvas
     *
     * @public
     * @this ffbFormWizard
     */
    this.render = function() {

        //clear
        this.cnt.html('');

        //render plugins panel
        _renderPlugins.call(this);

        //render canvas
        _renderCanvas.call(this);

        //render elements from data
        if (this.data.length > 0) {
            this.refresh();
        }
    };

    /**
     * Replace items
     *
     * @public
     * @this ffbFormWizard
     * @param {integer} draggableIndex
     * @param {integer} targetIndex
     */
    this.replaceItems = function(draggableIndex, targetIndex) {

        this.getData();

        var temp = $('<div>');
        temp.insertAfter(this.getItem(draggableIndex).cnt);
        this.getItem(draggableIndex).cnt.insertAfter(this.getItem(targetIndex).cnt);
        this.getItem(targetIndex).cnt.insertAfter(temp);
        temp.remove();

        var itemA = this.getItem(draggableIndex);
        var itemB = this.getItem(targetIndex);

        itemA.index = targetIndex;
        itemB.index = draggableIndex;

        this.data[targetIndex] = itemA;
        this.data[draggableIndex] = itemB;

        this.refresh();
    };

    /**
     * On Element drop
     *
     * @public
     * @this ffbFormWizard
     * @param {HTMLElement} target
     * @param {HTMLElement} button
     */
    this.onDrop = function(target, button) {        

        if (!button.hasClass('button') && button.hasClass('group') && target.hasClass('group')) {
            //group move

            var index = parseInt(target.attr('data-index'));
            if (target.hasClass('after')) {
                index++;
            }

            this.getData();
            this.replaceItems(parseInt($(button).attr('data-index')), index);
            return;
        }

        var itemType = null;
        var tData    = null;

        //prepare data for addItem
        if (button.hasClass('group')) {
            itemType = this.ITEM_TYPE_GROUP;
        }
        if (button.hasClass('layout')) {
            itemType = this.ITEM_TYPE_LAYOUT;
        }
        if (button.hasClass('element')) {
            itemType = this.ITEM_TYPE_ELEMENT;
        }

        var index   = this.getItemsCount();
        var subtype = parseInt($(button).attr('data-type'));

        //prepare index
        if (target.hasClass('workspace')) {
            index = this.getItemsCount();
        } else if (target.hasClass('before')) {
            index = parseInt(target.attr('data-index'));
        } else {
            index = parseInt(target.attr('data-index')) + 1;
        }

        //check for template
        if (button.hasClass('template') && button.attr('data-template') !== undefined) {

            //parse Json
            tData   = this.parseJSON(button.attr('data-template'))[0];
            subtype = tData.type;
        }

        this.getData();
        this.addItem(itemType, subtype, index, tData);
    };

    /**
     * Init form wizard, parse options, start render
     *
     * @public
     * @this ffbFormWizard
     * @param {HTMLElement} container
     */
    this.init = function(container) {

        this.cnt = $(container);
        if (this.cnt.length === 0) return;

        //parse options
        _parseOptions.call(this);

        //parse existed data
        this.data = _parseData.call(this, this.data);

        //parse existed templates
        _parseTemplates.call(this);

        //init options logic if exist
        _initOptions.call(this);

        //render plugins and canvas
        this.render();
    };

    this.init(container);
};
