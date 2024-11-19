/* English/UK initialisation for the jQuery UI date picker plugin. */
jQuery(function($){
	$.datepicker.regional['el'] = {
		closeText: 'Done',
		prevText: 'Prev',
		nextText: 'Next',
		currentText: 'Today',
		monthNames: ['Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μαίος','Ιούνιος',
		'Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'],
		monthNamesShort: ['Ιαν', 'Φεβρ', 'Μαρτ', 'Απρ', 'Μαι', 'Ιουν',
		'Ιουλ', 'Αυγ', 'Σεπτ', 'Οκτ', 'Νοβ', 'Δεκ'],
		dayNames: ['Κυριακή', 'Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο'],
		dayNamesShort: ['Κυρ', 'Δευτ', 'Τρ', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ'],
		dayNamesMin: ['Κυ','Δε','Τρ','Τετ','Πε','Παρ','Σα'],
		weekHeader: 'Wk',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['el']);
});
