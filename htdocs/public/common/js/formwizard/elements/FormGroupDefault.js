/* jshint -W117 */
"use strict";

/**
 * Form group default
 *
 * @this FormGroupDefault
 */
var FormGroupDefault = function() {

    FormGroup.call(this);
    this.label = {
        'de' : ffbTranslator.translate('LBL_GROUP_HEADLINE'),
        'en' : ffbTranslator.translate('LBL_GROUP_HEADLINE')
    };
    this.type  = this.GROUP_TYPE_DEFAULT;
};
FormGroupDefault.prototype = inherit(FormGroup.prototype);
