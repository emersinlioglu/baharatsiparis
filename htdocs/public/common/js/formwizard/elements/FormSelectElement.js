/* jshint -W117 */
"use strict";

/**
 * Form select element
 *
 * @this FormSelectElement
 */
var FormSelectElement = function() {

    FormElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_DROPDOWN');
    this.label = {};
    this.name  = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type  = this.ELEMENT_TYPE_SELECT;
    this.buttonClass = 'type-' + this.type;
    this.value = {};

    /**
     * Get data from elements
     *
     * @public
     * @this FormSelectElement
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('input[name="' + this.name + 'label"]').val().trim();
        var value = this.cnt.find('input[name="' + this.name + 'value"]').val().trim();

        this.label[this.locale] = label;
        this.value[this.locale] = value;

        //check properties
        var props = [];
        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];
            switch (prop.key) {
                case 'is-mandatory':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="elementRequired"]').prop('checked')
                    });
                    break;
                case 'is-visible':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="elementVisible"]').prop('checked')
                    });
                    break;
            }

            props.push(prop.getData());
        }

        var element = {
            id         : this.id,
            label      : this.label,
            options    : [],
            properties : props,
            type       : this.type,
            value      : this.value
        };

        //get options
        for (var o = 0; o < this.options.length; o++) {

            element.options.push(this.options[o].getData());
        }

        return element;
    };
};
//TODO Move common function in proto
FormSelectElement.prototype = inherit(FormElement.prototype);

/**
 * Create html element
 *
 * @public
 * @this FormSelectElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormSelectElement.prototype.getHTML = function (layout) {

    //init parent
    this.cnt = FormElement.prototype.getHTML.call(this, layout);

    //set default value if not existed
    for (var k = 0; k < this.formWiz.options.locales.length; k++) {

        var defs = this.formWiz.options.defValues;
        var code = this.formWiz.options.locales[k];
        if (
                typeof this.value[code] === 'undefined'
                && typeof defs[code] !== 'undefined'
                && typeof defs[code].VAL_PLEASE_SELECT !== 'undefined'
                ) {
            this.value[code] = defs[code].VAL_PLEASE_SELECT;
        }
    }

    //inputs for label
    var row = $('<div>')
            .addClass('row');
    var label = $('<label>')
            .attr('for', this.name + 'label')
            .html(ffbTranslator.translate('LBL_ELEMENT_LABEL'));
    var labelString = '';
    if (typeof this.label === 'object' && this.label[this.locale] !== undefined) {
        labelString = this.label[this.locale];
    }
    var input = $('<input>')
            .attr('name', this.name + 'label')
            .attr('type', 'text')
            .attr('value', labelString);
    row.append(label);
    row.append(input);
    this.cnt.append(row);

    //inputs for defaultvalue
    var rowDefVal = $('<div>')
            .addClass('row');
    var labelDefVal = $('<label>')
            .attr('for', this.name + 'label')
            .html(ffbTranslator.translate('LBL_DEFAULTVALUE'));
    var valueString = '';
    if (typeof this.value === 'object' && this.value[this.locale] !== undefined) {
        valueString = this.value[this.locale];
    }
    var inputDefVal = $('<input>')
            .attr('name', this.name + 'value')
            .attr('type', 'text')
            .attr('value', valueString);
    rowDefVal.append(labelDefVal);
    rowDefVal.append(inputDefVal);
    this.cnt.append(rowDefVal);

    this.cnt.append(this.getOptionsHTML());

    this.refreshOptions();

    return this.cnt;
};
