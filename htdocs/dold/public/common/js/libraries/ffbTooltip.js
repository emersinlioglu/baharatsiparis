"use strict";

/**
 * Tooltip tool
 *
 * @class
 * @constructor
 * @this ffbTooltip
 * @return ffbTooltip
 */
var ffbTooltip = function(element) {       

    this.el    = null;
    this.text  = null;
    this.tt = null;
    
    //check element
    this.el = $(element);
    if (this.el.length === 0) return null;

    if (this.el.hasClass('tooltip-active')) return;

    this.text = this.el.attr('data-tooltip');

    this.initListeners();

    this.el.addClass('tooltip-active');
}

/**
 * Init event listeners
 *
 * @private
 * @this ffbTooltip
 */
ffbTooltip.prototype.initListeners = function() {
    
    var self = this;

    this.el.on('mouseover', function() {

        self.showTooltip();
    });

    this.el.on('mouseout', function() {

        self.hideTooltip();
    });
}

/**
 * Hide tooltip
 *
 * @public
 * @this ffbTooltip
 */
ffbTooltip.prototype.hideTooltip = function() {

   if (this.tt) this.tt.remove();
}

/**
 * Show tooltip
 *
 * @public
 * @this ffbTooltip
 */
ffbTooltip.prototype.showTooltip = function() {

    if (this.text) {

        var text = this.text.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');

        this.tt = $('<span>')
        .addClass('tooltip')
        /*.css('left', this.el.position().left + 'px')*/
        .html(text);

        //this.tt.insertAfter(this.el);
        this.el.append(this.tt);
    }
}
