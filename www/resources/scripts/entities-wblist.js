/*
 * Javascript function used in the template  editentities/whiteblacklist.php 
 */

// Add title/tooltip to all overflown cells
$('.acl_entity, .acl_desc').each(function() {
	var $ele = $(this);
	if (this.offsetWidth < this.scrollWidth)
	$ele.attr('title', $ele.text());
});


/*
 * Tuning for jquery.tablesorter, used on the ACL-tables
 */

// custom function for sorting the non-text columns
function extractCol0(node,table,index) {
	if (node.children.length==0 || node.firstChild.nodeName != "INPUT" ) return "42";
	return ( node.firstChild.checked ? "1" : "2" );
}
function extractCol2(node,table,index) {
	if (node.children.length==0 || node.firstChild.nodeName != "IMG" ) return "null";
	return "__IMG__"+node.firstChild.src
}
function extractCol4(node,table,index) {
	if (node.children.length==0 || node.firstChild.nodeName != "A" ) return "null";
	node=node.firstChild;
	if (node.children.length==0 || node.firstChild.nodeName != "IMG" ) return "null";
	return "__IMG__"+node.firstChild.src
}

// set up tabel sorting
$(document).ready(function() {
	// set up sorting for all tables with class entity_sort
	$("table.entity_sort").tablesorter({textExtraction: { 0: extractCol0, 2: extractCol2, 4: extractCol4 }});
	// update the sorting indices whenever one of the checkboxes is updated
	$("table#entity_blacklist input").change(function() { $("table#entity_blacklist").trigger("update"); });
	$("table#entity_whitelist input").change(function() { $("table#entity_whitelist").trigger("update"); });
});


