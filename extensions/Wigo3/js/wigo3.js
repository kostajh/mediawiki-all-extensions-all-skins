
( function ( $, mw ) {

function wigo_ajax(name, args, callback) {
	$.ajax( mw.util.wikiScript(), { data: {
		action: 'ajax',
		rs: 'Wigo3\\WigoAjax::' + name,
		rsargs: args
	} } )
	.done( function ( data, textStatus, jqXHR ) {
		callback( jqXHR );
	} );
}

//for new up-down voting
//ajax return value is in the form of "$pollid:$plus:$minus:$zero:$totalvotes:$totaltooltip:$distribtitle:$myvote:$result"
function wigoupdate2(req)
{
	if ( req.readyState === 4 && req.status === 200 )
	{
		const arr = req.responseText.split( ':' );
		const span = document.getElementById( arr[0] );
		if (span)
		{
			const origvote = parseInt( span.innerHTML,10 );
			const plus = arr[1];
			const minus = arr[2];
			const zero = arr[3];
			const newvote = plus - minus;
			const newtotalvotes = arr[4];
			const newtotaltooltip = arr[5];
			const newdistribtitle = arr[6];
			const myvote = parseInt( arr[7] );

			const upbutton = document.getElementById(arr[0] + "-up");
			const neutralbutton = document.getElementById(arr[0] + "-neutral");
			const downbutton = document.getElementById(arr[0] + "-down");

			if ( upbutton && upbutton.style.display === 'none' ) {
				upbutton.style.display = '';
			}

			if ( neutralbutton && neutralbutton.style.display === 'none' ) {
				neutralbutton.style.display = '';
			}

			if ( downbutton && downbutton.style.display === 'none' ) {
				downbutton.style.display = '';
			}

			if ( origvote !== newvote || span.title !== newtotaltooltip ) {
				$(span).text(newvote);
				span.title = newtotaltooltip;
				//update buttons

				//up
				if (upbutton) {
					if (myvote === 1) {
						$(upbutton).addClass( "myvotebutton" );
					} else {
						$(upbutton).removeClass( "myvotebutton" );
					}
				}
				//neutral
				if (neutralbutton) {
					if (myvote === 0) {
						$(neutralbutton).addClass("myvotebutton");
					} else {
						$(neutralbutton).removeClass("myvotebutton");
					}
				}
				//down
				if (downbutton) {
					if (myvote === -1) {
						$(downbutton).addClass("myvotebutton");
					} else {
						$(downbutton).removeClass("myvotebutton");
					}
				}
				//update distribution bar
				const distribtable = document.getElementById(arr[0] + "-dist");
				if (distribtable) {
					distribtable.title = newdistribtitle;
					//calculate percentages
					let uppercent, downpercent, neutralpercent;
					if (newtotalvotes !== 0) {
						uppercent = (plus / newtotalvotes) * 100;
						downpercent = (minus / newtotalvotes) * 100;
						neutralpercent = (zero / newtotalvotes) * 100;
					} else {
						uppercent = 0;
						downpercent = 0;
						neutralpercent = 0;
					}
					if (uppercent !== 0) {
						uppercent += "%";
					}
					if (neutralpercent !== 0) {
						neutralpercent += "%";
					}
					if (downpercent !== 0) {
						downpercent += "%";
					}
					//update widths
					const tr = distribtable.getElementsByTagName("tr")[0]
					//tr.style.display = "";
					const tds = tr.getElementsByTagName("td");
					tds[0].style.display = "";
					tds[1].style.display = "";
					tds[2].style.display = "";
					tds[0].style.width = uppercent;
					tds[1].style.width = neutralpercent;
					tds[2].style.width = downpercent;
				}
				wigoinvalidate();
			}
		}
	} else {
		alert('An error occurred: ' + req.responseText);
	}
}

function wigoinvalidated(req)
{
	if (req.readyState === 4 && req.status === 200)
	{
		if (req.responseText.substring(0,2) !== "ok") {
			alert('An error occurred while invalidating the cache' + req.responseText);
		}
	} else {
		alert('An error occurred: ' + req.responseText);
	}

}

function wigoinvalidate() {
	wigo_ajax('invalidate',[mw.config.get('wgPageName')],wigoinvalidated);
}

function wigovoteup(voteid)
{
	button = document.getElementById(voteid+'-up');
	button.style.display = 'none';
	wigo_ajax('vote2',[voteid,1],wigoupdate2);
}

function wigovotedown(voteid)
{
	button = document.getElementById(voteid+'-down');
	button.style.display = 'none';
	wigo_ajax('vote2',[voteid,-1],wigoupdate2);
}

function wigovotereset(voteid)
{
	button = document.getElementById(voteid+'-neutral');
	button.style.display = 'none';
	wigo_ajax('vote2',[voteid,0],wigoupdate2);
}

mw.wigo = {
	voteup: wigovoteup,
	votereset: wigovotereset,
	votedown: wigovotedown,
	invalidate: wigoinvalidate,
};

} )( jQuery, mediaWiki );
