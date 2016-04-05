/* jshint -W117 */
"use strict";

/**
 * Form checkbox group element
 *
 * @this FormCheckboxgroupElement
 */
var FormCheckboxgroupElement = function() {

    FormElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_CHECKBOX_GROUP');
    this.className = 'checkboxgroup';
    this.label = {};
    this.name  = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type  = this.ELEMENT_TYPE_CHECKBOX;
    this.buttonClass = 'type-' + this.type;
    this.value = {};

    /**
     * Get data from elements
     *
     * @public
     * @this FormCheckboxgroupElement
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

        //get options
        for (var o = 0; o < this.options.length; o++) {

            var option = this.options[o];
            var comment = option.cnt.find('input[name="' + option.name + 'hasComment"]');
            if (comment.length > 0) {

                props = [
                    {key : 'has-comment', value : comment.first().prop('checked')}
                ];

                option.setProperties(props);
            }

            var optionData = option.getData();
            element.options.push(optionData);
        }

        return element;
    };

    /**
     * Refresh options
     *
     * @public
     * @this FormExtendedSelectElement
     */
    this.refreshOptions = function() {

        //update indexes
        for (var i = 0; i < this.options.length; i++) {

            var option   = this.options[i];
            option.index = i;

            if (option.cnt) {
                option.cnt.remove();
            }

            var html         = option.getHTML(this);
            var commentValue = null;

            //check properties values
            for (var k = 0; k < option.properties.length; k++) {
                var prop = option.properties[k];
                switch (prop.key) {
                    case 'has-comment':
                        commentValue = prop.value;
                        break;
                }
            }

            if (commentValue) {

               //create extra elements
                var label = $('<label>')
                    .addClass('property')
                    .attr('for', option.name + 'commentlabel')
                    .html(ffbTranslator.translate('LBL_HAS_COMMENT'));
                var input = $('<input>')
                    .attr('name', option.name + 'hasComment')
                    .attr('type', 'checkbox')
                    .attr('value', true)
                    .prop('checked', commentValue);
                label.append(input);
                $(html).find('.remove').after(label);
            }

            this.cnt.find('> .options > .row').last().before(html);
        }
    };
};
//TODO Move common function in proto
FormCheckboxgroupElement.prototype = inherit(FormElement.prototype);

/**
 * Create html element
 *
 * @public
 * @this FormCheckboxgroupElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormCheckboxgroupElement.prototype.getHTML = function(layout) {

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
