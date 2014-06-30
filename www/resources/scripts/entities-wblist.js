/*
 * Javascript function used in the template  editentities/whiteblacklist.php 
 */

(function() {

// Add title/tooltip to all overflown cells
$('.acl_entity, .acl_desc').each(function() {
	var $ele = $(this);
	if (this.offsetWidth < this.scrollWidth)
	$ele.attr('title', $ele.text());
});


/*
 * Tuning for jquery.tablesorter, used on the ACL-tables
 */

// tablesorter parser for the non-text-based columns
$.tablesorter.addParser({
	id: 'wblistParser',
	is: function(s) { return false; },
	format: function(s, table, cell, cellIndex) {
		var $cell = $(cell);
		if (cellIndex==0) { // first column, contains an input
			var $input = $cell.find('input[type=checkbox]');
			if ($input.length>0) {
				var $checked = $input.is(':checked');
				return $checked ? 2 : 1; // return value 0 is used for fall-through
			}
		}
		else if (cellIndex==2 || cellIndex==4) { // third and fifth column, contains an image
			var $img = $cell.find('img');
			if ($img.length>0) {
				return 1;
			}
		}
		return 0;
	},
	parsed: true,
	type: 'numeric'
});

// set up tabel sorting
$(document).ready(function() {
	// set up sorting for all tables with class entity_sort
	$("table.entity_sort").tablesorter({
		headers: {
			0 : { sorter: 'wblistParser' },
			2 : { sorter: 'wblistParser' },
			4 : { sorter: 'wblistParser' }
		},
		sortList: [[1,"a"],[0,"d"],[2,"a"],[3,"a"]],
		widgets: [ "zebra", "filter" ],
		widgetOptions: {
			filter_functions: {
				0: {
					"V": function(e, n, f, i, $r) { return n==2; },
					"-": function(e, n, f, i, $r) { return n==1; },
				},
				1: true,
				2: {
					"X": function(e, n, f, i, $r) { return n==1; },
					"-": function(e, n, f, i, $r) { return n==0; },
				},
				4: {
					"i": function(e, n, f, i, $r) { return n==1; },
					"-": function(e, n, f, i, $r) { return n==0; },
				},
			},
			filter_placeholder: {
				"search": "search",
				"select": "*",
			},
		},
	});
	// update the sorting indices whenever one of the checkboxes is updated
	$("table#entity_blacklist input").change(function() { $("table#entity_blacklist").trigger("update"); });
	$("table#entity_whitelist input").change(function() { $("table#entity_whitelist").trigger("update"); });
});

// trigger a ui update when the tab becomes visible
$("#tabdiv").on( "tabsactivate", function( event, ui ) {
	ui.newPanel.find(".entity_sort").trigger('applyWidgets');
} );

})();
