$(document).ready(function() {
	function setHeight() {
		windowHeight = $(window).height();
		content_h = $("div#content").height();
		h = Math.max(content_h, windowHeight);
		$('#sidebar').css('min-height', h);
	};

	$('div#header_message').delay(5000).fadeOut(1000);
	setHeight();

	$(window).resize(function() {
		setHeight();
	});

});