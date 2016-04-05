"use strict";

/**
 * Form Attribute init
 *
 * @class
 * @constuctor
 * @this TemplateForm
 * @var {object} form
 */
var TemplateForm = function(form) {

    var _ = this;
    _.form = form;
    _.request = null;

    /**
     * Init create form js
     *
     * @private
     * @this TemplateForm
     */
    this.init = function() {

        myApp.langSwitcher.hideInactiveTranslations();

        //init form
        var fi = new FormInitializer(this.form, 'create');
        fi.initDatePicker();

        _.initAssignmentCheckboxes();

        new SortEntitiesForm($('.form-sort-entities'));

        //init form submit
        var fo = new FormObject(this.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_TEMPLATE'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                if (typeof json.templateUrl !== 'undefined') {

                    myApp.refreshNavigation(json.templateUrl, function(){
                        // open template tab
                        var firstPane = $(myApp.pm.panes.first());
                        firstPane.find('.tab[data-content="templates"]').click();

                    });
                }
            }
        });

        this.form.addClass('active');
    }

    /**
     * Init assignment checkbox click
     *
     * @public
     * @this AttributeController
     */
    this.initAssignmentCheckboxes = function () {

        //init click on attribute checkboxes
        this.form.parent().find('.category-list .pane-navi-link-cnt input[type="checkbox"]').on('click', function (e) {

            var checked = $(this).is(':checked');

            var naviAjax = new ffbAjax();
            var self     = this;
            //
            //abort previously
            if (this.request) {
                this.request.abort();
            }

            var url = $(this).attr('data-href');
            //get content
            this.request = naviAjax.call(
                url,
                function (data) {

                    var result = ajax.isJSON(data);

                    if (result.state !== 'ok') {
                        var errorData = ajax.parseError(data);
                        ffbLightbox.showInfo({
                            'title': ffbTranslator.translate('TTL_ERROR'),
                            'className': 'error',
                            'text': errorData.message
                        });
                    }
                },
                {
                    'data': {assignAttribute: checked},
                    'type': 'post',
                    'accepts': 'json'
                }
            );
        });
    };

    this.init();
}