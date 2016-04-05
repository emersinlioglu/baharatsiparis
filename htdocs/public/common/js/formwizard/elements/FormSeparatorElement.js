/* jshint -W117 */
"use strict";

/**
 * Form separator element
 *
 * @this FormSeparatorElement
 */
var FormSeparatorElement = function() {

    FormElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_SEPARATOR');
    this.label = {};
    this.name  = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type  = this.ELEMENT_TYPE_SEPARATOR;
    this.buttonClass = 'type-' + this.type;
    this.value = {};

    //remove is-mandatory
    this.properties.splice(0, 1);

    /**
     * Get data from elements
     *
     * @public
     * @this FormSeparatorElement
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('input[name="' + this.name + 'label"]').val().trim();

        // set default value for all languages
        for (var k = 0; k < this.formWiz.options.locales.length; k++) {

            var code = this.formWiz.options.locales[k];
            this.label[code] = 'Separator';
        }

        //check properties
        var props = [];
        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];
            switch (prop.key) {
                case 'is-visible':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="elementVisible"]').prop('checked')
                    });
                    break;
            }

            props.push(prop.getData());
        }

        //set is-mandatory to false for headline
        props.push({
            key   : 'is-mandatory',
            value : false
        });

        var element = {
            id         : this.id,
            label      : this.label,
            properties : props,
            type       : this.type,
            value      : this.value
        };

        return element;
    };
};
//TODO Move common function in proto
FormSeparatorElement.prototype = inherit(FormElement.prototype);

/**
 * Create html element
 *
 * @public
 * @this FormSeparatorElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormSeparatorElement.prototype.getHTML = function(layout) {

    //init parent
    this.cnt = FormElement.prototype.getHTML.call(this, layout);

    //inputs for label
    var row = $('<div>')
        .addClass('row')
        .addClass('hide');
    var label = $('<label>')
        .attr('for', this.name + 'label')
        .html(ffbTranslator.translate('LBL_ELEMENT_SEPARATOR'));
    var labelString = 'separator';
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

    //hide required
    this.cnt.find('.title .required').addClass('hide');

    return this.cnt;
};
