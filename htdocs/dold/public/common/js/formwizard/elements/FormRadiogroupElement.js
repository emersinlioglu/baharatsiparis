/* jshint -W117 */
"use strict";

/**
 * Form radio group element
 *
 * @this FormRadiogroupElement
 */
var FormRadiogroupElement = function() {

    FormElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_RADIO_GROUP');
    this.label = {};
    this.name  = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type  = this.ELEMENT_TYPE_RADIO;
    this.buttonClass = 'type-' + this.type;
    this.value = {};

    /**
     * Get data from elements
     *
     * @public
     * @this FormRadiogroupElement
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('input[name="' + this.name + 'label"]').val().trim();

        this.label[this.locale] = label;

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

        for (var o = 0; o < this.options.length; o++) {

            element.options.push(this.options[o].getData());
        }

        return element;
    };
};
//TODO Move common function in proto
FormRadiogroupElement.prototype = inherit(FormElement.prototype);

/**
 * Create html element
 *
 * @public
 * @this FormRadiogroupElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormRadiogroupElement.prototype.getHTML = function(layout) {

    //init parent
    this.cnt = FormElement.prototype.getHTML.call(this, layout);

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

    this.cnt.append(this.getOptionsHTML());

    this.refreshOptions();

    return this.cnt;
};
