/*
 *
 * StaticRow widget for jQuery TableSorter 2.0
 * Version 1.0
 *
 * Copyright (c) 2011 Nils Luxton
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */

$.tablesorter.addWidget({

    // Give the new Widget an ID to be used in the tablesorter() call, as follows:
    // $('#myElement').tablesorter({ widgets: ['zebra','staticRow'] });
    id: 'staticRow',

    // "Format" is run on all widgets once when the tablesorter has finished initialising,
    // and then again every time a sort has finished.
    format: function(table) {

        // Use a property of the function to determine
        // whether this is the first run of "Format"
        // (i.e. is this the table's default starting position,
        //  or has it been sorted?)
        if (typeof $(table).data('hasSorted') == 'undefined')
        {
            $(table).data('hasSorted', true); // This will force us into the "else" block the next time "Format" is run

            // "Index" the static rows, saving their current (starting)
            // position in the table inside a data() param on the
            // <tr> element itself for later use.
            $('tbody .static', table).each(function() {
                $(this).data('tableindex', $(this).index());
            });
        }
        else
        {
            // Loop the static rows, moving them to their
            // original "indexed" position, and keep doing
            // this until no more re-shuffling needs doing
            var hasShuffled = true;

            while (hasShuffled)
            {
                hasShuffled = false;
                $('tbody .static', table).each(function() {
                    var targetIndex = $(this).data('tableindex');
                    if (targetIndex != $(this).index())
                    {
                        hasShuffled = true;
                        var thisRow = $(this).detach();
                        var numRows = $('tbody tr', table).length;

                        // Are we trying to be the last row?
                        if (targetIndex >= numRows)
                        {
                            thisRow.appendTo($('tbody', table));
                        }
                        // Are we trying to be the first row?
                        else if (targetIndex == 0)
                        {
                            thisRow.prependTo($('tbody', table));
                        }
                        // No, we want to be somewhere in the middle!
                        else
                        {
                            thisRow.insertBefore($('tbody tr:eq(' + targetIndex + ')', table));
                        }
                    }
                });
            }
        }

        $('tbody .static-last', table).each(function() {
            var row = $(this).detach();
            row.appendTo($('tbody', table));
        });

    }
});
