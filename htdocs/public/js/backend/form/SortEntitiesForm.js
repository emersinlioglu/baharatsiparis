"use strict";

/**
 * Form SortEntities init
 *
 * @class
 * @constuctor
 * @this SortEntitiesForm
 * @var {object} form
 */
var SortEntitiesForm = function(form, opt) {

    var _       = this;
    _.form      = null;
    _.request   = null;

    /**
     * init sortable attribute list
     *
     * @private
     * @this AttributeForm
     */
    this.init = function (form, opt) {

        if (!form) return;

        _.form = form;

        new ffbAccordion(myApp.pm.panes.last().find('.ffb-accordion'), false);

        _.form.find('ul.sortable').sortable({
            update: function (event, ui) {
                // todo ajax call

                // update order
                $(this).find('li').each(function (index, element) {
                    $(element).find('input:last:hidden').val(index);
                });

                var ajaxAttributeGroupForm = new ffbAjax();
                //var self     = this;

                //abort previously
                if (this.request) {
                    this.request.abort();
                }

                var url = _.form.attr('action');
                //get content
                this.request = ajaxAttributeGroupForm.call(
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
                        'data': _.form.serialize(),
                        'type': 'post',
                        'accepts': 'json'
                    }
                );

            }
        }
        );
    }

    this.init(form, opt);
}