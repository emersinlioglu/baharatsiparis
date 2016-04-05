/* jshint -W117 */
"use strict";

/**
 * Form extended select element
 *
 * @this FormExtendedSelectElement
 */
var FormExtendedSelectElement = function() {

    FormSelectElement.call(this);
    this.buttonTitle = ffbTranslator.translate('BTN_EXTENDED_DROPDOWN');
    this.className   = 'exended-select';
    this.label       = {};
    this.name        = 'customField' + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    this.type        = this.ELEMENT_TYPE_EXTENDED_SELECT;
    this.buttonClass = 'type-' + this.type;
    this.value       = {};

    /**
     * Get data from elements
     *
     * @public
     * @this FormExtendedSelectElement
     * @return {object} data
     */
    this.getData = function() {

        var label = this.cnt.find('input[name="' + this.name + 'label"]').val().trim();
        var value = this.cnt.find('input[name="' + this.name + 'value"]').val().trim();

        this.label[this.locale] = label;
        this.value[this.locale] = value;

        //check properties
        var props = [];
        for (var i = 0; i < this.properties.length; i++) {

            var prop = this.properties[i];
            switch (prop.key) {
                case 'is-mandatory':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="elementRequired"]').prop('checked')
                    });
                    break;
                case 'is-visible':
                    prop.setData({
                        key   : prop.key,
                        value : this.cnt.find('input[name="elementVisible"]').prop('checked')
                    });
                    break;
            }

            props.push(prop.getData());
        }

        //check setting 3 existed
        var setting3 = this.cnt.find('select[name="' + this.name + 'setting3label"]');

        //check settings
        var settings = {
            setting1 : this.cnt.find('select[name="' + this.name + 'setting1label"]').val(),
            setting2 : this.cnt.find('input[name="' + this.name + 'setting2label"]').prop('checked'),
            setting3 : setting3.length > 0 ? setting3.val() : null,
            setting4 : null
        };

        //prepare element data object
        var element = {
            id         : this.id,
            label      : this.label,
            options    : [],
            properties : props,
            type       : this.type,
            value      : this.value,
            settings   : settings
        };

        //get options
        for (var o = 0; o < this.options.length; o++) {

            var option = this.options[o];
            var amount = option.cnt.find('input[name="' + option.name + 'amountlabel"]').first().val().trim();
            var price  = option.cnt.find('input[name="' + option.name + 'pricelabel"]').first().val().trim()/*.replace(/,/igm, '.')*/;

            // check price is float
//            if (parseFloat(price) >= 0) {
//                price = parseFloat(price);
//            } else {
//                price = 0;
//            }

            props = [
                {key : 'has-amount', value : amount},
                {key : 'has-price', value : price/*.toFixed(2)*/}
            ];

            option.setProperties(props);
            var optionData = option.getData();

            element.options.push(optionData);
        }

        return element;
    };

    /**
     * Refresh options
     *
     * @public
     * @this FormExtendedSelectElement
     */
    this.refreshOptions = function() {

        //update indexes
        for (var i = 0; i < this.options.length; i++) {

            var option = this.options[i];
            option.index = i;

            if (option.cnt) {
                option.cnt.remove();
            }

            var html        = option.getHTML(this);
            var amountValue = null;
            var priceValue  = null;

            //check properties values
            for (var k = 0; k < option.properties.length; k++) {
                var prop = option.properties[k];
                switch (prop.key) {
                    case 'has-amount':
                        amountValue = prop.value;
                        break;
                    case 'has-price':
                        priceValue = prop.value;
                        break;
                }
            }

            // check price is float, convert in locale
            if (parseFloat(priceValue) >= 0) {
                priceValue = parseFloat(priceValue);
            } else {
                priceValue = 0;
            }

            // convert price
            priceValue = priceValue.toFixed(2);

            // update delimitr
            switch (this.locale) {
                case 'de':
                    priceValue = priceValue.replace(/\./igm, ',');
                    break;
                default:
            }

            //create extra elements
            var label = $('<label>')
                .addClass('property')
                .attr('for', option.name + 'amountlabel')
                .html(ffbTranslator.translate('LBL_AMOUNT'));
            var input = $('<input>')
                .attr('name', option.name + 'amountlabel')
                .attr('type', 'text')
                .attr('value', amountValue || '');
            label.append(input);
            $(html).find('.remove').before(label);

            //create extra elements
            var labelPrice = $('<label>')
                .addClass('property')
                .attr('for', option.name + 'pricelabel')
                .html(ffbTranslator.translate('LBL_PRICE'));
            var inputPrice = $('<input>')
                .attr('name', option.name + 'pricelabel')
                .attr('type', 'text')
                .attr('value', priceValue || '');
            labelPrice.append(inputPrice);
            $(html).find('.remove').before(labelPrice);

            this.cnt.find('> .options > .row').last().before(html);
        }
    };
};
//TODO Move common function in proto
FormExtendedSelectElement.prototype = inherit(FormSelectElement.prototype);

/**
 * Create html element
 *
 * @public
 * @this FormExtendedSelectElement
 * @param {FormLayout} layout
 * @return {HTMLElement}
 */
FormExtendedSelectElement.prototype.getHTML = function(layout) {

    //init parent
    this.cnt = FormSelectElement.prototype.getHTML.call(this, layout);

    var settingsDiv = $('<div class="settings">');
    var label       = null;
    var input       = null;

    //create settings controls
    var rowSetting1 = $('<div>')
            .addClass('row');
    label = $('<label>')
            .attr('for', this.name + 'setting1label')
            .html(ffbTranslator.translate('LBL_ELEMENT_SETTING_SELECT_TYPE'));
    input = $('<select class="hide">')
            .attr('name', this.name + 'setting1label')
            .append($('<option value="1">' + ffbTranslator.translate('OPT_ELEMENT_SETTING_SELECT_TYPE_RADIO') + '</option>'))
            .append($('<option value="2">' + ffbTranslator.translate('OPT_ELEMENT_SETTING_SELECT_TYPE_CHECKBOX') + '</option>'))
            .append($('<option value="3">' + ffbTranslator.translate('OPT_ELEMENT_SETTING_SELECT_TYPE_PRIO') + '</option>'))
            .val(this.settings.setting1 === null ? '1' : this.settings.setting1);

    rowSetting1.append(label);
    rowSetting1.append(input);
    settingsDiv.append(rowSetting1);

    //create settings controls
    var rowSetting2 = $('<div>')
            .addClass('row');
    label = $('<label>')
        .attr('for', this.name + 'setting2label')
        .append(ffbTranslator.translate('LBL_ELEMENT_SETTING_WAITLIST'));
    input = $('<input>')
        .attr('name', this.name + 'setting2label')
        .attr('type', 'checkbox')
        .attr('maxlength', '128')
        .val(1)
        .prop('checked', this.settings.setting2 === '1');
    rowSetting2.append(label);
    rowSetting2.append(input);
    settingsDiv.append(rowSetting2);

    //check multiple tms events setting
    if (this.formWiz.options.tmseventparts) {

        //create settings controls
        var rowSetting3 = $('<div>')
                .addClass('row');
        label = $('<label>')
                .attr('for', this.name + 'setting3label')
                .html(ffbTranslator.translate('LBL_ELEMENT_SETTING_SELECT_EVENTPART'));
        input = $('<select class="hide">')
                .attr('name', this.name + 'setting3label')
                .append($('<option value="">' + ffbTranslator.translate('OPT_ELEMENT_SETTING_SELECT_EVENTPART_ALL') + '</option>'));

        // create options
        for (var eventId in this.formWiz.options.tmseventparts) {
            if (!this.formWiz.options.tmseventparts.hasOwnProperty(eventId)) continue;

            input.append($('<option value="' + eventId + '">' + this.formWiz.options.tmseventparts[eventId] + '</option>'));
        }

        // set value
        input.val(this.settings.setting3 === null ? '' : this.settings.setting3);

        rowSetting3.append(label);
        rowSetting3.append(input);
        settingsDiv.append(rowSetting3);
    }

    //add settings
    this.cnt.append(settingsDiv);

    var self = this;

    //init select, after render end
    setTimeout(function() {
        self.cnt.find('select').each(function(i, sel) {

            new ffbDropdown(sel);
        });
    }, 0);

    return this.cnt;
};
