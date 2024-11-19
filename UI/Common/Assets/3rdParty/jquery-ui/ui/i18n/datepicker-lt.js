/* Lithuanian/LT initialisation for the jQuery UI date picker plugin. */
/* Written by Stuart. */
jQuery(function($){
	$.datepicker.regional['lt'] = {
		closeText: 'Atlikta',
		prevText: 'Atgal',
		nextText: 'Pirmyn',
		currentText: 'Šiandien',
		monthNames: ['Sausis','Vasaris','Kovas','Balandis','Gegužė','Birželis',
		'Liepa','Rugpjūtis','Rugsėjis','Spalis','Lapkritis','Gruodis'],
		monthNamesShort: ['Sau', 'Vas', 'Kov', 'Bal', 'Geg', 'Bir',
		'Lie', 'Rgp', 'Rgs', 'Spa', 'Lap', 'Gru'],
		dayNames: ['Sekmadienis', 'Pirmadienis', 'Antradienis', 'Trečiadienis', 'Ketvirtadienis', 'Penktadienis', 'Šeštadienis'],
		dayNamesShort: ['Sek', 'Pir', 'Ant', 'Tre', 'Ket', 'Pen', 'Šeš'],
		dayNamesMin: ['Se','Pi','An','Tr','Ke','Pe','Še'],
		weekHeader: 'Sav',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['lt']);
});
