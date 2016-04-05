/* jshint -W117 */
"use strict";

/**
 * Form multiple select element
 *
 * @this FormMultipleSelectElement
 */
var FormMultipleSelectElement = function() {

    FormSelectElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_MULTIPLE_DROPDOWN');
    this.label       = {};
    this.name        = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type        = this.ELEMENT_TYPE_MULTIPLE_SELECT;
    this.buttonClass = 'type-' + this.type;
    this.value       = {};
};
//TODO Move common function in proto
FormMultipleSelectElement.prototype = inherit(FormSelectElement.prototype);
