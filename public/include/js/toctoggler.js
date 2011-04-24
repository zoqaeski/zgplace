$(document).ready(function() {
	// Slide the TOC out when needed
	$("#toctoggle").text("Show Contents");
	$(".toc > ol").hide();
	$("#toctoggle").toggle(
		function() {
			$("#toctoggle").text("Hide Contents");
			$(".toc > ol").slideDown();
		}, function() {
			$("#toctoggle").text("Show Contents");
			$(".toc > ol").slideUp();
		}
	);

	/*
	 * Hide it when we've gone to a section. Despite being not very
	 * good usability practice (in that the TOC disappears on click
	 * and isn't shown by default), this behaviour is more consistent 
	 * in that we don't skip up and down the page when refreshing.
	 */
	$(".toc li > a").click(function() {
		$(".toc > ol").hide();
		$("#toctoggle").text("Show Contents");
		$("#toctoggle").click();
	});
});

