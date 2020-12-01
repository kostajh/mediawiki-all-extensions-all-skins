/*!
 * jquery.fixedHeaderTable. The jQuery fixedHeaderTable plugin
 *
 * Copyright (c) 2013 Mark Malek
 * Modified 2014 by Ishmal Lewis for use with MediaWiki
 * http://fixedheadertable.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 *
 * http://docs.jquery.com/Plugins/Authoring
 * jQuery authoring guidelines
 *
 * Launch  : September 2014
 * Version : 1.0
 * Released: September 9th, 2014
 *
 *
 * all CSS sizing (width,height) is done in pixels (px)
 */

(function ($, mw) {

    /* Local scope */

    var fth,
        settings = {};

    /* Utility functions */

    /*
         * Add CSS class to alternating rows
         */
    function _altRows(table, arg1) {
        var $self = $(table),
        altClass = (typeof (arg1) !== 'undefined') ? arg1 : settings.altClass;

        $self.closest('.fht-table-wrapper')
          .find('tbody tr:odd:not(:hidden)')
          .addClass(altClass);
    }

    /**
    * Find all header rows in a thead-less table and put them in a <thead> tag.
    * This only treats a row as a header row if it contains only <th>s (no <td>s)
    * and if it is preceded entirely by header rows. The algorithm stops when
    * it encounters the first non-header row.
    *
    * After this, it will look at all rows at the bottom for footer rows
    * And place these in a tfoot using similar rules.
    * @param $table jQuery object for a <table>
    */
    function _emulateTHeadAndFoot($table) {
        var $rows = $table.find('> tbody > tr');
        if (!$table.get(0).tHead) {
            var $thead = $('<thead>');
            $rows.each(function () {
                if ($(this).children('td').length > 0) {
                    // This row contains a <td>, so it's not a header row
                    // Stop here
                    return false;
                }
                $thead.append(this);
            });
            $table.find(' > tbody:first').before($thead);
        }
        if (!$table.get(0).tFoot) {
            var $tfoot = $('<tfoot>');
            var len = $rows.length;
            for (var i = len - 1; i >= 0; i--) {
                if ($($rows[i]).children('td').length > 0) {
                    break;
                }
                $tfoot.prepend($($rows[i]));
            }
            $table.append($tfoot);
        }
    }

    /*
             * return boolean
             * True if a thead and tbody exist.
             */
    function _isTable($obj) {
        var $self = $obj,
            hasTable = $self.is('table'),
            hasThead = $self.find('thead').length > 0,
            hasTbody = $self.find('tbody').length > 0;

        if (hasTable && hasThead && hasTbody) {
            return true;
        }

        return false;

    }

    /*
     * return void
     * bind scroll event
     */
    function _bindScroll($obj) {
        var $self = $obj,
            $wrapper = $self.closest('.fht-table-wrapper'),
            $thead = $self.siblings('.fht-thead'),
            $tfoot = $self.siblings('.fht-tfoot');

        $self.bind('scroll', function () {
            if (settings.fixedColumns > 0) {
                var $fixedColumns = $wrapper.find('.fht-fixed-column');

                $fixedColumns.find('.fht-tbody table')
                  .css({
                      'margin-top': -$self.scrollTop()
                  });
            }

            $thead.find('table')
              .css({
                  'margin-left': -this.scrollLeft
              });

            if (settings.footer || settings.cloneHeadToFoot) {
                $tfoot.find('table')
                  .css({
                      'margin-left': -this.scrollLeft
                  });
            }
        });
    }

    /*
     * return void
     */
    function _fixHeightWithCss($obj, tableProps) {
        if (settings.includePadding) {
            $obj.css({
                'height': $obj.height() + tableProps.border
            });
        } else {
            $obj.css({
                'height': $obj.parent().height() + tableProps.border
            });
        }
    }

    /*
     * return void
     */
    function _fixWidthWithCss($obj, tableProps, width) {
        if (settings.includePadding) {
            $obj.each(function () {
                $(this).css({
                    'width': width == undefined ? $(this).width() + tableProps.border : width + tableProps.border
                });
            });
        } else {
            $obj.each(function () {
                $(this).css({
                    'width': width == undefined ? $(this).parent().width() + tableProps.border : width + tableProps.border
                });
            });
        }

    }

    /*
     * return void
     */
    function _setupFixedColumn($obj, obj, tableProps) {
        var $self = $obj,
            $wrapper = $self.closest('.fht-table-wrapper'),
            $fixedBody = $wrapper.find('.fht-fixed-body'),
            $fixedColumn = $wrapper.find('.fht-fixed-column'),
            $thead = $('<div class="fht-thead" style="overflow: hidden;position: relative;"><table class="fht-table"><thead><tr></tr></thead></table></div>'),
            $tbody = $('<div class="fht-tbody" style="margin-top: -1;overflow: hidden;position: relative;"><table class="fht-table"><tbody></tbody></table></div>'),
            $tfoot = $('<div class="fht-tfoot" style="overflow: hidden;position: relative;"><table class="fht-table"><tfoot><tr></tr></tfoot></table></div>'),
            fixedBodyWidth = $wrapper.width(),
            fixedBodyHeight = $fixedBody.find('.fht-tbody').height() - settings.scrollbarOffset,
            $firstThChildren,
            $firstTdChildren,
            fixedColumnWidth,
            $newRow,
            firstTdChildrenSelector;

        $thead.find('table.fht-table').addClass(settings.originalTable.attr('class'));
        $tbody.find('table.fht-table').addClass(settings.originalTable.attr('class'));
        $tfoot.find('table.fht-table').addClass(settings.originalTable.attr('class'));

        $firstThChildren = $fixedBody.find('.fht-thead thead tr > *:lt(' + settings.fixedColumns + ')');
        fixedColumnWidth = settings.fixedColumns * tableProps.border;
        $firstThChildren.each(function () {
            fixedColumnWidth += $(this).outerWidth(true);
        });

        // Fix cell heights
        _fixHeightWithCss($firstThChildren, tableProps);
        _fixWidthWithCss($firstThChildren, tableProps);

        var tdWidths = [];
        $firstThChildren.each(function () {
            tdWidths.push($(this).width());
        });

        firstTdChildrenSelector = 'tbody tr > *:not(:nth-child(n+' + (settings.fixedColumns + 1) + '))';
        $firstTdChildren = $fixedBody.find(firstTdChildrenSelector)
          .each(function (index) {
              _fixHeightWithCss($(this), tableProps);
              _fixWidthWithCss($(this), tableProps, tdWidths[index % settings.fixedColumns]);
          });

        // clone header
        $thead.appendTo($fixedColumn)
          .find('tr')
          .append($firstThChildren.clone());

        $tbody.appendTo($fixedColumn)
          .css({
              'margin-top': -1,
              'height': fixedBodyHeight + tableProps.border + 2              
          });

        $firstTdChildren.each(function (index) {
            if (index % settings.fixedColumns == 0) {
                $newRow = $('<tr></tr>').appendTo($tbody.find('tbody'));

                if (settings.altClass && $(this).parent().hasClass(settings.altClass)) {
                    $newRow.addClass(settings.altClass);
                }
            }

            $(this).clone()
              .appendTo($newRow);
        });

        // set width of fixed column wrapper
        $fixedColumn.css({
            'height': 0,
            'width': fixedColumnWidth
        });


        // bind mousewheel events
        var maxTop = $fixedColumn.find('.fht-tbody .fht-table').height() - $fixedColumn.find('.fht-tbody').height();
        $fixedColumn.find('.fht-tbody .fht-table').bind('mousewheel', function (event, delta, deltaX, deltaY) {
            if (deltaY == 0) {
                return;
            }
            var top = parseInt($(this).css('marginTop'), 10) + (deltaY > 0 ? 120 : -120);
            if (top > 0) {
                top = 0;
            }
            if (top < -maxTop) {
                top = -maxTop;
            }
            $(this).css('marginTop', top);
            $fixedBody.find('.fht-tbody').scrollTop(-top).scroll();
            return false;
        });


        // set width of body table wrapper
        $fixedBody.css({
            //'width': fixedBodyWidth
        });

        // setup clone footer with fixed column
        if (settings.footer == true || settings.cloneHeadToFoot == true) {
            var $firstTdFootChild = $fixedBody.find('.fht-tfoot tr > *:lt(' + settings.fixedColumns + ')'),
                footwidth;

            _fixHeightWithCss($firstTdFootChild, tableProps);
            $tfoot.appendTo($fixedColumn)
              .find('tr')
              .append($firstTdFootChild.clone());
            // Set (view width) of $tfoot div to width of table (this accounts for footers with a colspan)
            footwidth = $tfoot.find('table').innerWidth();
            $tfoot.css({
                'top': settings.scrollbarOffset,
                'width': footwidth
            });
        }
    }

    /*
     * Setup table structure for fixed headers and optional footer
    */
    function _setupTable(table) {
        var $self = $(table),
            self = table,
            $thead = $self.find('thead'),
            $tfoot = $self.find('tfoot'),
            tfootHeight = 0,
            $wrapper,
            $divHead,
            $divBody,
            $fixedBody,
            widthMinusScrollbar;

        settings.originalTable = $(table).clone();
        settings.includePadding = _isPaddingIncludedWithWidth();
        settings.scrollbarOffset = _getScrollbarWidth();
        settings.themeClassName = settings.themeClass;

        if (settings.width.search('%') > -1) {
            widthMinusScrollbar = $self.parent().width() - settings.scrollbarOffset;
        } else {
            widthMinusScrollbar = settings.width - settings.scrollbarOffset;
        }

        $self.css({
            width: widthMinusScrollbar
        });


        if (!$self.closest('.fht-table-wrapper').length) {
            $self.addClass('fht-table');
            $self.wrap('<div class="fht-table-wrapper" style="overflow: hidden; position: relative;"></div>');
        }

        $wrapper = $self.closest('.fht-table-wrapper');

        if (settings.fixedColumn == true && settings.fixedColumns <= 0) {
            settings.fixedColumns = 1;
        } else if (settings.fixedColumns <= 0) {
            settings.fixedColumns = $thead.find('th.fixedcolumn').length; //Added this for MediaWiki
        }

        if (settings.fixedColumns > 0 && $wrapper.find('.fht-fixed-column').length == 0) {
            $self.wrap('<div class="fht-fixed-body" style="top: 0;left: 0;position: absolute; width: 100%;"></div>');

            $('<div class="fht-fixed-column" style="top: 0;left: 0;z-index: 1;position: absolute;"></div>').prependTo($wrapper);

            $fixedBody = $wrapper.find('.fht-fixed-body');
        }

        $wrapper.css({
            width: settings.width,
            height: settings.height
        })
          .addClass(settings.themeClassName);

        if (!$self.hasClass('fht-table-init')) {
            $self.wrap('<div class="fht-tbody" style="overflow: auto;position: relative;"></div>');
        }

        $divBody = $self.closest('.fht-tbody');

        var tableProps = _getTableProps($self);

        _setupClone($divBody, tableProps.tbody);

        if (!$self.hasClass('fht-table-init')) {
            if (settings.fixedColumns > 0) {
                $divHead = $('<div class="fht-thead" style="overflow: hidden;position: relative;"><table class="fht-table"></table></div>').prependTo($fixedBody);
            } else {
                $divHead = $('<div class="fht-thead" style="overflow: hidden;position: relative;"><table class="fht-table"></table></div>').prependTo($wrapper);
            }

            $divHead.find('table.fht-table')
              .addClass(settings.originalTable.attr('class'))
              .attr('style', settings.originalTable.attr('style'));

            $thead.clone().appendTo($divHead.find('table'));

            $thead.find('tr th').each(function (index) {
                var thisWidth = parseInt(tableProps.tbody[index], 10);
                $divHead.find('table tr th').eq(index).css({ 'min-width': thisWidth });
            });
        } else {
            $divHead = $wrapper.find('div.fht-thead');
        }

        _setupClone($divHead, tableProps.thead);

        $self.css({
            'margin-top': -$divHead.outerHeight(true)
        });

        /*
         * Check for footer
         * Setup footer if present
         */
        if (settings.footer == true) {
            _setupTableFooter($self, self, tableProps);

            if (!$tfoot.length) {
                $tfoot = $wrapper.find('div.fht-tfoot table');
            }

            tfootHeight = $tfoot.outerHeight(true);
        }

        var tbodyHeight = $wrapper.height() - $thead.outerHeight(true) - tfootHeight - tableProps.border;

        $divBody.css({
            'height': tbodyHeight
        });

        $self.addClass('fht-table-init');

        if (typeof (settings.altClass) !== 'undefined') {
            _altRows(self);
        }

        if (settings.fixedColumns > 0) {
            _setupFixedColumn($self, self, tableProps);
        }

        if (!settings.autoShow) {
            $wrapper.hide();
        }

        _bindScroll($divBody, tableProps);

        return self;
    }

    /*
     * return void
     */
    function _setupTableFooter($obj, obj, tableProps) {
        var $self = $obj,
            $wrapper = $self.closest('.fht-table-wrapper'),
            $tfoot = $self.find('tfoot'),
            $divFoot = $wrapper.find('div.fht-tfoot');

        if (!$divFoot.length) {
            if (settings.fixedColumns > 0) {
                $divFoot = $('<div class="fht-tfoot"><table class="fht-table"></table></div>').appendTo($wrapper.find('.fht-fixed-body'));
            } else {
                $divFoot = $('<div class="fht-tfoot"><table class="fht-table"></table></div>').appendTo($wrapper);
            }
        }
        $divFoot.find('table.fht-table').addClass(settings.originalTable.attr('class'));

        switch (true) {
            case !$tfoot.length && settings.cloneHeadToFoot == true && settings.footer == true:

                var $divHead = $wrapper.find('div.fht-thead');

                $divFoot.empty();
                $divHead.find('table')
                  .clone()
                  .appendTo($divFoot);

                break;
            case $tfoot.length && settings.cloneHeadToFoot == false && settings.footer == true:

                $divFoot.find('table')
                  .append($tfoot)
                  .css({
                      'margin-top': -tableProps.border
                  });

                _setupClone($divFoot, tableProps.tfoot);

                break;
        }

    }

    /*
     * return object
     * Widths of each thead cell and tbody cell for the first rows.
     * Used in fixing widths for the fixed header and optional footer.
     */
    function _getTableProps($obj) {
        var tableProp = {
            thead: {},
            tbody: {},
            tfoot: {},
            border: 0
        },
            borderCollapse = 1;

        if (settings.borderCollapse == true) {
            borderCollapse = 2;
        }

        tableProp.border = ($obj.find('th:first-child').outerWidth() - $obj.find('th:first-child').innerWidth()) / borderCollapse;

        $obj.find('thead tr:first-child > *').each(function (index) {
            tableProp.thead[index] = $(this).width() + tableProp.border;
        });

        $obj.find('tfoot tr:first-child > *').each(function (index) {
            tableProp.tfoot[index] = $(this).width() + tableProp.border;
        });

        $obj.find('tbody tr:first-child > *').each(function (index) {
            tableProp.tbody[index] = $(this).width() + tableProp.border;
        });

        return tableProp;
    }

    /*
     * return void
     * Fix widths of each cell in the first row of obj.
     */
    function _setupClone($obj, cellArray) {
        var $self = $obj,
            selector = ($self.find('thead').length) ?
              'thead tr:first-child > *' :
              ($self.find('tfoot').length) ?
              'tfoot tr:first-child > *' :
              'tbody tr:first-child > *',
            $cell;

        $self.find(selector).each(function (index) {
            $cell = ($(this).find('div.fht-cell').length) ? $(this).find('div.fht-cell') : $('<div class="fht-cell"></div>').appendTo($(this));
            
            $cell.css({
                'min-width': parseInt(cellArray[index], 10) + 1
            });

            /*
             * Fixed Header and Footer should extend the full width
             * to align with the scrollbar of the body
             */
            if (!$(this).closest('.fht-tbody').length && $(this).is(':last-child') && !$(this).closest('.fht-fixed-column').length) {
                var padding = Math.max((($(this).innerWidth() - $(this).width()) / 2), settings.scrollbarOffset);
                $(this).css({
                    'padding-right': parseInt($(this).css('padding-right')) + padding + 'px'
                });
            }
        });
    }

    /*
     * return boolean
     * Determine how the browser calculates fixed widths with padding for tables
     * true if width = padding + width
     * false if width = width
     */
    function _isPaddingIncludedWithWidth() {
        var $obj = $('<table class="fht-table"><tr><td style="padding: 10px; font-size: 10px;">test</td></tr></table>'),
            defaultHeight,
            newHeight;

        $obj.addClass(settings.originalTable.attr('class'));
        $obj.appendTo('body');

        defaultHeight = $obj.find('td').height();

        $obj.find('td')
          .css('height', $obj.find('tr').height());

        newHeight = $obj.find('td').height();
        $obj.remove();

        if (defaultHeight != newHeight) {
            return true;
        } else {
            return false;
        }

    }

    /*
     * return int
     * get the width of the browsers scroll bar
     */
    function _getScrollbarWidth() {
        var scrollbarWidth = 0;

        if (!scrollbarWidth) {
            if (/msie/.test(navigator.userAgent.toLowerCase())) {
                var $textarea1 = $('<textarea cols="10" rows="2"></textarea>')
                      .css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body'),
                    $textarea2 = $('<textarea cols="10" rows="2" style="overflow: hidden;"></textarea>')
                      .css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body');

                scrollbarWidth = $textarea1.width() - $textarea2.width() + 2; // + 2 for border offset
                $textarea1.add($textarea2).remove();
            } else {
                var $div = $('<div />')
                      .css({ width: 100, height: 100, overflow: 'auto', position: 'absolute', top: -1000, left: -1000 })
                      .prependTo('body').append('<div />').find('div')
                      .css({ width: '100%', height: 200 });

                scrollbarWidth = 100 - $div.width();
                $div.parent().remove();
            }
        }

        return scrollbarWidth;
    }



    /* Public scope */

    $.fixedHeaderTable = {

        // plugin's default options
        defaults: {
            width: '100%',
            height: '100%',
            themeClass: 'fht-default',
            borderCollapse: true,
            fixedColumns: 0, // fixed first columns
            fixedColumn: false, // For backward-compatibility
            sortable: false,
            autoShow: true, // hide table after its created
            footer: false, // show footer
            cloneHeadToFoot: false, // clone head and use as footer
            autoResize: false, // resize table if its parent wrapper changes size
            create: null // callback after plugin completes
        },

        init: function ($tables, options) {
            settings = $.extend({}, $.fixedHeaderTable.defaults, options);

            // iterate through all the DOM elements we are attaching the plugin to
            return $tables.each(function (i, table) {
                var $self = $(table); // reference the jQuery version of the current DOM element

                // Add the thead here, $self is the table in question
                // Quit if no tbody
                if (!table.tBodies) {
                    return;
                }
                if (!table.tHead) {
                    // No thead found. Look for rows with <th>s and
                    // move them into a <thead> tag or a <tfoot> tag
                    _emulateTHeadAndFoot($self);

                    // Still no thead? Then quit
                    if (!table.tHead) {
                        return;
                    }
                }

                if (_isTable($self)) {
                    _setupTable(table);
                    $.isFunction(settings.create) && settings.create.call(this);
                } else {
                    $.error('Invalid table mark-up');
                }
            });
        }
    };

    fth = $.fixedHeaderTable;

    $.fn.fixedHeaderTable = function (customOptions) {
        return fth.init(this, customOptions);
    };

}(jQuery, mediaWiki));