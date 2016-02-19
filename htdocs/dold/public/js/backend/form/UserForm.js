"use strict";

/**
 * Form User init
 *
 * @class
 * @constuctor
 * @this UserForm
 * @var {object} form
 */
var UserForm = function(form) {

    var _ = this;
    _.form = form;
    _.request = null;

    /**
     *
     * @param {stirng} role
     */
//    this.showRight = function(role) {
//
//        if (role === undefined) return;
//
//        _form.find('.user-rights .static-text').each(function(i, row) {
//
//            if ($(row).hasClass(role)) {
//                $(row).addClass('enabled');
//            } else {
//                $(row).removeClass('enabled');
//            }
//        });
//    }

    /**
     * Inits delete button
     * @returns {UserForm}
     */
    this.initDelete = function() {

        _.form.find('.button.delete').click(function(e) {
            e.preventDefault();

            var data = {
                id: _.form.find('input[name="id"]').val()
            };

            // abort previously request
            if (this.request) {
                this.request.abort();
            }

            this.request = new ffbAjax();
            var self = this;

            // get table
            this.request = ajax.call(
                $(self).data('url'),
                function(data) {

                    // parse result
                    var result = ajax.isJSON(data);

                    if (result && result.state === 'ok') {

                        // init Navi
                        myApp.pm.panes.first().find('.pane-navi-link.selected').click();
                    } else {

                        // show error
                        var errorData = ajax.parseError(data);
                        ffbLightbox.showInfo({
                            'title'     : ffbTranslator.translate('TTL_DELETE_USER'),
                            'className' : 'error',
                            'text'      : errorData.message
                        });
                    }
                },
                {
                    'accepts'   : 'json',
                    'data'      : data,
                    'type'      : 'post'
                }
            );

            return false;
        });
    }

    /**
     * Init create form js
     *
     * @private
     * @this UserForm
     */
    this.init = function() {

        //init form
        var fi = new FormInitializer(this.form, 'create');
        fi.initDatePicker();

        //init dropdowns
        var dropdowns = fi.initDropdowns();
//        dropdowns.role.opt.onSelect = function(element, index, value) {
//            _.showRight(value);
//        }

        //LanguageSwitcher
//        new LanguageSwitcher($('.panemanager-pane-main'));

        //show rights for current user if exists
//        _.showRight(_form.find('.user-rights').attr('data-current'));


        // init delete btn
        this.initDelete();

        //init form submit
        var fo = new FormObject(this.form);
        fo.initFormSubmit({
            'translations' : {
                'lbTitle': ffbTranslator.translate('TTL_SAVE_USER'),
                'progressMsg': ffbTranslator.translate('MSG_SAVING')
            },
            'isDontShowInvalidFields' : false,
            'fullScreenLoading': true
        }, function(result, json) {

            if (result === true) {

                if (typeof json.naviUrl !== 'undefined') {

                    myApp.refreshSubnavi(
                        $('<a/>').attr('href', json.naviUrl),
                        function(pane) {

                            // refresh subnavi
                            $(pane).find('.pane-navi-link[href="' + json.subnaviUrl + '"]').trigger('click');
                        }
                    );
                } else if (typeof json.subnaviUrl !== 'undefined') {

                    // refresh subnavi
                    $('.panemanager-pane.sub-navi-pane').find('.pane-navi-link[href="' + json.subnaviUrl + '"]').trigger('click');
                }
            }
        });

        this.form.addClass('active');
    }

    this.init();
}