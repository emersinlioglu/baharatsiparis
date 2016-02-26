"use strict";

/**
 * Form Category init
 *
 * @class
 * @constuctor
 * @this CategoryForm
 * @var {object} form
 */
var CategoryForm = function(form) {

    var _       = this;
    _.form      = form;
    _.request   = null;

    /**
     * Init create form js
     *
     * @private
     * @this UserForm
     */
    this.init = function() {

        myApp.langSwitcher.hideInactiveTranslations();

        //init form
        var fi = new FormInitializer(this.form, 'create');
        fi.initDropdowns();

        //init form submit
        var fo = new FormObject(this.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_CATEGORY'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                var rootProductForm = $('.form-root-product');
                if (rootProductForm.length > 0) {
                    rootProductForm.submit();
                } else {
                    if (typeof json.categoryUrl !== 'undefined') {

                        myApp.refreshNavigation(json.categoryUrl, function(){

                            var navi = $(myApp.pm.panes[0]);
                            navi.find('[data-form-url="' + json.categoryEditUrl + '"]').click();
                        });
                    }
                }

            }
        });

        this.form.addClass('active');
    }

    this.init();
}