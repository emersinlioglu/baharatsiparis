"use strict";

/**
 * Really Simple Color Picker in jQuery
 *
 * Licensed under the MIT (MIT-LICENSE.txt) licenses.
 *
 * Copyright (c) 2008-2012
 * Lakshan Perera (www.laktek.com) & Daniel Lacy (daniellacy.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
(function ($) {
    /**
     * Create a couple private variables.
     **/
    var selectorOwner,
        activePalette,
        cItterate = 0,
        templates = {
            control: $('<div class="colorPicker-picker">&nbsp;</div>'),
            palette: $('<div id="colorPicker_palette" class="colorPicker-palette" />'),
            swatch: $('<div class="colorPicker-swatch"><span></span></div>'),
            hexLabel: $('<label for="colorPicker_hex">Hexwert</label>'),
            hexField: $('<input type="text" id="colorPicker_hex" />')
        },
        transparent = "transparent",
        empty       = "empty",
        lastColor;
    /**
     * Create our colorPicker function
     **/
    $.fn.colorPicker = function (options) {
        return this.each(function () {

            // Setup time. Clone new elements from our templates, set some IDs, make shortcuts, jazzercise.
            var element = $(this),
                opts = $.extend({}, $.fn.colorPicker.defaults, options),
                defaultColor = $.fn.colorPicker.toHex(
                    (element.val().length > 0) ? element.val() : opts.pickerDefault
                ),
                newControl = templates.control.clone(),
                newPalette = templates.palette.clone().attr('id', 'colorPicker_palette-' + cItterate),
                newHexLabel = templates.hexLabel.clone(),
                newHexField = templates.hexField.clone(),
                paletteId = newPalette[0].id,
                swatch, controlText;
            $.fn.colorPicker.options = opts;
            /**
             * Build a color palette.
             **/

            // add class
            newPalette.addClass($.fn.colorPicker.options.cssClass);
            
            var hr0 = $('<hr>');
            // Eventfarbe
            var headLine = $('<div class="colorpicker-header"><h3>' + ffbTranslator.translate('TTL_COLORPICKER') + '</h3></div>');
            
            // Bestimmen Sie eine Farbe fuer das Event
            var infoText = $('<div class="info-text">' + ffbTranslator.translate('MSG_COLORPICKER') + '</div>');

            headLine.appendTo(newPalette);
            infoText.appendTo(newPalette);
            hr0.appendTo(newPalette);

//            $.fn.colorPicker.bindPalette(
            $.each(opts.colors, function (i) {
                swatch = templates.swatch.clone();
                if (opts.colors[i] === transparent) {
                    swatch.addClass(transparent).text('X');
                    $.fn.colorPicker.bindPalette(newHexField, swatch, transparent);
                } else if (opts.colors[i] === empty) {
                    swatch.addClass(empty);
                    $.fn.colorPicker.bindPalette(newHexField, swatch, ' ');                    
                } else {
                    $(swatch).find('span').css("background-color", "#" + this);
                    $.fn.colorPicker.bindPalette(newHexField, swatch);
                }
                swatch.appendTo(newPalette);
            });

            $.each(opts.addColors, function (i) {
                swatch = templates.swatch.clone();
                if (opts.addColors[i] === transparent) {
                    swatch.addClass(transparent).text('X');
                    $.fn.colorPicker.bindPalette(newHexField, swatch, transparent);
                } else if (opts.colors[i] === empty) {
                    swatch.addClass(empty);
                    $.fn.colorPicker.bindPalette(newHexField, swatch, ' ');            
                } else {
                    $(swatch).find('span').css("background-color", "#" + this);
                    $.fn.colorPicker.bindPalette(newHexField, swatch);
                }
                swatch.appendTo(newPalette);
            });


//            hr1.appendTo(newPalette);

            newHexLabel.attr('for', 'colorPicker_hex-' + cItterate);
            newHexField.attr({
                'id': 'colorPicker_hex-' + cItterate,
                'value': defaultColor
            });
            newHexField.bind("keydown", function (event) {
                if (event.keyCode === 13) {
                    var hexColor = $.fn.colorPicker.toHex($(this).val());
                    // use empty value if hexColor is not set
                    // to allow setting no value
                    var newColor = ($(this).val().length > 0) ? element.val() : "";
                    $.fn.colorPicker.changeColor(hexColor ? hexColor : newColor);
                }
                if (event.keyCode === 27) {
                    $.fn.colorPicker.hidePalette();
                }
            });
            newHexField.bind("keyup", function (event) {
                var hexColor = $.fn.colorPicker.toHex($(event.target).val());
                var newColor = ($(this).val().length > 0) ? element.val() : opts.pickerDefault;
                $.fn.colorPicker.previewColor(hexColor ? hexColor : newColor);
            });

            $('<div class="colorPicker_hexWrap" />').append(newHexLabel).appendTo(newPalette);
            newPalette.find('.colorPicker_hexWrap').append(newHexField);
            if (opts.showHexField === false) {
                newHexField.hide();
                newHexLabel.hide();
            }
            $("body").append(newPalette);
            newPalette.hide();
            /**
             * Build replacement interface for original color input.
             **/
            if (defaultColor === ' ' || defaultColor === '') {
                newControl.addClass(empty);
            } else {
                newControl.css("background-color", defaultColor);
            }
                
            newControl.bind("click", function () {
                if (element.is(':not(:disabled)')) {
                    $.fn.colorPicker.togglePalette($('#' + paletteId), $(this))
                }
            });
            if (options && options.onColorChange) {
                newControl.data('onColorChange', options.onColorChange);
            } else {
                newControl.data('onColorChange', function () {
                });
            }
            if (controlText = element.data('text'))
                newControl.html(controlText)
            element.after(newControl);
            element.bind("change", function () {
                element.next(".colorPicker-picker").css(
                    "background-color", $.fn.colorPicker.toHex($(this).val())
                );
            });
            element.val(defaultColor);
            if (element[0].tagName.toLowerCase() === 'input') {
                try {
                    element.attr('type', 'hidden')
                } catch (err) { // oldIE doesn't allow changing of input.type
                    element.css('visibility', 'hidden').css('position', 'absolute')
                }
            } else {
                element.hide();
            }
            cItterate++;
        });
    };
    /**
     * Extend colorPicker with... all our functionality.
     **/
    $.extend(true, $.fn.colorPicker, {
        /**
         * Return a Hex color, convert an RGB value and return Hex, or return false.
         *
         * Inspired by http://code.google.com/p/jquery-color-utils
         **/
        toHex: function (color) {
// If we have a standard or shorthand Hex color, return that value.
            if (color.match(/[0-9A-F]{6}|[0-9A-F]{3}$/i)) {
                return (color.charAt(0) === "#") ? color : ("#" + color);
// Alternatively, check for RGB color, then convert and return it as Hex.
            } else if (color.match(/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/)) {
                var c = ([parseInt(RegExp.$1, 10), parseInt(RegExp.$2, 10), parseInt(RegExp.$3, 10)]),
                    pad = function (str) {
                        if (str.length < 2) {
                            for (var i = 0, len = 2 - str.length; i < len; i++) {
                                str = '0' + str;
                            }
                        }
                        return str;
                    };
                if (c.length === 3) {
                    var r = pad(c[0].toString(16)),
                        g = pad(c[1].toString(16)),
                        b = pad(c[2].toString(16));
                    return '#' + r + g + b;
                }
// Otherwise we wont do anything.
            } else {
                return '';
            }
        },
        /**
         * Check whether user clicked on the selector or owner.
         **/
        checkMouse: function (event, paletteId) {
            var selector = activePalette,
                selectorParent = $(event.target).parents("#" + selector.attr('id')).length;
            if (event.target === $(selector)[0] || event.target === selectorOwner[0] || selectorParent > 0) {
                return;
            }
            $.fn.colorPicker.hidePalette();
        },
        /**
         * Hide the color palette modal.
         **/
        hidePalette: function () {
            $(document).unbind("mousedown", $.fn.colorPicker.checkMouse);
            $('.colorPicker-palette').hide();
        },
        /**
         * Show the color palette modal.
         **/
        showPalette: function (palette) {
            var hexColor = selectorOwner.prev("input").val();
            palette.css({
                top: selectorOwner.offset().top + (selectorOwner.outerHeight()),
                left: selectorOwner.offset().left
            });
            $("#color_value").val(hexColor);
            palette.show();
            $(document).bind("mousedown", $.fn.colorPicker.checkMouse);
        },
        /**
         * Toggle visibility of the colorPicker palette.
         **/
        togglePalette: function (palette, origin) {
// selectorOwner is the clicked .colorPicker-picker.
            if (origin) {
                selectorOwner = origin;
            }
            activePalette = palette;
            if (activePalette.is(':visible')) {
                $.fn.colorPicker.hidePalette();
            } else {
                $.fn.colorPicker.showPalette(palette);
            }
        },
        /**
         * Update the input with a newly selected color.
         **/
        changeColor: function (value) {
            var displayColor = value;
            
            if (value.length === 0) {               
                displayColor = '#' + $.fn.colorPicker.options.pickerDefault;
                selectorOwner.removeClass(empty);
            } 
            if (value === ' ' || value === '') {
                selectorOwner.addClass(empty);
                displayColor = '';
                value = '';
            } else {
                selectorOwner.removeClass(empty);
            }
            
            selectorOwner.parent().find("input").val(value).change();
            selectorOwner.css("background-color", displayColor);
            selectorOwner.parent().parent().find('span.colorPicker-picker').css("background-color", displayColor);

            $.fn.colorPicker.hidePalette();
//            selectorOwner.data('onColorChange').call(selectorOwner, $(selectorOwner).prev("input").attr("id"), value);

        },
        /**
         * Preview the input with a newly selected color.
         **/
        previewColor: function (value) {
            // removed preview for DEREVK-216
            // set color for inplace edit
//            $('.editable.value.colorPicker-picker.preview').css('background-color', value);
//            selectorOwner.css("background-color", value);
        },
        /**
         * Bind events to the color palette swatches.
         */
        bindPalette: function (paletteInput, element, color) {
            color = color ? color : $.fn.colorPicker.toHex($(element).find('span').css("background-color"));
            element.bind({
                click: function (ev) {
                    lastColor = color;
                    $.fn.colorPicker.changeColor(lastColor);
                },
                mouseover: function (ev) {
                    lastColor = paletteInput.val();
                    $(this).css("border-color", "#BF002B");
                    paletteInput.val(color);
                    $.fn.colorPicker.previewColor(color);
                },
                mouseout: function (ev) {
                    $(this).css("border-color", "#fff");
                    paletteInput.val(selectorOwner.css("background-color"));
                    paletteInput.val(lastColor);
                    $.fn.colorPicker.previewColor(lastColor);
                }
            });
        }
    });
    /**
     * Default colorPicker options.
     *
     * These are publibly available for global modification using a setting such as:
     *
     * $.fn.colorPicker.defaults.colors = ['151337', '111111']
     *
     * They can also be applied on a per-bound element basis like so:
     *
     * $('#element1').colorPicker({pickerDefault: 'efefef', transparency: true});
     * $('#element2').colorPicker({pickerDefault: '333333', colors: ['333333', '111111']});
     *
     **/
    $.fn.colorPicker.defaults = {
        // colorPicker default selected color.
        pickerDefault: "B3B3B3",
        // Default color set.
        colors: [
            'A4DCFF', '72BCD7', '006CD0', 'A7299A', 'FF7BCC', 'FF734D', 'FF2F2F', 'B90000',
            '72DA26', '468C42', '969478', 'FFC066', 'FFD3B0', 'CA8222', 'B3B3B3', '494949'
        ],
        // If we want to simply add more colors to the default set, use addColors.
        addColors: [],
        // Show hex field
        showHexField: true
    };
})(jQuery);
