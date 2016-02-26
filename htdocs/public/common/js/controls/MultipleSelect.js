/*jshint -W117 */
"use strict";

/**
 * Custom multiple select
 *
 * @class
 * @constructor
 * @this {MultipleSelect}
 * @param {string|select} element
 * @return {MultipleSelect}
 */
var MultipleSelect = function(element, opt) {

    var _          = this;
    var _dbg       = false;

    _.el           = null;
    _.dd           = null;
    _.disabled     = false;
    _.parent       = null;
    _.values       = [];
    _.opt          = {
        'limit'    : null, // null - no limit, number is limit "data-limit"
        'onSelect' : null  // callBack function
    };

    // init options
    for (var key in opt) {
        if(_.opt[key] !== undefined) {
            _.opt[key] = opt[key];
        }
    }

    // init limit
    var limit = $(element).attr('data-limit');
    if (limit !== undefined && parseInt(limit) > 0) {
        _.opt.limit = parseInt(limit);
    }

    /**
     * Create list html and insert into parent
     *
     * @private
     * @this {MultipleSelect}
     */
    var _createListHtml = function(parent) {

        parent.find('.ffbdropdown-multiple-list').remove();
        if (_.values.length > 0) {

            var ul = $('<ul>')
                .addClass('ffbdropdown-multiple-list');

            for (var i = 0; i < _.values.length; i++) {

                var value = _.values[i];
                _dbg && console.log('MS value', value);

                var li = $('<li>');

                // add span to remove item
                var span = $('<span>')
                    .addClass('remove')
                    .attr('data-value', value)
                    .attr('title', ffbTranslator.translate('LNK_REMOVE_ITEM'))
                    .on('click', function(e) {
                        _removeValue($(this).attr('data-value'));
                        _renderList();
                        return false;
                    });

                // check is disabled
                if (!_.disabled) {
                    li.append(span);
                }

                var opt = _.el.find('option[value="' + value + '"]');
                li.append(opt.html());

                ul.append(li);
            }

            parent.append(ul);
        }
    };

    /**
     * Render selected values list
     *
     * @private
     * @this {MultipleSelect}
     */
    var _renderList = function() {

        var parent = _.el.parents('.row');

        //check editable mode
        var parentInplace = null;
        if (parent.hasClass('editable')) {
            parentInplace = parent.find('.editable.value');
            _createListHtml(parentInplace);
            parent        = parent.find('.editable.edit');
        }

        _createListHtml(parent);
    };

    /**
     * Add value to values
     *
     * @private
     * @this {MultipleSelect}
     * @param {string} value
     */
    var _addValue = function(value) {

        _.values.push(value);

        var index = _.getValueIndex(value);

        _.el.next('.ffbdropdown-main')
            .find('.ffbdropdown-list')
                .find('li[data-value="' + index +  '"]')
                    .addClass('in-list');

        //set value to select
        _.el.val(_.values);
        _.dd.refreshList(_.values);

        if (_.parent.hasClass('editable')) {
            _saveValue();
        }

        _renderList();

        if (_.opt.onSelect) {
            var func = _.getFunctionsParts(_.opt.onSelect);
            if (func) {
                func(element, _.value);
            }
        }

    };

    /**
     * Remove value from list
     *
     * @private
     * @this {MultipleSelect}
     * @param {string} value
     */
    var _removeValue = function(value, noSave) {

        var index = -1;
        for (var i = 0; i < _.values.length; i++) {
            if (_.values[i] == value) {
                index = i;
            }
        }
        _.values.splice(index, 1);

        var valueIndex = _.getValueIndex(value);

        _.el.next('.ffbdropdown-main')
            .find('.ffbdropdown-list')
                .find('li[data-value="' + valueIndex +  '"]')
                    .removeClass('in-list');

        //set value to select
        _.el.val(_.values);
        _.dd.refreshList(_.values);

        if (noSave === true) {
            return;
        }

        if (_.parent.hasClass('editable')) {
            _saveValue();
        }

        _renderList();

        if (_.opt.onSelect) {
            var func = _.getFunctionsParts(_.opt.onSelect);
            if (func) {
                func(element, _.value);
            }
        }

    };

    /**
     * Init ffbDropdown object and render selected list
     *
     * @private
     * @this {MultipleSelect}
     */
    var _render = function() {

        var className = ['default', 'multiple'];
        if (_.opt.limit && _.opt.limit === 1) {
            className.push('limit-one');
        }

        //init dropdown
        _.dd = new ffbDropdown(_.el[0], {
            'liHeight'          : 30,
            'className'         : className.join(' '),
            'updateDefaultText' : false,
            'onSelect'          : function(id, optionIndex) {

                // check is disabled
                if (_.disabled) return;

                if (optionIndex !== '') {

                    var value = $(_.el.children()[optionIndex]).val();
                    var index = -1;

                    // get index in selected values
                    for (var i = 0; i < _.values.length; i++) {
                        //no strong match, couse strings and integers
                        if (_.values[i] == value) {
                            index = i;
                        }
                    }

                    if (index >= 0) {

                        //set unselected
                        _removeValue(value);
                    } else {

                        if (!_.opt.limit || _.opt.limit > _.values.length) {

                            //set selected
                            _addValue(value);
                        } else if (_.opt.limit === 1) {

//                            //if limit is 1, update value by click
//                            if (_.values.length === 1) {
//                                _removeValue(_.values[0], true);
//                            }
//                            _addValue(value);

                            //if limit is 1, dont save value
                            _.dd.refreshList(_.values);
                            return;

                        } else {

                            //set value to select
                            _.el.val(_.values);
                            _.dd.refreshList(_.values);
                        }
                    }
                } else {

                    //set value to select
                    _.el.val(_.values);
                    _.dd.refreshList(_.values);
                }
            }
        });

        //get start values
        var values = _.el.val();
        if (values) {
            for (var i = 0; i < values.length; i++) {
                if (values[i] !== '') { // && values[i] !== 0

                    _.values.push(values[i]);

                    // get index                    
                    var opt = _.el.find('option[value="' + values[i] + '"]');

                    // select list option by index
                    _.el.next('.ffbdropdown-main')
                        .find('.ffbdropdown-list')
                            .find('li[data-value="' + opt.index() +  '"]')
                                .addClass('in-list');
                }
            }
        }

        //hide empty value from list
        _.el.find('option').each(function(i, opt) {

            if ($(opt).val() === '') {
                _.el.next('.ffbdropdown-main')
                    .find('.ffbdropdown-list')
                        .find('li[data-value="' + i +  '"]')
                            .addClass('hide');
            }
        });

        //render list
        _renderList();
    };

    /**
     * Save values by inplace editor
     *
     * @private
     * @this {MultipleSelect}
     */
    var _saveValue = function() {

        _.parent.find('.editable-button.ok').trigger('click');
    };

    /**
     * Get function from string
     *
     * @public
     * @this {MultipleSelect}
     */
    _.getFunctionsParts = function(functionName) {

        //Check, if function return
        if (typeof(functionName) === 'function') {
            return functionName;
        }

        //If function name parse
        var func = null;

        if (functionName) {
            var parts = functionName.split('.');
            func      = window[parts[0]];
            var i     = 1;
            while(i < parts.length) {
                func = func[parts[i]];
                i++;
            }
        }

        //If function exist in window, return
        if (typeof(func) === 'function') {
            return func;
        } else {
            return false;
        }
    };

    /**
     * Get option index per value
     *
     * @public
     * @this {MultipleSelect}
     * @param {string} value
     * @returns {Number}
     */
    _.getValueIndex = function(value) {

        //hide empty value from list
        return _.el.find('option[value="' + value + '"]').index();
    };

    /**
     * Init file upload
     *
     * @public
     * @this {MultipleSelect}
     * @param {object} element
     */
    _.init = function(element) {

        //check element
        _.el = $(element);
        if (_.el.length === 0) {
            return null;
        }

        //check parent for inplace
        _.parent = _.el.parents('.row');
        if (_.parent.hasClass('editable')) {
            _.parent.addClass('no-buttons');
        }

        // check is disabled
        if (typeof _.el.attr('disabled') !== 'undefined') {
            _.disabled = true;
        }

        //create dropdown and render list
        _render();
    };

    _.init(element);

};
