"use strict";

/**
 * Custom select
 *
 * @class
 * @param {string|select} element
 * @param {object} opt
 */
var ffbDropdown = function(element, opt) {

    var _ = this;

    /**
     * This property allows to show console logs in debug mode
     * or to suppress them in production.
     *
     * @private
     * @var {boolean}
     */
    var _dbg = false;

    var _currentLi   = null;
    var _div         = null;
    var _fake        = null;
    var _input       = null;
    var _list        = null;
    var _valuesCount = 0;

    _.id          = null;
    _.select      = null;
    _.value       = null;
    _.values      = {};
    _.opt         = {
        'autoClose'         : false,     //close by mouseover or document click
        'className'         : 'default', //Dropdown css class
        'isLink'            : false,     //Show value as link
        'liHeight'          : 25,        //Height of li element
        'listHeight'        : 10,        //Count of values in list
        'onSelect'          : null,      //callBack function
        'postFunc'          : null,      //callBack function after select
        'updateDefaultText' : true,      //Update input value by select
        'valuePrefix'       : ''         //Value prefix for selected options in input
    }

    /**
     * Init contate care logic
     *
     * @public
     * @constructor
     * @this {ffbDropdown}
     * @param {string|select} element
     * @param {object} opt
     */
    _.init = function(element, opt) {

        //Check element type
        if (typeof element === 'string') {
            _.select = _.e(element);
        } else if (typeof element === 'object' && element.tagName.toLowerCase() === 'select') {
            _.select = element;
        }
        if (!_.select) return;

        var isInitilized = $(_.select).closest('.row.select').find('.ffbdropdown-main').length;
        if (isInitilized) {
            return;
        }

        //Check id
        _.id = _.select.id;
        if (!_.id) {
            var id = 'ffbd' + parseInt(Math.random()*9999);
            _.select.setAttribute('id', id);
            _.id = id;
        }

        //Init options
        for (var key in opt) {
            if(_.opt[key] !== undefined) _.opt[key] = opt[key];
        }

        //Get values
        var options       = _.children(_.select);
        var selectedIndex = null;
        _valuesCount      = options.length;

        for (var i = 0; i < _valuesCount; i++) {
            _.values[i] = {
                value     : options[i].value,
                'class'    : options[i].className,
                content   : _.trim(_.strip_tags($(options[i]).text())),
                disabled  : false,
                hasPrepend: options[i].getAttribute('data-prepend')
            }
            if (options[i].selected) {
                _.value       = i;
                selectedIndex = i;
            }
            if (options[i].disabled) _.values[i].disabled = true;
        }

        //Create custom select html
        _.createHTML();

        //Init elements
        _div   = _.e(_.id + '-custom');
        _input = _.e(_.id + '-value');
        _fake  = _.e(_.id + '-fake');
        _list  = _.e(_.id + '-list');

        if (   _currentLi === null
            && _div === null
            && _fake === null
            && _input === null
            && _list === null
        ) {
            return;
        }

        if (selectedIndex !== null) {
            _currentLi = _.children(_list)[selectedIndex];
        } else {
            var li    = _.children(_list)[0];
            var index = 0;
            while (_.value === null) {
                if (!_.hasClass(li, 'disabled')) {
                    _.value = index;
                    if (_.opt.updateDefaultText || index === 0) {
                        var newValue = (_.values[index]) ? _.opt.valuePrefix + _.values[index].content : '';
                        _input.value = newValue;
                    }
                    _currentLi   = li;
                } else {
                    li = _.next(li);
                    index++;
                }
            }
        }

        //Set selected value if selected value != ''
        if (   _.value >= 0
            && typeof _.values[_.value] !== 'undefined'
            && _.values[_.value].value !== ''
        ) {
            _.addClass(_div, 'selected-not-empty');
        } else {
            _.removeClass(_div, 'selected-not-empty');
        }

        //Hide original select
        _.addClass(_.select, 'hide');

        //Init Events
        _.initEvents();
    }

    /**
     * Get all page content rows and update height
     *
     * @private
     * @this {ffbDropdown}
     */
    _.createHTML = function() {

        // get selected value
        var inputValue = (_.values[0]) ? _.values[0].content : null;
        if (_.opt.updateDefaultText) {
            inputValue = (_.values[_.value]) ? _.values[_.value].content : null;
        }

        var isRequired = '';
        // check is required
        if (typeof _.select.getAttribute('data-required') !== 'undefined' && _.select.getAttribute('data-required')) {
            isRequired = _.select.getAttribute('data-required');
        }

        var wrap = _.create('div',
            [
                _.create('input', null, {
                    'id'        : _.id + '-fake',
                    'name'      : _.id + '-fake',
                    'className' : 'ffbdropdown-fake',
                    'type'      : 'text',
                    'readonly'  : 'readonly'
                }),
                _.create('input', null, {
                    'id'        : _.id + '-value',
                    'name'      : _.id + '-value',
                    'className' : 'ffbdropdown-value',
                    'maxlength' : '255',
                    'value'     : inputValue + (_.value === 0 ? isRequired:''),
                    'type'      : 'text',
                    'readonly'  : 'readonly'
                })
            ],
            {
                'className' : 'wrap'
            }
        );

        //Create values list
        var i = 0;
        var lis = [];

        for (var value in _.values) {

            var className = [];
            var liClass   = '';

            if (i === 0) className.push('first');
            if (i === _valuesCount - 1) className.push('last');
            if (_.value === value) className.push('active');
            if (_.values[value].disabled) className.push('disabled');
            className.push(_.values[value]['class']);

            var content = _.values[value].content;
            if (_.opt.isLink) {
                content = _.create('a', _.values[value].content, {
                    'href'  : _.values[_.value].value,
                    'title' : _.values[value].content
                });
            } else if (i === 0) {

                content += isRequired;
            }

            var li = _.create('li', content, {
                'className' : className.join(' '),
                'data-value' : value
            });

            //DEREVK-413/414
            if (_.values[value].hasPrepend) {
                li.setAttribute('data-prepend', _.values[value].hasPrepend);
                _.addClass(li, 'has-prepend');
            }

            lis.push(li);
            i++;
        }

        //Create list html, set max height if will
        var ulStyle = '';
        if (_valuesCount > _.opt.listHeight) ulStyle = 'height:' + _.opt.listHeight * _.opt.liHeight + 'px';
        var ul = _.create('ul', lis, {
            'id'        : _.id + '-list',
            'className' : 'ffbdropdown-list hide',
            'style'     : ulStyle
        });

        //DEREVK-413/414
        var selectHeadline = _.select.getAttribute('data-headline');
        if (selectHeadline) {
            _.addClass(ul, 'has-headline');
            ul.setAttribute('data-headline', selectHeadline);
        }

        //create dropdown
        var div = _.create('div', [wrap, ul], {
            'id' : _.id + '-custom',
            'className' : 'ffbdropdown-main ' + _.opt.className
        });

        //Insert html after parent select
        _.select.parentNode.insertBefore(div, _.next(_.select));
    }

    _.initEvents = function() {

        //Add input Listener (if select is not disabled)
        var disabled = _.select.getAttribute('disabled');
        if (!disabled || 'disabled' !== disabled.toLowerCase()) {
            _.addEvent(_input, 'onclick', function(event) {

                _fake.focus();
                _.toggle();
            });
        }

        //Add key events to fake
        _.addEvent(_fake, 'onkeydown', function(event) {

            switch (event.keyCode) {
                case 38:
                    //Go to previous value
                    _.selectPrevious();
                    break;
                case 40:
                    //Go to next value
                    _.selectNext();
                    break;
                case 13:
                    //Update select value to current
                    _.selectValue();
                    _.stopEvent(event);
                    break;
                default:
                    //Look for value in list
                    _.lookForValue(String.fromCharCode(event.keyCode));
                    break;
            }
        });

        if (_.opt.autoClose === true) {

            //Add toggle to input
            _.addEvent(_input, 'onmouseout', function(event) {

                var target = null;
                if (event.target) target = event.target;
                else if (event.srcElement) target = event.srcElement;
                if (target.nodeType === 3) {
                    // defeat Safari bug
                    target = target.parentNode;
                }

                if (target) {
                    var el = _.parents(target, 'ffbdropdown-main');
                    if (el.length === 0 || el[0] === undefined || el[0].getAttribute('id') !== _.id + '-custom') {
                        _.hide();
                    }
                }
            });

            //Add Listener to list
            _.addEvent(_list, 'onmouseout', function(event) {

                var target = null;
                if (event.target) target = event.target;
                else if (event.srcElement) target = event.srcElement;
                if (target.nodeType === 3) {
                    // defeat Safari bug
                    target = target.parentNode;
                }

                if (target) {
                    var el = _.parents(target, 'ffbdropdown-main');
                    if (el.length === 0 || el[0] === undefined || el[0].getAttribute('id') !== _.id + '-custom') {
                        _.hide();
                    }
                }
            });

        } else {

            //Add Listener to list
            _.addEvent(document, 'onclick', function(event) {

                var target = null;
                if (event.target) target = event.target;
                else if (event.srcElement) target = event.srcElement;
                if (target.nodeType === 3) {
                    // defeat Safari bug
                    target = target.parentNode;
                }

                if (target) {

                    var el = _.parents(target, 'ffbdropdown-main');
                    if (el.length === 0 || el[0] === undefined || el[0].getAttribute('id') !== _.id + '-custom') {
                        _.hide();
                    }
                }
            });
        }

        //Add Listener to lis
        var lis = _.children(_list);
        for (var i = 0; i < _valuesCount; i++) {

            _.addEvent(lis[i], 'onmouseover', function(event) {

                if (!_.hasClass(this, 'disabled')) {

                    if (_currentLi) _.removeClass(_currentLi, 'selected');
                    _currentLi = this;
                    _.addClass(_currentLi, 'selected');
                    //_input.value = _.trim(_currentLi.innerHTML);
                }
            });

            _.addEvent(lis[i], 'onclick', function(event) {

                if (!_.hasClass(this, 'disabled')) _.selectValue();
            });
        }

        //Add Listener to wrapper if targen not input
        var wrap = _.children(_div)[0];
        _.addEvent(wrap, 'onclick', function(event) {

            var target = null;
            if (event.target) target = event.target;
            else if (event.srcElement) target = event.srcElement;
            if (target.nodeType === 3) {
                // defeat Safari bug
                target = target.parentNode;
            }

            // check disabled
            var disabled = _.select.getAttribute('disabled');

            if (   (!disabled || 'disabled' !== disabled.toLowerCase())
                && target
                && event.target
                && event.target.tagName === 'DIV'
            ) {
                _.toggle();
            }
        });
    }

    _.toggle = function() {

        if (_.hasClass(_div, 'active')) _.hide();
        else _.show();
    }

    _.show = function() {

        // check open direction
        var offset   = _.offset(_div);
        var bheight  = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        var ulHeight = _valuesCount > _.opt.listHeight ? _.opt.listHeight + 1 : _valuesCount + 1;

        // update list height before open
        if (_valuesCount > _.opt.listHeight) {
            _list.style.height = (_.opt.listHeight * _.opt.liHeight) + 'px';
        }

        if (Math.abs(offset.top - $(window).scrollTop() - bheight) < _div.offsetHeight * ulHeight) {
            _.addClass(_div, 'open-top');
        } else {
            _.removeClass(_div, 'open-top');
        }

        _.addClass(_div, 'active');
        _.removeClass(_list, 'hide');
        _.scrollToCurrent();
    }

    _.hide = function() {

        _.removeClass(_div, 'active');

        var selected = null;
        var active   = null;
        var lis = _.children(_list);
        for (var i = 0; i < lis.length; i++) {

            if (_.hasClass(lis[i], 'selected')) selected = lis[i];
            if (_.hasClass(lis[i], 'active')) active = lis[i];
        }

        if (selected) _.removeClass(selected, 'selected');
        if (active) {
            _currentLi = active;
            if (_.opt.updateDefaultText) {
                var newValue = _.opt.valuePrefix + _.trim(_.strip_tags($(_currentLi).text()));
                _input.value = newValue;
            }
        } else {
            _currentLi = null;
        }
        _.addClass(_list, 'hide');

    }

    _.selectPrevious = function() {

        if (!_currentLi) {
            _currentLi = _.children(_list)[_valuesCount];
            _.addClass(_currentLi, 'selected');
        }
        if (!_currentLi) return;

        var previous = null;
        var curr = _currentLi;
        do {
            previous = _.prev(curr);
            if (!previous) break;
            if (_.hasClass(previous, 'disabled')) {
                curr = previous;
                previous = null;
            }
        } while(!previous);

        if (previous) {
            _.removeClass(_currentLi, 'selected');
            _currentLi = previous;
            _.addClass(_currentLi, 'selected');
            if (_.opt.updateDefaultText) {
                var newValue = _.opt.valuePrefix + _.trim(_.strip_tags(_currentLi.innerHTML));
                _input.value = newValue;
            }
            _.scrollToCurrent();
        }

    }

    _.selectNext = function() {

        if (!_currentLi) {
            _currentLi = _.children(_list)[0];
            _.addClass(_currentLi, 'selected');
        }
        if (!_currentLi) return;

        var next = null;
        var curr = _currentLi;
        do {
            next = _.next(curr);
            if (!next) break;
            if (_.hasClass(next, 'disabled')) {
                curr = next;
                next = null;
            }
        } while(!next);

        if (next) {
            _.removeClass(_currentLi, 'selected');
            _currentLi = next;
            _.addClass(_currentLi, 'selected');
            if (_.opt.updateDefaultText) {
                var newValue = _.opt.valuePrefix + _.trim(_.strip_tags(_currentLi.innerHTML));
                _input.value = newValue;
            }
            _.scrollToCurrent();
        }

    }

    _.lookForValue = function(value, matchOnly) {

        var valueLength = value.length;
        var lis         = _.children(_list);
        var index       = 0;
        for (var key in _.values) {

            if (   value === _.values[key].value
                || (   typeof matchOnly === 'undefined'
                    && value.toLowerCase() === _.values[key].content.slice(0, valueLength).toLowerCase()
                   )
            ) {

                var li = lis[index];
                if (!_.hasClass(li, 'disabled')) {
                    _.removeClass(_currentLi, 'selected');
                    _currentLi = li;
                    _.addClass(_currentLi, 'selected');
                    if (_.opt.updateDefaultText) {
                        var newValue = _.opt.valuePrefix + _.trim(_.strip_tags(_currentLi.innerHTML));
                        _input.value = newValue;
                    }
                    _list.scrollTop = index * _.opt.liHeight;
                    break;
                }
            }

            index++;
        }
    }

    _.refreshList = function(values) {

        var lis = _.children(_list);
        for (var i = 0; i < _valuesCount; i++) {

            var li = lis[i];
            _.removeClass(li, 'active');
            for (var k = 0; k < values.length; k++) {
                if (li.getAttribute('data-value') === values[k]) {
                    _.addClass(li, 'active');
                }
            }
        }
    }

    _.scrollToCurrent = function() {

        var listOffset  = _.offset(_list).top;
        var liOffset    = _.offset(_currentLi).top;
        _list.scrollTop = liOffset - listOffset;

    }

    _.selectValue = function() {

        var valueIndex = null;
        var lis        = _.children(_list);

        for (var i = 0; i < _valuesCount; i++) {

            if (_.hasClass(lis[i], 'active')) {
                _.removeClass(lis[i], 'active');
            }
            if (lis[i] === _currentLi && !_.hasClass(lis[i], 'disabled')) {
                valueIndex = i;
            }
        }

        if (valueIndex !== null) {

            _.addClass(_currentLi, 'active');

            _.value = valueIndex;

            _.select.value = _.values[_.value].value;

            // Set selected value if selected value != ''
            if (_.value >= 0 && _.values[_.value].value !== '') {
                _.addClass(_div, 'selected-not-empty');
            } else {
                _.removeClass(_div, 'selected-not-empty');
            }

            if (_.opt.updateDefaultText) {
                var newValue = _.opt.valuePrefix + _.trim(_.strip_tags($(_currentLi).text()));
                _input.value = newValue;
            }

            // Sometimes its usefull to debug this part,
            // so please don't remove the log statements.
            var dbgOnSelect = false;
            if (_.opt.onSelect) {
                var func = _.getFunctionsParts(_.opt.onSelect);
                if (dbgOnSelect) {
                    _dbg && console.log('option "onSelect" exists', func);
                }
                if (func) {
                    func(element, _.value, _.values[_.value].value);
                } else if (dbgOnSelect) {
                    _dbg && console.log('func does not exist');
                }
            } else if (dbgOnSelect) {
                _dbg && console.log('option "onSelect" does not exist');
            }

            if (_.opt.postFunc) {
                var func = _.getFunctionsParts(_.opt.postFunc);
                if (func) func(element);
            }

            if (_.opt.isLink) {
                window.location.assign(_.values[_.value].value);
            }

            _.hide();
        }
    }

    _.setValue = function(value) {

        _.lookForValue(value.toString(), true);
        _.selectValue();
    }

    _.setOptionClass = function(index, className) {

        _.addClass(_.children(_list)[index], className);
    }

    _.setOptionHTML = function(index, html) {

        _.children(_list)[index].innerHTML = html;
    }

    /* Dom tools ----------------------------------------------------------------------------------------------------*/

    /**
     * Get element By Id
     *
     * @public
     * @constructor
     * @this {ffbDropdown}
     * @param {string} id
     * @return {object} getElementById
     */
    _.e = function(id) {
        return document.getElementById(id);
    }

    /**
     * Returns the DOM element
     *
     * @param {string} tagName
     * @param {string|object|array} content  String content or DOM element or Array of DOM elements
     * @param {object} options
     * @return {object} newElement
     */
    _.create = function(tagName, content, options) {

        var newElement = document.createElement(tagName);

        //Set options
        for (var i in options) {
            if (!options.hasOwnProperty(i)) continue;
            switch (i) {
                case 'className':
                    newElement.className = options[i];
                    break;
                default:
                    newElement.setAttribute(i, options[i]);
                    break;
            }
        }

        if (!content) return newElement;

        //Set content
        switch (typeof(content)) {
            case 'object':
                if (content.length !== undefined) {
                    for (var k = 0; k < content.length; k++) {
                        newElement.appendChild(content[k]);
                    }
                }
                else newElement.appendChild(content);
                break;
            default:
                newElement.innerHTML = content;
                break;
        }

        return newElement;
    }

    _.sibling = function(n, elem) {

        var r = [];
        for (; n; n = n.nextSibling) {

            if (n.nodeType === 1 && n !== elem) {
                r.push(n);
            }
        }

        return r;
    }

    _.children = function(elem) {

        if (elem) return _.sibling(elem.firstChild);
        else return null;
    }

    /**
     *
     *   1   ELEMENT_NODE
     *   2   ATTRIBUTE_NODE
     *   3   TEXT_NODE
     *   4   CDATA_SECTION_NODE
     *   5   ENTITY_REFERENCE_NODE
     *   6   ENTITY_NODE
     *   7   PROCESSING_INSTRUCTION_NODE
     *   8   COMMENT_NODE
     *   9   DOCUMENT_NODE
     *   10  DOCUMENT_TYPE_NODE
     *   11  DOCUMENT_FRAGMENT_NODE
     *   12  NOTATION_NODE
     */
    _.parents = function(elem, until) {

        var matched = [];
        var cur     = elem.parentNode;

        while (cur && cur.nodeType !== 9) {

            if (until && cur.nodeType === 1 && _.hasClass(cur, until)) {
                matched.push(cur);
            } else if (!until && cur.nodeType === 1) {
                matched.push(cur);
            }

            cur = cur.parentNode;
        }

        return matched;
    }

    _.next = function(element) {

        var next = element.nextSibling;

        if (!next) return null;
        else if (next.nodeType === 1) return next;
        else if (next) return _.next(next);
    }

    _.prev = function(element) {

        var prev = element.previousSibling;

        if (!prev) return null;
        else if (prev.nodeType === 1) return prev;
        else if (prev) return _.prev(prev);
    }

    _.trim = function(text) {

        var whitespace = "[\\x20\\t\\r\\n\\f]";
        var rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" );
        return text == null ? "" : (text + "").replace(rtrim, "");
    }

    _.strip_tags = function(str) {

        return str.replace(/<\/?[^>]+>/gi, '');
    }

    _.offset = function(elem) {

        if (!elem) {
            return {
                'top'  : 0,
                'left' : 0
            }
        }

        var box     = elem.getBoundingClientRect();
        var body    = document.body;
        var docElem = document.documentElement;
        var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
        var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
        var clientTop = docElem.clientTop || body.clientTop || 0;
        var clientLeft = docElem.clientLeft || body.clientLeft || 0;
        var top  = box.top +  scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;

        return {
            'top'  : Math.round(top),
            'left' : Math.round(left)
        }
    }

    //Event responder
    _.createResponder = function(element, eventName, handler) {

        var responder = function(event) {

            if (handler.call(element, event) === false) {

                /*if (event.preventDefault) event.preventDefault();
                if (event.stopPropagation) event.stopPropagation();*/
                event.returnValue = false;
                event.cancelBubble = true;
                event.stopped = true;
                if (!!window.attachEvent === false) return false;
            }
        };

        return responder;
    }

    //Add event crossBrowsers function
    _.addEvent = function(el, evnt, func) {

       if (el === null || el === undefined) {
           return el;
       }

       if (el.addEventListener) {
            el.addEventListener(evnt.substr(2).toLowerCase(), _.createResponder(el, evnt.substr(2).toLowerCase(), func), false);
       } else if (el.attachEvent) {
          el.attachEvent(evnt.toLowerCase(), _.createResponder(el, evnt.toLowerCase(), func));
       } else {
          el[evnt] = _.createResponder(el, evnt.toLowerCase(), func);
       }

       return el;
    }

    //Stop Event
    _.stopEvent = function(event) {

       event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
       if (event.preventDefault) {
           event.preventDefault();
       }

       return false;
    }

    //Get function from string
    _.getFunctionsParts = function(functionName) {

        //Check, if function return
        if (typeof(functionName) === 'function') return functionName;

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
        if (typeof(func) === 'function') return func;
        else return false;
    }

    _.hasClass = function(element, className) {

        if (!element) return element;
        var elementClassName = element.className;
        return (elementClassName.length > 0 && (elementClassName === className || new RegExp("(^|\\s)" + className + "(\\s|$)").test(elementClassName)));
    }

    _.addClass = function(element, className) {

        if (!element) return element;
        if (!_.hasClass(element, className)) element.className += (element.className ? ' ' : '') + className;
        return element;
    }

    _.removeClass = function(element, className) {

        if (!element) return element;
        element.className = element.className.replace(new RegExp("(^|\\s+)" + className + "(\\s+|$)"), ' ');
        return element;
    }

    _.init(element, opt);
}
