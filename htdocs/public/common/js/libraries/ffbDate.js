"use strict";

/**
 * Date tools
 *
 * @class
 * @constructor
 * @this ffbDate
 * @return ffbDate
 */
var ffbDate = function() {

    var _ = this;
    //Options
    _.opt = {
        'days'       : 'Sonntag Montag Dienstag Mittwoch Donnerstag Freitag Samstag'
    };

    /**
     * Get CW
     *
     * @see http://stackoverflow.com/questions/7765767/show-week-number-with-javascript
     * @public
     * @this ffbDate
     * @param {Date} date
     * @return {integer} cw
     */
    this.getWeek = function(date) {

        // We have to compare against the first monday of the year not the 01/01
        // 60*60*24*1000 = 86400000
        // 'onejan_next_monday_time' reffers to the miliseconds of the next monday after 01/01

        var day_miliseconds = 86400000;
        var onejan = new Date(date.getFullYear(), 0, 1, 0, 0, 0);
        var onejan_day = (onejan.getDay() === 0) ? 7 : onejan.getDay();
        var days_for_next_monday = (8 - onejan_day);
        var onejan_next_monday_time = onejan.getTime() + (days_for_next_monday * day_miliseconds);

        // If one jan is not a monday, get the first monday of the year
        var first_monday_year_time = (onejan_day > 1) ? onejan_next_monday_time : onejan.getTime();

        // This at 00:00:00
        var this_date = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0);
        var this_time = this_date.getTime();
        var days_from_first_monday = Math.round(((this_time - first_monday_year_time) / day_miliseconds));
        var first_monday_year = new Date(first_monday_year_time);

        // We add 1 to "days_from_first_monday" because if "days_from_first_monday" is *7,
        // then 7/7 = 1, and as we are 7 days from first monday,
        // we should be in week number 2 instead of week number 1 (7/7=1)
        // We consider week number as 52 when "days_from_first_monday" is lower than 0,
        // that means the actual week started before the first monday so that means we are on the firsts
        // days of the year (ex: we are on Friday 01/01, then "days_from_first_monday"=-3,
        // so friday 01/01 is part of week number 52 from past year)
        // "days_from_first_monday<=364" because (364+1)/7 == 52, if we are on day 365, then (365+1)/7 >= 52 (Math.ceil(366/7)=53) and thats wrong
        if (days_from_first_monday >= 0 && days_from_first_monday < 364) {
            var cw = Math.ceil((days_from_first_monday + 1) / 7);
        } else {
            var cw = 52;
        }

        return cw;
    }

    this.getWeekdays = function(nth) {

        return _.opt.days.split(' ');
    }

    this.getWeekday = function(nth) {
        var day = '';
        var days = _.opt.days.split(' ');


        if (nth < 7) {
            day = days[nth];
        } else {
            day = '';
        }
        return day;
    }
}
