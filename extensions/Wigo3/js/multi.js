(function ($, mw) {

	var $spinner;

	function multiupdate(req, voteid, val, count) {
		if (req.readyState === 4 && req.status === 200) {
			var results = req.responseText.split(':');
			var sum = 0;
			var i;
			for (i = 0; i < results.length; i++) {
				sum += parseInt(results[i], 10);
			}
			var invalidate = false;
			for (i = 0; i < count; ++i) {
				var span = document.getElementById(voteid + "-" + i + "-result");
				var titlespan = document.getElementById(voteid + "-" + i);
				if (span) {
					//get number of votes
					var currentvotes = parseInt(span.innerHTML, 10);
					//update and invalidate if number of votes changed
					var newvotes = parseInt(results[i], 10);
					if (newvotes !== currentvotes) {
						invalidate = true;
						$(span).text(newvotes);
					}
					//calculate column width and update
					var columndiv = document.getElementById(voteid + "-" + i + "-column");
					if (columndiv) {
						var percent;
						//removeSpinner(voteid+'-'+i);
						if (sum === 0) {
							percent = 0;
						} else {
							percent = newvotes / sum * 100;
						}
						var oldwidth = columndiv.style.width;
						var newwidth = percent + "%";
						if (oldwidth != newwidth) {
							invalidate = true;
							columndiv.style.width = newwidth;
						}
					}
					//add class and bolding if my vote
					if (i == val) {
						//add class
						$(span).addClass("myvote");
						span.style.fontWeight = "bold";
						$(titlespan).addClass("myvote");
						titlespan.style.fontWeight = "bold";
					} else {
						//remove class and bolding
						span.style.fontWeight = "normal";
						$(span).removeClass("myvote");
						titlespan.style.fontWeight = "normal";
						$(titlespan).removeClass("myvote");
					}
				}
			}
			if (invalidate) {
				mw.wigo.invalidate();
			}
		} else {
			alert('An error occurred: ' + req.responseText);
		}
	}

	function multivotesend(voteid, val, count) {
		if (voteid.substr(0, 5) !== 'multi') {
			alert('Invalid vote id');
		} else {
			if ($spinner) {
				$spinner.remove();
			}
			$spinner = $.createSpinner();
			$(document.getElementById(voteid + '-' + val + '-column')
				.parentNode.parentNode.previousSibling.firstChild).append($spinner);
			$.ajax(mw.util.wikiScript(), {
				data: {
					action: 'ajax',
					rs: 'Wigo3\\MultiAjax::vote',
					rsargs: [voteid, val, count]
				}
			})
				.done(function (data, textStatus, req) {
					multiupdate(req, voteid, val, count);
				});
		}
	}

	function multivotegetmyvote(voteId) {
		$.ajax(mw.util.wikiScript(), {
			data: {
				action: 'ajax',
				rs: 'Wigo3\\MultiAjax::getmyvote',
				rsargs: [voteId]
			}
		})
			.done(function (data, textStatus, req) {
				if (req.readyState === 4 && req.status === 200) {
					var i = req.responseText;
					var span = document.getElementById(voteId + i + "-result");
					var titlespan = document.getElementById(voteId + i);
					if (span) {
						$(span).addClass("myvote");
						span.style.fontWeight = "bold";
						$(titleSpan).addClass("myvote");
						titlespan.style.fontWeight = "bold";
					}
				}
			});
	}

	$(document).ready(function () {
		multivotegetmyvote(mw.config.get('wigo3MultiVoteId'));
	});

	mw.multivote = {
		send: multivotesend,
	};

})(jQuery, mediaWiki);
