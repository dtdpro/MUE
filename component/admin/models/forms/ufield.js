window.addEvent('domready', function() {
	document.formvalidator.setHandler('ufield',
		function (value) {
			regex=/^[^0-9]+$/;
			return regex.test(value);
	});
});

