/* Swedish/SE initialisation for the jQuery UI date picker plugin. */
/* Translated by Kristian Salov. */
jQuery(function($){
	$.datepicker.regional['sv'] = {
		closeText: 'Klar',
		prevText: 'Förra',
		nextText: 'Nästa',
		currentText: 'Idag',
		monthNames: ['Januari','Februari','Mars','April','Maj','Juni',
		'Juli','Augusti','September','Oktober','November','December'],
		monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun',
		'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
		dayNames: ['Söndag', 'Måndag', 'Tisday', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag'],
		dayNamesShort: ['Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör'],
		dayNamesMin: ['Sö','Må','Ti','On','To','Fr','Lö'],
		weekHeader: 'Vecka',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['sv']);
});