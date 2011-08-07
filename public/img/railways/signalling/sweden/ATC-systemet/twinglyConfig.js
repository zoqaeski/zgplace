var tw_language = "swedish";
var tw_conf_id = "idg";
var tw_localCss = "/polopoly_fs/2.109!/idg_twingly.css";
//var tw_url = "http://www.idg.se/2.1085/1.169909";

// for handling the special styling of idg blogs
var blogRegexp = /http:\/\/blogg\.idg\.se\//i;

var tw_onComplete = function () {
	var items = jQuery("#tw_link_widget .tw_item");

	jQuery.each(items,
		function (i, val)
		{
			var postLink = jQuery("div[class='headline'] > a",val);
			var sub = jQuery("div[class='sub'] > span", val);
			var url = postLink.attr("href");
			if (blogRegexp.test(url))
			{
				postLink.css("color","#f9990a");
				sub.after("&nbsp;-&nbsp;<a href='http://blogg.idg.se/' style='color:#f9990a;'>IDG blogg</a>");
			}
		}
	)
};