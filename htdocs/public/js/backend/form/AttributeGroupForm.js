"use strict";

/**
 * Form AttributeGroup init
 *
 * @class
 * @constuctor
 * @this AttributeForm
 * @var {object} form
 */
var AttributeGroupForm = function(form) {

    var _ = this;
    _.form = form;
    _.request = null;

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

        new SortEntitiesForm($('.form-sort-entities'));

        //init form submit
        var fo = new FormObject(this.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_ATTRIBUTE_GROUP'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                if (typeof json.attributeGroupUrl !== 'undefined') {

                    myApp.refreshNavigation(json.attributeGroupUrl, function(){
                        //todo
                    });
                }
            }
        });

        this.form.addClass('active');
    }

    this.init();
}