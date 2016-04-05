/* jshint -W117 */
"use strict";

/**
 * Form Layout
 *
 * @this FormLayoutDefault
 */
var FormLayoutDefault = function() {

    FormLayout.call(this);
    this.columnsCount   = 1;
    this.columnsClasses = ['full'];
    this.data  = [
        []
    ];
    this.label = {
        'de' : ffbTranslator.translate('LBL_FULL_WIDTH_LAYOUT'),
        'en' : ffbTranslator.translate('LBL_FULL_WIDTH_LAYOUT')
    };
    this.type  = this.LAYOUT_TYPE_DEFAULT;
};
FormLayoutDefault.prototype = inherit(FormLayout.prototype);
