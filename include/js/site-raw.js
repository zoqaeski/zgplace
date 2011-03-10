// ------------------------------------- //
// ZQ Site javascript file. v3.0         //
//                                       //
// Last edit date: 27/04/2007 7:08:12 PM //
// Last Edit By: Miksago aka Micheil.    //
// ------------------------------------- //

// ------------------------------------- //
//		OPTIONS
// ------------------------------------- //

Options = {
	param: "page",
	dataDir: "data"
};

// ------------------------------------- //
//			END OPTIONS
// ------------------------------------- //

String.prototype.contains = function(t) { return this.indexOf(t) >= 0 ? true : false; }
String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.substring(1).toLowerCase();
}

/* Copyright (c) 2006-2007 Mathias Bank (http://www.mathias-bank.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Version 2.1
 *
 * Thanks to
 * Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
 * Tom Leonard for some improvements
 *
 * Modified toonly find it via $(document);
 *
 */


jQuery.fn.extend({
/**
* Returns get parameters.
*
* If the desired param does not exist, null will be returned
*
* To get the document params:
* @example value = $(document).getUrlParam("paramName");
*
*/
	getUrlParam: function(strParamName){
		strParamName = escape(unescape(strParamName));

		var returnVal = new Array();
		var qString = null;
			//document-handler
			if (window.location.search.search(strParamName) > -1 ){

				qString = window.location.search.substr(1,window.location.search.length).split("&");
			}

		if (qString==null){
			return null;
		}

		for (var i=0;i<qString.length; i++){
			if (escape(unescape(qString[i].split("=")[0])) == strParamName){
				returnVal.push(qString[i].split("=")[1]);
			}
		}

		if (returnVal.length==0){
			return null;
		}
		else if (returnVal.length==1){
			return returnVal[0];
		}
		else{
			return returnVal;
		}
	}
});

Site = {
//=> Variables, these are global:
	param: function(){
		var tmp_param = $(document).getUrlParam(Options.param);
		if(tmp_param){
		if(tmp_param.contains("../")){
			return null;
		}else{
			return tmp_param;
		}
		}else{
			return param = Site.param();
		}
	},

//=> Main Functions:
	start: function(){
		Site.Aload();

		$("noscript").remove(); // We don't need this anymore.
	},
	Aload: function() {
		var page = Site.param();

		if(page != null){
			$('#content').empty();
			Site.GetData(page,"#content");
		}
		else {
			$("#content div#HomePage").fadeIn("slow");
		}
	},

// CAN I HAVE SO DATA, SIR?
	GetData: function(what, where){
		$.ajaxSetup( {
			global: false,
			type: "GET",
			dataType: "html"
		});
		var req = $.ajax({
			type: "GET",
			url: Options.dataDir+"/"+what+".html",
			success: function(output){
				$(where).empty();
				$(".error").hide();

				$(where).append("<div id='hiddenContent' style='display:none; visibility:hidden; height:0px!important;'>"+output+"</div>");

				var titleBak = document.title;
				var newTitle = $("#hiddenContent").find("title").text();
				document.title = newTitle+" @ "+titleBak;

				// Breadcrumbs?
					var param = Site.param();
					Site.makeCrumbs (param, newTitle);

				var outie = $("#hiddenContent").find("#body");

				$(where).prepend(outie);



				$("#hiddenContent").remove();

				if(output.length>2000){
					$('<span class="goTop">[<a href="#breadcrumb">top</a>]</span>').appendTo(where)/*.bind('click',function(){

					})*/;
				}

				Site.pageWidgets(output);

				req = null;

			},
			error: function() {
				$(where).empty();
				$(where).prepend("<h1>404: Page Lost</h1><p>It appears that the page you were looking for has been... erm... lost. We apologise for any inconveniences that may have been caused by this error, and will hunt down that missing page. It is also quite possible that the page does not exist (yet).</p><p>Please hit Reload to return to previous page, or pick a link from the menu (it should be floating around somewhere...)</p>");
			}
		});
	},


	pageWidgets: function(req){
		var param = Site.param();
		// Table Of Contents?
			Site.makeToc();
		// PrintPage.

		if(param){
			var location = Options.dataDir+"/"+param+".html";
			$("#printContainer").append('<a href="'+location+'" title="Print This Page">Print This Page</a>');
		}
	},


	makeCrumbs: function (s_path, newTitle) {
		if(s_path){
			var crumbs = s_path.split("\/");

			for (var k=0; k<crumbs.length; k++) {

				page = crumbs[k];
				totalLen = crumbs.length - 1;
				if(k == totalLen){

					if(newTitle.indexOf(': ') != -1){
						newTitle = newTitle.split(": ")[1];
					}

					$("#breadcrumb").append('<a href="?page='+Site.getCrumbTrail(crumbs, k)+'">'+newTitle+'</a>');
				}
				else{
					$("#breadcrumb").append('<a href="?page='+Site.getCrumbTrail(crumbs, k)+'">'+page.capitalize()+'</a> > ');
				}
			}
		}
	},
	getCrumbTrail: function(a_crumbs, n_index) {
		var result = "";
		i = 0;
		for (var k=n_index; k >= 0; k--) {
			result += a_crumbs[i];
			if(k > 0){
				result += "/"
			}
			i++;
		}
		return result;
	},

	makeToc: function(){
		var bookIs = $("#content .toc").attr("id");

		var bookID = "#" + bookIs;
		//alert(bookID);

		var tocData = "data/tocs/"+bookIs+".txt";
		//alert(tocData);

		//var tocPlace = $(.levelTwo).attr("id");
		//alert(tocPlace);

		if(tocData != 'data/tocs/undefined.txt'){
			$.get(tocData, function(data){
				$(".levelTwo").empty();
				$(bookID).html(data);
				$(bookID).slideDown();
			});
		}
	}
};

$(function(){
	Site.start();



	//var pb = history.go(-1);
	//alert(pb);
	//var pbclick = "window.location="+pb+';';
	//$("#backer").attr("onclick",pbclick);
});
