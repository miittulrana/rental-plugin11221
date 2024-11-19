/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Lukas Smrcek */
jQuery(function($){
	$.datepicker.regional['cs'] = {
		closeText: 'Potvrdit',
		prevText: 'Předchozí',
		nextText: 'Další',
		currentText: 'Dnes',
		monthNames: ['Leden','Únor','Březen','Duben','Květen','Červen',
		'Červenec','Srpen','Září','Říjen','Listopad','Prosinec'],
		monthNamesShort: ['LED', 'ÚNO', 'BŘE', 'DUB', 'KVĚ', 'ČER',
		'ČERV', 'SRP', 'ZÁŘ', 'ŘÍJ', 'LIS', 'PRO'],
		dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
		dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
		dayNamesMin: ['Ne','Po','Út','St','Čt','Pá','So'],
		weekHeader: 'Wk',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['cs']);
});
