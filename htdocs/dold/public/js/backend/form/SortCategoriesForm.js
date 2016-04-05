"use strict";

/**
 * Form SortCategories init
 *
 * @class
 * @constuctor
 * @this SortCategoriesForm
 * @var {object} form
 */
var SortCategoriesForm = function(form, opt) {

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

        var fo = new FormObject(_.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_CATEGORY_SORT'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                if (typeof json.categoryUrl !== 'undefined') {

                    myApp.refreshNavigation(json.categoryUrl, function(){

                    });
                }
            }
        });

        //new ffbAccordion(myApp.pm.panes.last().find('.ffb-accordion'), false);

        //_.form.find('ul.category-list, ul.sublist, ul.subsublist').sortable({
        _.form.find('ul.sortable').sortable({
            stop: function(event, ui) {

                // update index
                ui.item.closest('ul').find('> li').each(function(i, elm) {

                   $(elm).find('> input[type="hidden"]').val(i);
                });
            },
            //update: function(event, ui) {
            //
            //    var newAjax = new ffbAjax();
            //
            //    //abort previously
            //    if (this.request) {
            //        this.request.abort();
            //    }
            //
            //    var data = ui.item.closest('ul').sortable('serialize');
            //
            //    var url = _.form.attr('action');
            //    //get content
            //    this.request = newAjax.call(
            //        url,
            //        function (data) {
            //
            //            var result = ajax.isJSON(data);
            //            if (result.state == 'ok') {
            //
            //                myApp.pm.refreshNavigation();
            //
            //            } else {
            //                var errorData = ajax.parseError(data);
            //                ffbLightbox.showInfo({
            //                    'title': ffbTranslator.translate('TTL_ERROR'),
            //                    'className': 'error',
            //                    'text': errorData.message
            //                });
            //            }
            //        },
            //        {
            //            'data': data,
            //            'type': 'post',
            //            'accepts': 'json'
            //        }
            //    );
            //
            //}
        });


    }

    this.init(form, opt);
}