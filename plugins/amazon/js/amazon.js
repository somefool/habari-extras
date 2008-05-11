var amazonSearch = {
	init: function() {
		$('#amazon_search').click(function () {
			amazonSearch.search();
		});
	},
	search: function() {
		spinner.start();

		var query= {};
		
		query['search_index'] = $('#amazon_search_index' ).children("option[@selected]").val();
		query['keywords'] = $('#amazon_keywords' ).val();
		$.post(habari.url.habari + '/admin_ajax/amazon_search', query, amazonSearch.searchShow, 'json');
	},
	searchShow: function(result) {
		spinner.stop();

		if (result.errorMessage) {
			humanMsg.displayMsg(result.errorMessage);
			return;
		}
		
		var amazon_result = $('#amazon-result');
		amazon_result.empty();
		var html = '';
		$( result.Items ).each( function() {
			html += '<div class="container"><div style="float: left; width: 80px;">';
			html += "<a href=\"#\" onclick=\"javascript: amazonSearch.insert('" + this.ASIN + "');\">";
			html += '<img src="' + this.SmallImageURL + '" width="' + this.SmallImageWidth + '" height="' + this.SmallImageHeight + '" alt="" /></a>';
			html += '</div><div style="float: left; margin-left: 8px;">';
			html += '<div class="amazon-search-title">';
			html += "<a href=\"#\" style=\"color: #ffffff;\" onclick=\"javascript: amazonSearch.insert('" + this.ASIN + "');\">";
			html += this.Title + ' (' + this.Binding + ')</a></div>';
			html += '<div class="amazon-search-price">' + this.Price + '</div>';
			html += '</div><div class="amazon-search-clear" style="clear: both;"></div></div>';
		});
		amazon_result.append(html);
	},
	insert: function(asin)
	{
		spinner.start();

		var query= {};
		query['asin'] = asin;
		$.post(habari.url.habari + '/admin_ajax/amazon_insert', query, amazonSearch.insertDo, 'json');
	},
	insertDo: function(result)
	{
		spinner.stop();

		if (result.errorMessage) {
			humanMsg.displayMsg(result.errorMessage);
			return;
		}

		habari.editor.insertSelection(result.html);
	}
}

$(document).ready(function(){
	amazonSearch.init();
});