window.addEvent('domready', function() {
	document.formvalidator.setHandler('uopt',
		function (value) {
			regex=/^[^0-9]+$/;
			return regex.test(value);
	});
});

