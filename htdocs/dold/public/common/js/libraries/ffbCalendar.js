"use strict";

/**
 * Calendar
 *
 * @class
 * @constructor
 * @this ffbCalendar
 * @return ffbCalendar
 */
var ffbCalendar = function(id, options) {

    var _ = this;

    //check id, add if not exist
    if (typeof id === 'object' && $(id).attr('id') !== undefined) {
        id = $(id).attr('id');
    } else if (typeof id === 'object') {
        var newId = 'ffbc' + Math.floor(Math.random() * 999999);
        $(id).attr('id', newId);
        id = newId;
    }

    //ID is required
    if (!id) return;

    //Save id
    _.id         = id; //id = input.id
    _.value      = null;
    _.today      = null;
    _.current    = null;
    _.dateObject = new Date();
    _.showed     = false;
    _.divID      = _.id + '-calendar';
    _.dayInSec   = 1000 * 60 * 60 * 24;
    _.min        = null;
    _.max        = null;
    _.dateSptr   = null; //Date separator

    //Options
    _.opt = {
        'type'       : 'flat',                                            //flat, popup
        'className'  : null,                                              //css class name
        'target'     : null,                                              //calendar input or other element, by default input
        'format'     : 'y-m-d',                                           //Date format
        'timeFormat' : 'h:i:s',                                           //Time format
        'startDay'   : 'm',                                               //Week start day [m,s]
        'weeks'      : true,                                              //Show week position [true, false]
        'time'       : false,                                             //Show time controls [true, false]
        'container'  : null,                                              //id of div for output html, if no container show after input
        'readonly'   : false,                                             //Set readonly for input
        'days'       : 'Mo Tu We Th Fr Sa Su',                            //Days names
        'months'     : 'Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec', //Month names
        'cw'         : 'CW',                                              //Calendar Week label
        'events'     : {},                                                //Object with days events {'day in milliseconds' : 'url'}
        'minDate'    : null,                                              //Min date value
        'maxDate'    : null,                                              //Max date value
        'onShow'     : null,
        'onSelect'   : null,
        'onMove'     : null,
        'weekends'   : 'enabled',                                         //Allow click for weekends [enabled, disabled]
        'autoClose'  : false                                              //Close Calendar by outter click
    }

    //Reset options
    for (var key in options) {
        _.opt[key] = options[key];
    }

    //Init calendar
    _.init = function() {

        //Get today in seconds
        if (_.opt.time) {
            _.today = new Date().getTime();
        } else {
            _.today = new Date(_.dateObject.getFullYear(), _.dateObject.getMonth(), _.dateObject.getDate(), 0, 0, 0, 0).getTime();
        }

        //Add readonly attribute to input
        if (_.opt.readonly && $('#' + _.id).length > 0) $('#' + _.id).attr('readonly', true);
        if (_.opt.time && $('#' + _.id).length > 0) $('#' + _.id).addClass('with-time');

        //Parse min and max values
        if (_.opt.minDate) {
            var minSeconds = _.parseDate(_.opt.minDate);
            _.min = new Date(minSeconds.year, minSeconds.month, minSeconds.day, 0, 0, 0, 0).getTime();
        }
        if (_.opt.maxDate) {
            var maxSeconds = _.parseDate(_.opt.maxDate);
            _.max = new Date(maxSeconds.year, maxSeconds.month, maxSeconds.day, 0, 0, 0, 0).getTime();
        }

        //Check for start value in input and parse it
        _.getInputValue();

        //Check for calendar type and add listner to elements to show it
        if (_.opt.type === 'flat') {
            //Render calendar in container or after input
            _.render();
        } else {
            //Add event listner to target or to input
            _.initListener();
        }

    }

    //Parse input value, if is emty get today
    _.getInputValue = function() {

        //Check for input
        if ($('#' + _.id).length > 0) {

            //If no value, return
            var inputValue = $('#' + _.id).val();
            if (!inputValue) {
                _.current = _.today;
                return;
            }

            //Parse date to format
            var cDate = _.parseDate(inputValue);

            //Validate Date
            if (isNaN(cDate.year) || isNaN(cDate.month) || isNaN(cDate.day)) {
                _.current = _.today;
                return;
            }
            if (_.opt.time && (isNaN(cDate.hours) || isNaN(cDate.minutes) || isNaN(cDate.seconds)))  {
                _.current = _.today;
                return;
            }

            //Create js date from value
            if (_.opt.time) {
                var userDate = new Date(cDate.year, cDate.month, cDate.day, cDate.hours, cDate.minutes, cDate.seconds, 0);
            } else {
                var userDate = new Date(cDate.year, cDate.month, cDate.day);
            }

            //Save input date
            _.value = userDate.getTime();

            //Save current value
            _.current = _.value;

        } else {
            _.current = _.today;
        }
    }

    //Add event listener to target or to input
    _.initListener = function() {

        var element = null;

        //Get target element
        if ($('#' + _.id).length > 0) element = $('#' + _.id);
        if ($('#' + _.opt.target).length > 0) element = $('#' + _.opt.target);

        //Check for element and add listner
        if (element) {

            element.on('click', function(event) {

                //update calendar manager
                window.ffbCalendarManager.update();

                //If showed = true so hide calendar, if not show
                if (_.showed === true) {

                    //Hide Calendar
                    element.removeClass('calendar-showed');
                    _.hideCalendar();
                } else {

                    //Show Calendar
                    element.addClass('calendar-showed');
                    _.showCalendar();
                }

                return false;
            });
        }

        //init autoclose
        if (_.opt.autoClose) {

            //init manager and add scope
            window.ffbCalendarManager.init(_);
        }
    }

    //Hide popup calendar
    _.hideCalendar = function() {

        if ($('#' + _.divID).length > 0) $('#' + _.divID).remove();

        _.showed = false;

    }

    //Show popup calendar
    _.showCalendar = function() {

        _.getInputValue();

        _.render();

        _.showed = true;

        var func = _.getFunctionsParts(_.opt.onShow);
        if (func) func(_.id);

    }

    //Start render process
    _.render = function(value) {

        if (!value) {

            //Get current value
            if (_.current) value = _.current;

        }

        //Remove previous calendar
        if ($('#' + _.divID).length > 0) $('#' + _.divID).remove();

        //Get calendar head
        var html = _.getHead(value);

        //Get calendar body
        html += _.getBody(value);

        //Get time controls
        if (_.opt.time) {

            html += _.getTimeControls(value);

        }

        //Get calendar footer
        html += _.getFooter(value);

        //Create calendar div warpper
        var divToInsert = document.createElement('div');
        divToInsert.setAttribute('id', _.divID);
        divToInsert.setAttribute('class', 'ffb-calendar-wrapper');
        if (_.opt.type === 'popup') divToInsert.style.position = 'absolute';

        //Check opt classname
        var className = '';
        if (_.opt.className) {
            className = _.opt.className + ' ';
        }

        //Create calendar div
        var content = '<div class="ffb-calendar ' + className + _.opt.type + (_.opt.time ? ' time':'') + '">';
        content += html;
        content += '</div>';

        divToInsert.innerHTML = content;

        //Insert div in container or after input
        if (_.opt.container) {
            $('#' + _.opt.container).html(divToInsert);
        } else {
            $(divToInsert).insertAfter($('#' + _.id));
        }

        //Init calendar controls
        _.initControls();

    }

    _.getHead = function(value) {

        //Get date Object for value
        var d = new Date(value);
        //Parse months names
        var months = _.opt.months.split(' ');

        //Create header div with 2 left/right links and date in center
        var html = '<div class="ffb-calendar-head">';
        if (_.opt.type === 'popup') html += '<div class="ffb-calendar-year-left"></div>';
        html += '<div class="ffb-calendar-month-left"></div>';
        html += '<div class="ffb-calendar-date" data-value="' + (d.getMonth() + 1) + '.' + d.getFullYear() + '">' + months[d.getMonth()] + ' ' + d.getFullYear() + '</div>';
        html += '<div class="ffb-calendar-month-right"></div>';
        if (_.opt.type === 'popup') html += '<div class="ffb-calendar-year-right"></div>';
        html += '</div>';

        if (_.opt.type === 'popup') {
            var title = '<div class="ffb-calendar-title">Select Date';
            title += '<div class="ffb-calendar-close"></div>';
            title += '</div>';

            html = title + html;
        }

        return html;

    }

    _.getBody = function(value) {

        //Create body table
        var html = '<table class="ffb-calendar-body">';

        //Get day names row
        html += _.getDayNamesRow();

        //Get month days
        html += _.getDayRows(value);

        //Get month first day

        html += '</table>';

        return html;
    }

    _.getTimeControls = function(value) {

        //Get date Object for value
        var d = new Date(value);

        //Create header div with 2 left/right links and date in center
        var html = '<div class="ffb-calendar-time">';
        html += '<input name="sCalendarHours" type="text" maxlength="2" value="' + d.getHours() + '" /> : ';
        html += '<input name="sCalendarMinutes" type="text" maxlength="2" value="' + d.getMinutes() + '" /> : ';
        html += '<input name="sCalendarSeconds" type="text" maxlength="2" value="' + d.getSeconds() + '" />';
        html += '</div>';

        return html;

    }

    _.getDayNamesRow = function() {

        //Create day names row
        var html = '<thead><tr class="ffb-calendar-names-row">';
        //If is week number, add empty column
        if (_.opt.weeks) html += '<th class="ffb-calendar-week-no">' + _.opt.cw + '</th>';
        //Parse day to array
        var days = _.opt.days.split(' ');
        //Move through array and create names columns
        for (var i = 0; i < 7; i++)
            html += '<th>' + days[i] + '</th>';

        html += '</thead></tr>';

        return html;

    }

    _.getDayRows = function(value) {

        //Get date Object for value
        var d            = new Date(value); //Value date
        var day          = new Date(d.getFullYear(), d.getMonth(), 1); //Date Object for while
        var todayDate    = new Date(_.today);
        var todayTime    = new Date(todayDate.getFullYear(), todayDate.getMonth(), todayDate.getDate()).getTime();
        var valueDay     = new Date(_.value); //Current value date Object
        var valueTime    = new Date(valueDay.getFullYear(), valueDay.getMonth(), valueDay.getDate()).getTime();
        var className    = [];
        var dayHTML      = ''; //td with day html
        var html         = ''; //Full body html
        var firstYearDay = new Date(d.getFullYear(), 0, 1); //First year day for weeks number

        while (day.getMonth() === d.getMonth()) {

            html += '<tr class="ffb-calendar-days-row">';

            for (var i = 0; i < 7; i++) {

                //If is first td and are weeks, create weeks column
                if (i === 0 && _.opt.weeks) {

                    var weekNo = null;
                    var tDay = firstYearDay;
                    //If in start week less als 4 days, first week is next week
                    if (firstYearDay.getUTCDay() < 4) weekNo = 1;
                    //Move through weeks to current week
                    while(tDay.getTime() < day.getTime()) {
                        if (tDay.getUTCDay() === 6) weekNo++;
                        tDay = new Date(tDay.getTime() + _.dayInSec + 3600000);
                        tDay = new Date(tDay.getFullYear(), tDay.getMonth(), tDay.getDate());
                    }
                    //If is html, create column, else empty
                    if (weekNo)
                        html += '<td class="ffb-calendar-week-no" data-value="' + weekNo + '">' + weekNo + '</td>';
                    else
                        html += '<td class="ffb-calendar-week-no">&nbsp;</td>';

                }
                //Add classes for next and previous months
                if (i < day.getUTCDay()) html += '<td class="ffb-calendar-prev-month">&nbsp;</td>';
                else if (d.getMonth() !== day.getMonth()) html += '<td class="ffb-calendar-next-month">&nbsp;</td>';
                else {
                    //Create classes for current month
                    className = [];
                    if (_.value && day.getTime() === valueTime) className.push('ffb-calendar-value');
                    if (day.getTime() === todayTime) className.push('ffb-calendar-today');

                    var dayString = _.getValue(day.getTime());
                    if (_.opt.events[dayString]) {
                        dayHTML = '<a href="' + _.opt.events[dayString] + '">' + day.getDate() + '</a>';
                    }
                    else dayHTML = day.getDate();

                    //Check for min and max value
                    if (_.min && day.getTime() < _.min) className.push('ffb-calendar-disabled');
                    if (_.max && day.getTime() > _.max) className.push('ffb-calendar-disabled');

                    //Check weekend
                    if (_.opt.weekends === 'disabled' &&
                        (day.getDay() === 0 || day.getDay() === 6)
                    ) {
                        className.push('ffb-calendar-disabled');
                    }

                    //Create td html with classes
                    if (className.length !== 0) {
                        className = ' class="' + className.join(' ') + '"';
                    } else {
                        className = '';
                    }

                    html += '<td' + className + '>' + dayHTML + '</td>';

                }

                if (i === day.getUTCDay()) {
                    day = new Date(day.getTime() + _.dayInSec + 3600000);
                    day = new Date(day.getFullYear(), day.getMonth(), day.getDate());
                }
            }

            html += '</tr>';

        }

        return html;

    }

    _.getFooter = function(value) {

        var html = '';

        if (_.opt.type === 'popup') {
            var footer = '<div class="ffb-calendar-footer"></div>';
            html = footer;
        }

        return html;

    }

    _.initControls = function() {

        // action click on month
        $('.ffb-calendar-date').click(function(event){

            if (_.opt.onCMClick) {
               var val = $(this).attr("data-value");
                val = val.split('.');
                _.opt.onCMClick('month/' + val[0] + '/year/' + val[1]);
            }
            return false;
        });

        $('.ffb-calendar-week-no').click(function(event){
            if (_.opt.onCWClick) {

                var week = $(this).attr("data-value");
                var val = $('.ffb-calendar-date').attr('data-value');
                val = val.split('.');
                var day = parseInt($(this).nextAll('td:not(.ffb-calendar-prev-month)').first().html());
                _.opt.onCWClick('cw/' + week + '/day/' + day  + '/month/' + val[0] + '/year/' + val[1]);
            }
        });

        var mLeft = $('#' + _.divID + ' .ffb-calendar-month-left');
        var mRight = $('#' + _.divID + ' .ffb-calendar-month-right');

        if (mLeft[0])
            $(mLeft[0]).on('click', function(event) {
                _.moveMonth('prev');
                return false;
            });

        if (mRight[0])
            $(mRight[0]).on('click', function(event) {
                _.moveMonth('next');
                return false;
            });

        var tds = $('#' + _.divID + ' td');
        tds.each(function(i, td) {
            var td = $(td);
            td.unbind().on('click', function(event) {

                if (parseInt(this.innerHTML) > 0 && !td.hasClass('ffb-calendar-week-no') && !td.hasClass('ffb-calendar-disabled')) {
                    var d = new Date(_.current);

                    if (_.opt.time) {

                        var timeValues = _.getTimeValues();
                        _.value = new Date(d.getFullYear(), d.getMonth(), parseInt(this.innerHTML), timeValues.hours, timeValues.minutes, timeValues.seconds, 0).getTime();

                    } else {

                        _.value = new Date(d.getFullYear(), d.getMonth(), parseInt(this.innerHTML)).getTime();
                    }

                    if ($('#' + _.id).length > 0) {
                        $('#' + _.id).val(_.getValue());
                    }
                    if (_.opt.type === 'popup') _.hideCalendar();

                    var func = _.getFunctionsParts(_.opt.onSelect);
                    if (func) func(_.id, _.getValue(), _.dateSptr);

                    if (_.opt.onCDClick) {
                        var val = $('.ffb-calendar-date').attr('data-value');
                        val = val.split('.');
                        var day = parseInt(this.innerHTML);
                        _.opt.onCDClick('day/' + day + '/month/' + val[0] + '/year/' + val[1]);
                    }
                } else if (td.hasClass('ffb-calendar-week-no')) {

                    if (_.opt.onCWClick) {

                        var week = $(this).attr("data-value");
                        var val = $('.ffb-calendar-date').attr('data-value');
                        val = val.split('.');
                        var day = parseInt($(this).nextAll('td:not(.ffb-calendar-prev-month)').first().html());
                        _.opt.onCWClick('cw/' + week + '/day/' + day  + '/month/' + val[0] + '/year/' + val[1]);
                    }
                }

                return false;

            });
        });

        //Init close button, previous and next year
        if (_.opt.type === 'popup') {

            var closeButton = $('#' + _.divID + ' .ffb-calendar-close');
            if (closeButton[0])
                $(closeButton[0]).on('click', function(event) {
                    _.hideCalendar();
                    return false;
                });

            var yLeft = $('#' + _.divID + ' .ffb-calendar-year-left');
            var yRight = $('#' + _.divID + ' .ffb-calendar-year-right');

            if (yLeft[0])
                $(yLeft[0]).on('click', function(event) {
                    _.moveYear('prev');
                    return false;
                });

            if (yRight[0])
                $(yRight[0]).on('click', function(event) {
                    _.moveYear('next');
                    return false;
                });
        }

        //Init time inputs
        if (_.opt.time) {

             $('#' + _.divID + ' .ffb-calendar-time input').each(function(i, input) {

                 $(input).on('keydown', function(event) {

                     var key = event.keyCode || event.charCode;
                     if ((key < 48 || key > 57) && (key < 96 || key > 105) && key != 8 && key != 46) return false;

                 });

                 $(input).on('blur', function(event) {

                     if (isNaN(this.value)) this.value = 0;

                     switch (this.name) {
                         case 'sCalendarHours':

                             if (parseInt(this.value) > 23) this.value = 23;
                             if (parseInt(this.value) < 0) this.value = 0;

                             break;
                         case 'sCalendarMinutes':
                         case 'sCalendarSeconds':

                             if (parseInt(this.value) > 59) this.value = 59;
                             if (parseInt(this.value) < 0) this.value = 0;

                             break;
                     }

                 });

             });

        }

    }

    _.moveMonth = function(direction) {

        var d = new Date(_.current);

        var year = d.getFullYear();
        var month = d.getMonth();

        if (direction === 'prev') {

            if (month === 0) {
                year--;
                month = 11;
            } else {
                month--;
            }

            _.current = new Date(year, month, 1, d.getHours(), d.getMinutes(), d.getSeconds(), 0).getTime();

        } else {

            if (month === 11) {
                year++;
                month = 0;
            } else {
                month++;
            }

            _.current = new Date(year, month, 1, d.getHours(), d.getMinutes(), d.getSeconds(), 0).getTime();
        }

        _.render(_.current);

        var func = _.getFunctionsParts(_.opt.onMove);
        if (func) func(_.id);

    }

    _.moveYear = function(direction) {

        var d = new Date(_.current);

        var year  = d.getFullYear();
        var month = d.getMonth();
        var day   = d.getDate();

        if (direction === 'prev') {

            year--;
            _.current = new Date(year, month, day, d.getHours(), d.getMinutes(), d.getSeconds(), 0).getTime();

        } else {

            year++;
            _.current = new Date(year, month, day, d.getHours(), d.getMinutes(), d.getSeconds(), 0).getTime();
        }

        _.render(_.current);

        var func = _.getFunctionsParts(_.opt.onMove);
        if (func) func(_.id);

    }

    _.getTimeValues = function() {

        var result = {
            'hours'   : 0,
            'minutes' : 0,
            'seconds' : 0
        }
        var ids = ['hours', 'minutes', 'seconds'];

        $('#' + _.divID + ' .ffb-calendar-time input').each(function(index, input) {

            result[ids[index]] = parseInt(input.value);

        });

        return result;

    }

    _.getValue = function(value) {

        if (!value) value = _.value;

        var d = new Date(value);
        var html = _.opt.format;
        if (_.opt.time) html += ' ' + _.opt.timeFormat;

        var m = d.getMonth() + 1;
        if (m < 10) m = '0' + m;

        var day = d.getDate();
        if (day < 10) day = '0' + day;

        html = html.replace('y', d.getFullYear());
        html = html.replace('m', m);
        html = html.replace('d', day);

        if (_.opt.time) {

            var hours = d.getHours();
            if (hours < 10) hours = '0' + hours;

            var mins = d.getMinutes();
            if (mins < 10) mins = '0' + mins;

            var secs = d.getSeconds();
            if (secs < 10) secs = '0' + secs;

            html = html.replace('h', hours);
            html = html.replace('i', mins);
            html = html.replace('s', secs);
        }

        return html;
    }

    _.parseDateString = function(value) {

        var result = {};
        var separator = null;

        //Get date separator
        separator = _.opt.format.match(/([\.\-\:\/])/)[0];

        _.dateSptr = separator;

        //Split date and format to m,d,y
        var dateArray   = value.split(separator);
        var formatArray = _.opt.format.split(separator);

        //Go through format and get date parts
        for (var i = 0; i < 3; i++) {

            switch (formatArray[i]) {
                case 'd':
                    result['day'] = dateArray[i];
                    break;
                case 'm':
                    result['month'] = dateArray[i] - 1;
                    break;
                case 'y':
                    result['year'] = dateArray[i];
                    break;
            }
        }

        return result;
    }

    _.parseTimeString = function(value) {

        var result = {};
        var separator = null;

        //Get date separator
        separator = _.opt.timeFormat.match(/([:])/)[0];

        //Split date and format to m,d,y
        var timeArray   = value.split(separator);
        var formatArray = _.opt.timeFormat.split(separator);

        //Go through format and get date parts
        for (var i = 0; i < 3; i++) {

            switch (formatArray[i]) {
                case 'h':
                    result['hours'] = timeArray[i];
                    break;
                case 'i':
                    result['minutes'] = timeArray[i];
                    break;
                case 's':
                    result['seconds'] = timeArray[i];
                    break;
            }
        }

        return result;
    }

    _.parseDate = function(value) {

        var result = {};

        if (_.opt.time) {

            var valueParts = value.split(' ');
            if (valueParts.length === 2) {
                var resultDate = _.parseDateString(valueParts[0]);
                var resultTime = _.parseTimeString(valueParts[1]);
                var result     = $.extend(resultTime, resultDate);
            }
        } else {
            result = _.parseDateString(value);
        }

        return result;
    }

    /**
     * Parse function name or call function
     *
     * @param {function|string}
     * @return function|false
     */
    _.getFunctionsParts = function(functionName) {

        //Check, if function return
        if ($.isFunction(functionName) === true) return functionName;

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
        if ($.isFunction(func)) return func;
        else return false;
    }

    _.init();

}

//ffbCalendarManager
window.ffbCalendarManager = {

    //auto close calendars
    store: [],

    //is initialized
    isInitialized: false,

    //add to store
    add: function(scope) {
        if ($.inArray(scope, window.ffbCalendarManager.store) === -1) {
            window.ffbCalendarManager.store.push(scope);
        }
    },

    //remove from store
    remove: function(scope) {
        var i = $.inArray(scope, window.ffbCalendarManager.store);
        if (i >= 0) {
            window.ffbCalendarManager.store[i].splice(i, 1);
        }
    },

    //update visibility
    update: function() {
        if (this.store.length > 0) {
            for (var i = 0; i < this.store.length; i++) {
                if (this.store[i].showed) {
                    this.store[i].hideCalendar();
                }
            };
        }
    },

    //init
    init: function(obj) {

        if (!window.ffbCalendarManager.isInitialized) {

            //bind global click
            $(document).on('click', function(e) {

                var s = window.ffbCalendarManager.store;

                //ignore empty store
                if (s.length > 0) {

                    //get target
                    var targ = null;
                    if (e.target) {
                        targ = e.target;
                    } else if (e.srcElement) {
                        targ = e.srcElement;
                    }

                    //loop through entries
                    for (var i = 0; i < s.length; i++) {

                        //hide if showed
                        if (s[i].showed &&
                                $(targ).parents('#' + s[i].divID).length === 0
                        ) {
                            s[i].hideCalendar();
                        }
                    };
                }
            });

            window.ffbCalendarManager.isInitialized = true;
        }

        if (typeof obj !== 'undefined') {

            window.ffbCalendarManager.add(obj);
        }
    }
};
