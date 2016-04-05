/* jshint -W117 */
"use strict";

/**
 * Form note text element
 *
 * @this FormNoteTextElement
 */
var FormNoteTextElement = function() {

    FormTextareaElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_TEXT_NOTE');
    this.className = 'textnote';
    this.label = {};
    this.name  = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type  = this.ELEMENT_TYPE_NOTE_TEXT;
    this.buttonClass = 'type-' + this.type;
    this.value = {};

    /**
     * Get data from elements
     *
     * @public
     * @this FormNoteTextElement
     * @return {object} data
     */
    this.getData = function() {

        var value = this.cnt.find('textarea[name="' + this.name + 'value"]').val().trim();

        // set default value for all languages
        for (var k = 0; k < this.formWiz.options.locales.length; k++) {

            var code = this.formWiz.options.locales[k];
            this.label[code] = 'Note text';
        }

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
            properties : props,
            type       : this.type,
            value      : this.value
        };

        return element;
    };
};
//TODO Move common function in proto
FormNoteTextElement.prototype = inherit(FormTextareaElement.prototype);
