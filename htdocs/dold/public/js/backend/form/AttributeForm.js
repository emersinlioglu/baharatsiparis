"use strict";

/**
 * Form Attribute init
 *
 * @class
 * @constuctor
 * @this AttributeForm
 * @var {object} form
 */
var AttributeForm = function(form) {

    var _           = this;
    var _dropdowns  = null;
    var _form       = form;
    var _request    = null;

    /**
     * Init attribute type dropdown
     */
    _.initAttributeTypeDropdown = function() {

        if (!_dropdowns.hasOwnProperty('type')) {return;}

        // show additional fields for the selected attribute type
        //var dd = _dropdowns['type'];
        //var key = dd.value;
        //var selectedAttributeType = dd.values[dd.value].value;
        //_form.find('.additional-fields.attribute-type-' + selectedAttributeType).removeClass('hide');

        // init onSelect for type field
        _dropdowns['type'].opt.onSelect = function(e, index, optionValue) {
            var elm = $(e);
            _form.find('.additional-fields').addClass('hide');
            _form.find('.additional-fields.attribute-type-' + optionValue).removeClass('hide');
        };
    }

    /**
     * Init form submit
     */
    _.initFormSubmit = function() {

        //init form submit
        var fo = new FormObject(_form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_ATTRIBUTE'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                if (typeof json.attributeUrl !== 'undefined') {

                    var mainNavi = $(myApp.pm.panes.get(0));

                    var subnaviUrl = mainNavi.find('.pane-navi-link.selected').attr('href');
                    if (!subnaviUrl) {
                        subnaviUrl = json.subnaviUrl;
                    }

                    myApp.refreshSubnavi(
                        $('<a/>').attr('href', subnaviUrl),
                        function(pane) {

                            // refresh subnavi
                            $(pane).find('.pane-navi-link[href="' + json.attributeUrl + '"]').trigger('click');
                        }
                    );

                }
            }
        });

        _form.addClass('active');
    }

    /**
     * Init create form js
     *
     * @private
     * @this AttributeForm
     */
    this.init = function() {

        myApp.langSwitcher.hideInactiveTranslations();

        //init form
        var fi = new FormInitializer(_form, 'create');
        fi.initDatePicker();
        _dropdowns = fi.initDropdowns();

        _.initAttributeTypeDropdown();
        _.initFormSubmit();

    }

    this.init();
}