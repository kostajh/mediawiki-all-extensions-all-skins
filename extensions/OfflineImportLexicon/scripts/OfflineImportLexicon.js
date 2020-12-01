/*
  Author: Lia Veja
  License: Quadruple licensed GFDL, GPL, LGPL and Creative Commons Attribution 3.0 (CC-BY-3.0)

  Choose whichever license of these you like best :-)
  This file belongs to: ImportOfflineLexicon extension

*/

var hasVolumeVar = false,
	page = '',
	currentLexicon = [],
	textObj = [],
	selectedProp = [],
	flag = false,
	from = 0,
	index = 0,
	queryText = '',
	selected = [],
	$j = jQuery.noConflict();

function initGlobals() {
	//  alert("Globals");
}

function edit_function( text, save_function, edit_res ) {
	save_function( text = queryText, { summary: 'This is saved using offline editor.', minor: false }, post_save_function );
}
function edit_function_images( text, save_function, edit_res ) {
	save_function( text = queryText, { summary: 'This is saved using offline editor.', minor: false }, post_save_function_images );
}
function post_save_function( save_res ) {
	var status = save_res ? 'Saved!' : 'Not Saved';
	_statusbar = new StatusBar( null, { showCloseButton: true, additive: true, afterTimeoutText: status } );
	_statusbar.show( status );
	//   alert(save_res ? "Saved!" : "Not Saved");
	jQuery( '#lexicon_template' ).hide();
	jQuery( '#volume_template' ).hide();
	//   jQuery('#lemmata_template').hide();
	flag = true;
}

function post_save_function_images( save_res ) {
	var status = save_res ? 'Saved page: ' + page : 'Not Saved';
	_statusbar = new StatusBar( null, { showCloseButton: true, additive: true, afterTimeoutText: status } );
	_statusbar.show( status );
	//   alert(save_res ? "Saved!" : "Not Saved");
	jQuery( '#lexicon_template' ).hide();
	jQuery( '#volume_template' ).hide();
	//   jQuery('#lemmata_template').hide();
	flag = true;
	from++;
	if ( from < currentLexicon.length ) {
		var item = currentLexicon[ from ];
		flag = false;
		var page_name = item.name,
			x = parseInt( page_name.lastIndexOf( '-' ) + 1 ),
			y = parseInt( page_name.lastIndexOf( '.' ) ),
			page_numberx = trimNumber( page_name.substring( x, y ) ),
			//    alert(page_numberx);
			page_number = parseInt( page_numberx ),
			page_numbering = item.val; // this should be worked out!
		setTimeout( function () {
			createImagesTemplate( page_name, page_number, page_numbering );
		}, 1000 );
	} else {
		index++;
		if ( from < currentLexicon.length ) {
			from = 0;
			jQuery( '#lemmata_template' ).clearForm();
			var sel = trim( selected[ index ] ).replace( /-/g, '-2D' ).replace( / /g, '-20' ).replace( /\//g, '-2F' ).replace( /&/g, '%26' ),
				jsonArticlesP = '/-5B-5B@@@-5D-5D/-3FTitle/-3FPart-20of-20Lexicon/-3FPart-20of-20Volume/-3FLanguage/-3FHas-20Digitized/-3FURN/-3FFirst-20Page/-3FLast-20Page/-3FAuthor/-3FCategory/searchlabel%3DlexiconB/limit%3D20/format%3Djson',
				query = wgServer + wgScript + jsonGeneralLink + jsonArticlesP.replace( '@@@', sel );
			getJsonArticle( query );
		}
	}
	return false;
}

// Retrieve lexicons when application starts
jQuery( document ).ready( function () {
	// init status bar
	var status = 'Click on New Lexicon ...';
	_statusbar = new StatusBar( null, { showCloseButton: true, additive: true, afterTimeoutText: status } );
	_statusbar.show( status );
	var query = wgServer + wgScript + jsonGeneralLink + jsonLink;
	getJsonProperties( 'lexicon', query );
	jQuery( '#selectLexicon' ).val( '' );

	jQuery( '#selectLexicon' ).on( 'change', function ( event ) {
		jQuery( '#lexiconList li' ).remove();
		if ( jQuery( '#selectLexicon option:selected' ).val() !== '' || jQuery( '#selectLexicon option:selected' ).val() !== undefined ) {
			jQuery( '#selectVolume option' ).remove();
			var temp = trim( jQuery( '#selectLexicon option:selected' ).attr( 'title' ) ),
				query = wgServer + wgScript + jsonGeneralLink;
			if ( temp !== '' ) {
				query += jsonLinkVolume.replace( '@@@', jQuery( '#selectLexicon option:selected' ).val().replace( /-/g, '-2D' ).replace( / /g, '-20' ) );
				getJsonProperties( 'volume', query );
			}
		}
		jQuery( '#volume' ).show();
		jQuery( '#addVolume' ).show();
		jQuery( '.rightArea' ).show();
		jQuery( '#addlemmatabutton' ).show();
		jQuery( '#lexicon_template' ).hide();
		jQuery( '#volume_template' ).hide();
		return false;
	} );

	jQuery( '#selectVolume' ).on( 'change', function ( event ) {
		jQuery( '#lexiconList li' ).remove();
		if ( jQuery( '#selectVolume option:selected' ).val() !== '' || jQuery( '#selectVolume option:selected' ).val() !== undefined ) {
			var temp = trim( jQuery( '#selectVolume option:selected' ).text() ),
				lexicon = trim( jQuery( '#selectLexicon option:selected' ).text() ),
				query = wgServer + wgScript + jsonGeneralLink;
			if ( temp !== '' ) {
				query += jsonLinkArticle.replace( '@@@', temp.replace( /-/g, '-2D' ).replace( / /g, '-20' ).replace( /\//g, '-2F' ) );
			} else {
				// the case with no volume lexicon
				query += jsonLinkArticleNoVolume.replace( '@@@', lexicon.replace( /-/g, '-2D' ).replace( / /g, '-20' ).replace( /\//g, '-2F' ) );
			}
			getJsonProperties( 'article', query );
		}
		jQuery( '#addVolume' ).show();
		jQuery( '.rightArea' ).show();
		jQuery( '#addlemmatabutton' ).show();
		jQuery( '#lexicon_template' ).hide();
		jQuery( '#volume_template' ).hide();
		return false;
	} );
	jQuery( '#selectoutput' ).on( 'change', function ( event ) {
		if ( jQuery( '#selectoutput option:selected' ).val() !== '' ) {
			return false;
		} else { return false; }
	} );

	// Register listeners
	jQuery( '#btnSearch' ).on( 'click', function () {
		search( jQuery( '#searchKey' ).val() );
		return false;
	} );

	// Trigger search when pressing 'Return' on search key input field
	jQuery( '#searchKey' ).on( 'keypress', function ( e ) {
		if ( e.which == 13 ) {
			search( jQuery( '#searchKey' ).val() );
			e.preventDefault();
			return false;
		}
	} );

	jQuery( '#addLexicon' ).on( 'click', function () {
		jQuery( '#Lexicon' )[ 0 ].reset();
		//   jQuery('#Lexicon').clearForm();
		jQuery( '#lexicon_template' ).show();
		jQuery( '#volume_template' ).hide();
		jQuery( '#lemmata_template' ).hide();
		jQuery( '.rightArea' ).hide();
		return false;
	} );

	jQuery( '#addVolume' ).on( 'click', function () {
		jQuery( '#lexicon_template' ).hide();
		jQuery( '#volume_template' ).show();
		jQuery( '#lemmata_template' ).hide();
		return false;
	} );

	jQuery( '#addlemmatabutton' ).on( 'click', function () {
		jQuery( '#volume_template' ).css( 'display:none;' );
		jQuery( '#lemmata_template' ).show();
		jQuery( '#lemmata_template' ).show();
		return false;
	} );

	jQuery( '#lexiconbutton' ).on( 'click', function () {
		jQuery( '#output' ).val( 'save' );
		if ( validateLexiconForm() ) { createLexiconTemplate(); }
		return false;
	} );

	jQuery( '#addvolumebutton' ).on( 'click', function () {
		alert( jQuery( '#output' ).val() );
		jQuery( '#volume_template' ).show();
		return false;
	} );

	jQuery( '#volumebutton' ).on( 'click', function () {
		jQuery( '#add' ).val( 'save' );
		alert( jQuery( '#add' ).val() );
		if ( validateVolumeForm() ) { createVolumeTemplate(); }
		return false;
	} );
	jQuery( '#lemmatabutton' ).on( 'click', function () {
		jQuery( '#addLemmata' ).val( 'save' );
		if ( validateLemmataForm() ) { createLemmataTemplate(); }
		return false;
	} );
	jQuery( '#newmediabutton' ).on( 'click', function () {
		if ( validateLemmataForm() ) {
			createLemmataTemplate();
		}
		from = 0;
		var item = currentLexicon[ from ];
		flag = false;
		var page_name = item.name,
			x = parseInt( page_name.lastIndexOf( '-' ) + 1 ),
			y = parseInt( page_name.lastIndexOf( '.' ) ),
			page_numberx = trimNumber( page_name.substring( x, y ) ),
			//    alert(page_numberx);
			page_number = parseInt( page_numberx ),
			page_numbering = item.val; // this should be worked out!
		setTimeout( function () {
			createImagesTemplate( page_name, page_number, page_numbering );
		}, 1000 );
		return false;
	} );
	jQuery( '#addimagesbutton' ).on( 'click', function () {
		// count checkboxes and for each of which checkboxes automatically create media metadata
		//  alert(jQuery('#lexiconList input[type=checkbox]:checked').size());
		for ( var j = selected.length; j > 0; j-- ) {
			selected.pop();
		}
		jQuery( '#lexiconList input:checked' ).each( function () {
			selected.push( jQuery( this ).val() );
		} );
		index = 0;
		// get the article first
		var sel = trim( selected[ index ] ).replace( /-/g, '-2D' ).replace( / /g, '-20' ).replace( /\//g, '-2F' ).replace( /&/g, '%26' ),
			jsonArticlesP = '/-5B-5B@@@-5D-5D/-3FTitle/-3FPart-20of-20Lexicon/-3FPart-20of-20Volume/-3FLanguage/-3FHas-20Digitized/-3FURN/-3FFirst-20Page/-3FLast-20Page/-3FAuthor/-3FCategory/searchlabel%3DlexiconB/limit%3D20/format%3Djson',
			query = wgServer + wgScript + jsonGeneralLink + jsonArticlesP.replace( '@@@', sel );
		getJsonArticle( query );
		return false;
	} );

	jQuery( 'body' ).on( 'click', '#lexiconList a', function () {
		findById( jQuery( this ).data( 'identity' ) );
	} );

	jQuery( '#lexiconcancel' ).on( 'click', function () {
		jQuery( '#Lexicon' ).clearForm();
		jQuery( '#lexicon_template' ).hide();
		jQuery( '#selectLexicon' ).trigger( 'change' );

		return false;
	} );
	jQuery( '#volumecancel' ).on( 'click', function () {
		jQuery( '#Volume' ).clearForm();
		jQuery( '#volume_template' ).hide();
		return false;
	} );
	jQuery( '#lemmatacancel' ).on( 'click', function () {
		jQuery( '#Lemmata' ).clearForm();
		jQuery( '#lemmata_template' ).hide();
		return false;
	} );
	jQuery( '#lemmatarefresh' ).on( 'click', function () {
		jQuery( '#Lemmata' ).clearForm();
		return false;
	} );
	// decorations
	jQuery( '.tooltip-target' ).ezpz_tooltip(
		{
		// contentPosition: 'rightStatic'
		}
	);

} );
// Functions

// usage:
// 1.$('form').clearForm();  - clear all elements
// 2.$(':input').clearForm(); - clear only input fields
jQuery.fn.clearForm = function () {
	return this.each( function () {
		var type = this.type, tag = this.tagName.toLowerCase();
		if ( tag == 'form' ) { return jQuery( ':input', this ).clearForm(); }
		if ( type == 'text' || type == 'password' || tag == 'textarea' ) { this.value = ''; } else if ( type == 'checkbox' || type == 'radio' ) { this.checked = false; } else if ( tag == 'select' ) { this.selectedIndex = -1; }
	} );
};

function trimNumber( s ) {
	return s.replace( /^0+/, '' );
}

function checkImagesTemplate() {
	i = 0;
	while ( !flag ) {
		i++;
	}
	return;
}

function getJsonArticle( query ) {
	// this should be worked out!!!!
	/* get the page with metadata and show it on form
{{Cora Lemmata Data
| Title = Abbitte
| Part of Lexicon = 1859-78_Schmid
| Part of Volume = 1859-78_Schmid/Volume_0001
| Language = ger
| URN =  urn:nbn:de:0111-bbf-spo-3211907
| First Page = 13
| Last Page = 17
| Has Digitized = File:1859-78_Schmid-Volume_0001-00000013.jpg;File:1859-78_Schmid-Volume_0001-00000014.jpg;File:1859-78_Schmid-Volume_0001-00000015.jpg;File:1859-78_Schmid-Volume_0001-00000016.jpg;File:1859-78_Schmid-Volume_0001-00000017.jpg;
| Category = Article
}}
{{Authors Data
| Author = Palmer, ...
}} */
	var res = '{{Cora Lemmata Data \n';
	for ( var j = selectedProp.length; j > 0; j-- ) {
		selectedProp.pop();
	}
	jQuery.getJSON( query, function ( data ) {
		jQuery.each( data.items, function ( i, item ) {
			// alert("aici+++");
			var temp = item.title;
			if ( temp !== '' && temp !== undefined ) {
				res += '| Title = ' + temp + '\n';
				jQuery( '#lemmataTitle' ).val( temp );
			}
			temp = item.subtitle;
			if ( temp !== '' && temp !== undefined ) {
				res += '| Subtitle = ' + temp + '\n';
				jQuery( '#lemmataSubtitle' ).val( temp );
			}
			temp = item.part_of_lexicon;
			if ( temp !== '' && temp !== undefined ) {
				res += '| Part of Lexicon = ' + temp + '\n';
			}
			temp = item.part_of_volume;
			if ( temp !== '' && temp !== undefined ) { res += '| Part of Volume = ' + temp + '\n'; }
			res += '| Has Digitized = ';
			var obj = item.has_digitized;
			jQuery( '#lemmataImages' ).text( obj );
			res += obj;
			res += '\n{{Authors \n| Author = ';
			var object = item.author;
			jQuery( '#lemmataAuthor' ).val( object );
			//   res += page+'; \n';
			temp = item.first_page;
			if ( temp != '' && temp != undefined ) {
				res += '| First Page = ' + temp + '\n';
				jQuery( '#lemmataFirstPage' ).val( temp );
			}
			temp = item.last_page;
			if ( temp != '' && temp != undefined ) {
				res += '| Last Page = ' + temp + '\n';
				jQuery( '#lemmataLastPage' ).val( temp );
			}
			temp = item.urn;
			if ( temp != '' && temp != undefined ) { res += '| URN = ' + temp + '\n'; }
			temp = item.language;
			if ( temp != '' && temp != undefined ) {
				res += '| Language = ' + temp + '\n';
				jQuery( '#lemmataLanguage' ).val( temp );
			}
			res += '| Category = ' + item.category + '}}\n';
			temp = item.type;
			jQuery( "#metadataType option[value='" + item.type + "']" ).attr( 'select', true );
			queryText = res;
		} );
		jQuery( '#lemmata_template' ).show();
		jQuery( '#lemmatabutton' ).hide();

	} );
	return false;
}
function validateLexiconForm() {
	// validate and process form here
	jQuery( '.error' ).hide();
	var name = jQuery( 'input#metadataTitle' ).val();
	//   alert(name);
	if ( name == '' ) {
		jQuery( 'label#title_error' ).show();
		jQuery( 'input#metadataTitle' ).trigger( 'focus' );
		return false;
	}
	var short = jQuery( 'input#metadataShortTitle' ).val();
	//   alert(short);
	if ( short == '' ) {
		jQuery( 'label#short_error' ).show();
		jQuery( 'input#metadataShortTitle' ).trigger( 'focus' );
		return false;
	}
	var place = jQuery( 'input#metadataPlaceofPublication' ).val();
	if ( place == '' ) {
		jQuery( 'label#place_error' ).show();
		jQuery( 'input#metadataPlaceofPublication' ).trigger( 'focus' );
		return false;
	}
	var year = jQuery( 'input#metadataYearofPublication' ).val();
	if ( year == '' ) {
		jQuery( 'label#year_error' ).show();
		jQuery( 'input#metadataYearofPublication' ).trigger( 'focus' );
		return false;
	}
	var editor = jQuery( 'input#metadataEditor' ).val();
	if ( editor == '' ) {
		jQuery( 'label#editor_error' ).show();
		jQuery( 'input#metadataEditor' ).trigger( 'focus' );
		return false;
	}
	return true;
}
function createLexiconTemplate() {
	// process form here
	queryText = '{{Cora Lexicon Data  \n';
	var name = jQuery( 'input#metadataTitle' ).val();
	queryText += '| Title = ' + name + ' \n';
	var place = jQuery( 'input#metadataPlaceofPublication' ).val();
	queryText += '| Place of Publication = ' + place + ' \n';
	var year = jQuery( 'input#metadataYearofPublication' ).val();
	queryText += '| Year of Publication = ' + year + ' \n';
	var editor = jQuery( 'input#metadataEditor' ).val();
	queryText += '| Editor = ' + editor + ' \n';
	var has_volume = trim( hasVolume() ) + ' \n';
	queryText += has_volume;
	var sub = jQuery( 'input#metadataSubtitle' ).val();
	if ( sub != '' && sub != undefined ) {
		queryText += '| Subtitle = ' + sub + ' \n';
	}
	var edi = jQuery( 'input#metadataEdition' ).val();
	if ( edi != '' && edi != undefined ) {
		queryText += '| Edition = ' + edi + ' \n';
	}
	var publisher = jQuery( 'input#metadataPublisher' ).val();
	queryText += '| Publisher = ' + publisher + ' \n';
	var language = jQuery( 'input#metadataLanguage' ).val();
	if ( language != '' && language != undefined ) {
		queryText += '| Language = ' + language + ' \n';
	}
	queryText += '| URN = \n';
	queryText += '| IDBBF = \n}}\n';
	queryText += '[[Category:Manually Imported]] \n';
	var short = jQuery( 'input#metadataShortTitle' ).val();
	alert( queryText );
	JsMwApi().page( short ).edit( edit_function );
}

function validateVolumeForm() {
	// validate and process form here

	jQuery( '.error' ).hide();
	var name = jQuery( 'input#volumeTitle' ).val();
	//   alert(name);
	if ( name == '' ) {
		jQuery( 'label#vtitle_error' ).show();
		jQuery( 'input#volumeTitle' ).trigger( 'focus' );
		return false;
	}
	var place = jQuery( 'input#volumePlaceofPublication' ).val();
	if ( place == '' ) {
		jQuery( 'label#vplace_error' ).show();
		jQuery( 'input#volumePlaceofPublication' ).trigger( 'focus' );
		return false;
	}
	var year = jQuery( 'input#volumeYearofPublication' ).val();
	if ( year == '' ) {
		jQuery( 'label#vyear_error' ).show();
		jQuery( 'input#volumeYearofPublication' ).trigger( 'focus' );
		return false;
	}
	var editor = jQuery( 'input#volumeEditor' ).val();
	if ( editor == '' ) {
		jQuery( 'label#veditor_error' ).show();
		jQuery( 'input#volumeEditor' ).trigger( 'focus' );
		return false;
	}
	return true;
}

function createVolumeTemplate() {
	// process form here
	queryText = '{{Cora Volume Data  \n';
	var name = jQuery( 'input#volumeTitle' ).val();
	queryText += '| Title = ' + name + ' \n';
	var place = jQuery( 'input#volumePlaceofPublication' ).val();
	queryText += '| Place of Publication = ' + place + ' \n';
	var year = jQuery( 'input#volumeYearofPublication' ).val();
	queryText += '| Year of Publication = ' + year + ' \n';
	var editor = jQuery( 'input#volumeEditor' ).val();
	queryText += '| Editor = ' + editor + ' \n';
	var sub = jQuery( 'input#volumeSubtitle' ).val();
	queryText += '| Subtitle = ' + sub + ' \n';
	var publisher = jQuery( 'input#volumePublisher' ).val();
	queryText += '| Publisher = ' + publisher + ' \n';
	var fiz = jQuery( 'input#volumeDescription' ).val();
	queryText += '| Physical Description = ' + fiz + ' \n';
	var language = jQuery( 'input#volumeLanguage' ).val(),
		short = jQuery( '#selectLexicon option:selected' ).val(),
		number = jQuery( 'input#volumeNumber' ).val().toString(),
		volumeCount = 1;
	if ( number !== '' ) { volumeCount = parseInt( number ); } else
	if ( jQuery( '#selectVolume option' ).length > 0 ) {
		volumeCount = jQuery( '#selectVolume option' ).length;
		volumeCount++;
	}
	var vol = zeroPad( volumeCount, 2 ),
		has_volume = '| Volume Number = ' + volumeCount + ' \n';
	queryText += has_volume;
	queryText += '| Part of Lexicon = ' + short + ' \n';
	queryText += '| Language = ' + language + ' \n}}\n';
	page = short + '/Volume ' + vol;
	alert( queryText );
	// save new wiki page for volume
	JsMwApi().page( page ).edit( edit_function );
	// create entry point into selectVolume drop down list  directly
	nextProcess( 'fillLexicon()' );
	return false;
}

function nextProcess( param_function ) {
	var t = setTimeout( param_function, 3000 );
}
function fillVolume() {
	// create entry point into selectVolume drop down list by re-query against SMW
	//  alert("after 3 sec....");
	jQuery( '#selectVolume' ).show();
	//  nextProcess("fillLexicon()");
	jQuery( '#selectLexicon' ).trigger( 'change' );
	jQuery( '#selectVolume' ).trigger( 'change' );
	return false;
}
function fillLexicon() {
	//   alert("here");
	// re-write lexicon page carefully
	// ask against lexicon page, get metadata and add new added volume
	jQuery( '#selectVolume' ).hide();
	var query = wgServer + wgScript + jsonGeneralLink + jsonLexicon.replace( '@@@', jQuery( '#selectLexicon option:selected' ).val().replace( '-', '-2D' ).replace( '', '-20' ).replace( '/', '-2F' ) );
	getJsonLexicon( query );
	//  return false;
}
/*
* read corresponding lexicon properties, add new has volume reference and re-write lexicon page using API
* @param query - query string for lexicon properties retrieving
*
* */
function getJsonLexicon( query ) {
	var res = '{{Cora Lexicon Data \n';
	for ( var j = selectedProp.length; j > 0; j-- ) {
		selectedProp.pop();
	}
	jQuery.getJSON( query, function ( data ) {
		jQuery.each( data.items, function ( i, item ) {
			//   alert(item.language);
			res += '| Title = ' + item.title + '\n';
			var temp = item.editor;
			if ( temp != '' && temp != undefined ) { res += '| Editor = ' + temp + '\n'; }
			temp = item.has_volume;
			res += '| Has Volume = ';
			var countItems = 0;
			if ( temp != '' && temp != undefined ) {
				var obj = temp,
					list = obj == null ? [] : ( obj instanceof Array ? obj : [ obj ] );
				for ( j = 0; j < list.length; j++ ) {
					if ( trim( list[ j ] ) !== trim( page ) ) {
						res += list[ j ] + ';';
						//   alert(list[j]);
					} else { countItems += 1; }

				}
				hasVolumeVar = true;
			}
			//  alert(page);

			res += page + '; \n';
			temp = item.idbbf;
			if ( temp != '' && temp != undefined ) { res += '| IDBBF = ' + temp + '\n'; }
			res += '| Place of Publication = ' + item.place_of_publication + '\n';
			temp = item.publisher;
			if ( temp != '' && temp != undefined ) { res += '| Publisher = ' + temp + '\n'; }
			temp = item.language;
			if ( temp != '' && temp != undefined ) { res += '| Language = ' + temp + '\n'; }
			temp = item.edition;
			if ( temp != '' && temp != undefined ) { res += '| Edition = ' + temp + '\n'; }
			temp = item.subtitle;
			if ( temp != '' && temp != undefined ) { res += '| Subtitle = ' + temp + '\n'; }
			temp = item.urn;
			if ( temp != '' && temp != undefined ) { res += '| URN = ' + temp + '\n'; }
			temp = item.year_of_publication;
			var d = String( temp );
			res += '| Year of Publication = ' + d.substring( 0, 4 ) + '\n';
			//   res += "| Year of Publication = "+temp +"\n";
			res += '}}\n\n';
			res += '[[Category:Manualy Imported]]\n';
			alert( res );
			queryText = res;
			var pageLexicon = jQuery( '#selectLexicon option:selected' ).val();
			JsMwApi().page( pageLexicon ).edit( edit_function );
			nextProcess( 'fillVolume()' );
		} );
	} );
	return res;
}

function parseDate( input ) {
	var parts = input.match( /(\d+)/g );
	alert( parts[ 0 ] );
	// new Date(year, month [, date [, hours[, minutes[, seconds[, ms]]]]])
	// return new Date(parts[0], parts[1]-1, parts[2]); // months are 0-based
	return parts[ 0 ];
}

function validateLemmataForm() {
	// validate and process form here
	jQuery( '.error' ).hide();
	var name = jQuery( 'input#lemmataTitle' ).val();
	//   alert(name);
	if ( name == '' ) {
		jQuery( 'label#ltitle_error' ).show();
		jQuery( 'input#lemmataTitle' ).trigger( 'focus' );
		return false;
	}
	var short = jQuery( 'input#lemmataFirstPage' ).val();
	//   alert(short);
	if ( short == '' ) {
		jQuery( 'label#lfirst_error' ).show();
		jQuery( 'input#lemmataFirstPage' ).trigger( 'focus' );
		return false;
	}
	var place = jQuery( 'input#lemmataLastPage' ).val();
	if ( place == '' ) {
		jQuery( 'label#llast_error' ).show();
		jQuery( 'input#lemmataLastPage' ).trigger( 'focus' );
		return false;
	}
	return true;
}
function createLemmataTemplate() {
	// process form here

	queryText = '{{Cora Lemmata Data  \n';
	var name = jQuery( 'input#lemmataTitle' ).val();
	queryText += '| Title = ' + name + ' \n';
	var first = jQuery( 'input#lemmataFirstPage' ).val();
	queryText += '| First Page = ' + first + ' \n';
	var last = jQuery( 'input#lemmataLastPage' ).val();
	queryText += '| Last Page = ' + last + ' \n';
	var firstP = jQuery( 'input#lemmataFirstPageN' ).val(),
		//  queryText += '| First Page = '+firstP+" \n";
		lastP = jQuery( 'input#lemmataLastPageN' ).val(),
		//  queryText += '| Last Page = '+lastP+" \n";
		type = jQuery( '#metadataType option:selected' ).val();
	queryText += '| Category = ' + type + ' \n';
	var sub = jQuery( 'input#lemmataSubtitle' ).val();
	queryText += '| Subtitle = ' + sub + ' \n';
	var language = jQuery( 'input#lemmataLanguage' ).val(),
		short = jQuery( '#selectLexicon option:selected' ).val(),
		volume = jQuery( '#selectVolume option:selected' ).val(),
		has_volume = '';
	if ( volume != '' && volume != undefined ) { has_volume = '| Part of Volume = ' + volume + ' \n'; }
	queryText += has_volume;
	queryText += '| Part of Lexicon = ' + short + ' \n';
	var images = trim( hasImages( first, last ) ) + ' \n';
	queryText += images;
	queryText += '| Language = ' + language + ' \n}}\n';
	var authors = trim( hasAuthors() ) + ' \n';
	queryText += authors;
	var page = '';
	if ( volume != '' && volume != undefined ) { page = volume + '/' + name; } else { page = short + '/' + name; }
	alert( queryText );
	JsMwApi().page( page ).edit( edit_function );
	// evaluate the number of checkboxes
	var checkNum = jQuery( '#lexiconList input[type=checkbox]' ).length;
	jQuery( '#lexiconList' ).append( '<li id="' + checkNum + '"><a href="' + wgServer + wgScript + '/' + page + '" data-identity="' + page + '" target="_blank">' + name + '</a></li>' );
	addCheckBox( jQuery( '#li-' + checkNum ), page, name );
	jQuery( '#addimagesbutton' ).show();
	jQuery( '.rightArea' ).show();
	//     alert("!currentLexicon "+currentLexicon.length);
	return false;
}

function hasImages( first, last ) {
	for ( var j = currentLexicon.length; j > 0; j-- ) {
		currentLexicon.pop();
	}
	var res = '',
		images = jQuery( '#lemmataImages' ).val(),
		firstP = jQuery( 'input#lemmataFirstPageN' ).val(),
		lastP = jQuery( 'input#lemmataLastPageN' ).val(),
		volume = jQuery( '#selectVolume option:selected' ).val(),
		lexicon = jQuery( '#selectLexicon option:selected' ).val(),
		antet = '| Has Digitized = ';
	if ( trim( images ) == '' || images == undefined ) {
		return createImagesFromScratch( antet, first, last, firstP, lastP );
	} else
	if ( images.indexOf( ';' ) != -1 ) {
		var label = getNumbering( firstP, lastP ),
			arrCreators = images.split( ';' ),
			countItems = 0;
		res += antet;
		for ( var i in arrCreators ) {
			countItems += 1;
			if ( volume != '' && volume != undefined ) {
				res += 'File:' + volume.replace( '/', '-' ) + '-' + arrCreators[ i ];
				currentLexicon.push( { name: 'File:' + volume.replace( '/', '-' ) + '-' + arrCreators[ i ], val: label[ i ] } );
			} else {
				res += 'File:' + lexicon + '-' + arrCreators[ i ];
				currentLexicon.push( { name: 'File:' + lexicon + '-' + arrCreators[ i ], val: label[ i ] } );
			}
			if ( countItems < arrCreators.length ) {
				res += ';';
			}
		}
	} else {
		if ( volume != '' && volume != undefined ) {
			res += antet + 'File:' + volume.replace( '/', '-' ) + '-' + images;
			currentLexicon.push( { name: 'File:' + volume.replace( '/', '-' ) + '-' + images, val: firstP } );
		} else {

			res += antet + 'File:' + lexicon + '-' + images;
			currentLexicon.push( { name: 'File:' + lexicon + '-' + images, val: firstP } );
		}
	}
	alert( res );
	return res;
}

function createImagesFromScratch( item, first, last, firstP, lastP ) {
	var res = item,
		countItems = 0,
		volume = jQuery( '#selectVolume option:selected' ).val(),
		lexicon = jQuery( '#selectLexicon option:selected' ).val(),
		label = getNumbering( firstP, lastP ),
		firstN = parseInt( first ),
		lastN = parseInt( last );
	for ( var i = firstN; i <= lastN; i++ ) {
		if ( volume != '' && volume != undefined ) {
			res += 'File:' + volume.replace( '/', '-' ) + '-' + zeroPad( i, 8 ) + '.jpg';
			currentLexicon.push( { name: 'File:' + volume.replace( '/', '-' ) + '-' + zeroPad( i, 8 ) + '.jpg', val: label[ countItems ] } );
		} else {
			res += 'File:' + lexicon + '-' + zeroPad( i, 8 ) + '.jpg';
			currentLexicon.push( { name: 'File:' + lexicon + '-' + zeroPad( i, 8 ) + '.jpg', val: label[ countItems ] } );
		}
		countItems += 1;
		if ( countItems < lastN - firstN + 1 ) {
			res += ';';
		}
	}
	return res;
}
function getNumbering( firstP, lastP ) {
	var label = [];
	if ( firstP.indexOf( ';' ) != -1 ) {
		// string
		alert( 'string' );
		var temp = firstP.split( ';' ),
			temp1 = lastP.split( ';' );
		label = temp.concat( temp1 );
	} else
	if ( !isNaN( firstP ) && !isNaN( lastP ) ) {
		// numeric
		//  alert("numeric");
		label = generateNumericString( firstP, lastP, false );
	} else if ( !isNaN( deromanize( firstP ) ) && !isNaN( deromanize( lastP ) ) ) {
		// roman
		//   alert("roman");
		label = generateNumericString( deromanize( firstP ), deromanize( lastP ), true );
	} else {
		//  alert("wrong");
		label[ 0 ] = firstP;
		label[ 1 ] = lastP;
	}
	return label;
}
function generateNumericString( start, end, roman ) {
	var res = [],
		firstN = parseInt( start ),
		lastN = parseInt( end );
	for ( var i = firstN; i <= lastN; i++ ) {
		if ( roman ) { res.push( romanize( i ) ); } else { res.push( String( i ) ); }
	}
	return res;
}

function createImagesTemplate( page_name, page_number, page_numbering ) {
	// process form here
	/* {{Cora Image Data
        | Original URI =  http://goobiweb.bbf.dipf.de/viewer/content/10168875X_01/800/0/00000013.jpg
        | Rights Holder = BBF - Bibliothek fuer Bildungsgeschichtliche Forschung
        | URN = urn:nbn:de:0111-bbf-spo-3212079
        | Page Number = 13
        | Page Numbering = 6
        | Part of Article =  1859-78_Schmid/Volume_0001/ABC-Buch; 1859-78_Schmid/Volume_0001/ABC-Schďż˝tzen; 1859-78_Schmid/Volume_0001/Abbitte;
        | Category = Digitized;Encyklopďż˝die des gesammten Erziehungs- und Unterrichtswesens
        }} */
	//    alert("createImagesTemplate");
	// get the name of articles referring this page
	for ( var j = selectedProp.length; j > 0; j-- ) {
		selectedProp.pop();
	}
	var pag = page_name.replace( /-/g, '-2D' ),
		pag1 = pag.replace( / /g, '-20' ),
		query = wgServer + wgScript + jsonGeneralLink + jsonArticles.replace( '@@@', pag1 );
	jQuery.getJSON( query, function ( data ) {
		jQuery.each( data.items, function ( i, item ) {
			var prop = item.label;
			if ( existsInTab( prop, selectedProp ) < 0 ) {
				//   alert(prop);
				flag = true;
				selectedProp.push( { name: prop, val: item.title } );
			}
			if ( i == 100 ) { return false; }
		} );
		//  alert("images");
		var countItems = 0,
			res = '';
		for ( var j = 0; j < selectedProp.length; j++ ) {
			res += selectedProp[ j ].name;
			countItems += 1;
			if ( countItems < selectedProp.length ) {
				res += ';';
			}
		}
		textObj = trim( res );
		createImage( page_name, page_number, page_numbering );
		// alert(queryText);
		//  nextProcess("waitForMe()");
		JsMwApi().page( page_name ).edit( edit_function_images );
		return false;
		//  checkImagesTemplate();
	} );
	// checkImagesTemplate();
}

function createImage( page_name, page_number, page_numbering ) {
	queryText = '{{Cora Image Data  \n';
	var rights = jQuery( '#lemmataRights' ).val();
	queryText += '| Rights Holder = ' + rights + ' \n';
	queryText += '| Page Number = ' + page_number + ' \n';
	queryText += '| Page Numbering = ' + page_numbering + ' \n';
	var firstP = jQuery( 'input#lemmataFirstPageN' ).val(),
		//  queryText += '| First Page = '+firstP+" \n";
		lastP = jQuery( 'input#lemmataLastPageN' ).val();
	//  queryText += '| Last Page = '+lastP+" \n";
	queryText += '| Category = Offline Lexicon;' + jQuery( '#selectLexicon option:selected' ).attr( 'title' ) + ' \n';
	var short = jQuery( '#selectLexicon option:selected' ).val(),
		volume = jQuery( '#selectVolume option:selected' ).val(),
		type = jQuery( '#metadataType option:selected' ).val();
	queryText += '| Part of ' + type + ' = ' + textObj + '}}\n';
	page = page_name;
	alert( queryText );
	//  JsMwApi().page(page).edit(edit_function_images);
	return false;
}

function waitForMe() {
	//  alert(queryText);
	return false;
}

function hasAuthors() {
	var authors = jQuery( 'input#lemmataAuthor' ).val(),
		res = '{{Authors Data \n';
	if ( authors.indexOf( ';' ) != -1 ) {
		var arrCreators = authors.split( ';' ),
			countItems = 0;
		res += '| Author = ';
		for ( var i in arrCreators ) {
			countItems += 1;
			res += arrCreators[ i ];
			if ( countItems < arrCreators.length ) {
				res += ';';
			}
		}
	} else {
		res += '| Author = ' + authors;
	}
	//  alert(res);
	res += '}}\n';
	return res;
}

function hasVolume() {
	var res = '',
		short = jQuery( 'input#metadataShortTitle' ).val(),
		// var hasvol = jQuery("input#metadataHasVolume").val();
		hasvol = jQuery( '#selectoutput option:selected' ).text();
	// alert('hasvol '+hasvol);
	if ( hasvol.indexOf( ';' ) != -1 ) {
		var arrCreators = hasvol.split( ';' ),
			countItems = 0;
		res += '| Has Volume = ';
		for ( var i in arrCreators ) {
			countItems += 1;
			res += short + '/Volume ' + zeroPad( arrCreators[ i ], 2 );
			if ( countItems < arrCreators.length ) {
				res += ';';
			}
		}
		hasVolumeVar = true;
	} else {
		// alert(jQuery("#selectoutput option:selected").text());
		//  if(hasvol=="1" && jQuery("#selectoutput option:selected").text()=='1') res ='| Has Volume =' + short+'/Volume '+zeroPad(1,2);
		if ( hasvol == '1' ) {
			res = '| Has Volume =';

		} else {
			res += '| Has Volume = ';
			hasVolumeVar = true;
			var ii = parseInt( jQuery( '#selectoutput option:selected' ).text() );
			alert( 'ii ' + ii );
			for ( i = 1; i <= ii; i++ ) {
				res += short + '/Volume ' + zeroPad( i, 2 );
				if ( i < ii ) {
					res += ';';
				}
			}
		}
	}
	alert( res );
	return res;
}
function zeroPad( num, places ) {
	var zero = places - num.toString().length + 1;
	return Array( +( zero > 0 && zero ) ).join( '0' ) + num;
}

function showLemmata() {
	jQuery( '#lexiconList li' ).remove();
	for ( var j = 0; j < selectedProp.length; j++ ) {
		jQuery( '#lexiconList' ).append( '<li id="li-' + j + '"><a href="' + wgServer + wgScript + '/' + selectedProp[ j ].name + '" data-identity="' + selectedProp[ j ].name + '" target="_blank">' + selectedProp[ j ].val + '</a></li>' );
		addCheckBox( jQuery( '#li-' + j ), selectedProp[ j ].val, selectedProp[ j ].name );
	}

	jQuery( '#addimagesbutton' ).show();
	jQuery( '.rightArea' ).show();
	return false;
}

function initImagesData( data ) {
	/* jslint evil: true */
	var obj = eval( data );
	alert( obj[ 0 ].id );
	alert( fixedDecodeURI( obj[ 0 ].name ) );
	var list = obj == null ? [] : ( obj instanceof Array ? obj : [ obj ] );
	for ( var i = 0; i < list.length; i++ ) {
		// alert("De aici "+list[i].id);
		selectedProp.push( { name: fixedDecodeURI( list[ i ].name ), id: list[ i ].id, volumeId: list[ i ].volumeId } );
	}
	return false;
}
function addCheckBox( name, value, val ) {
	//  alert(selectedProp.length);
	var checkbox = document.createElement( 'input' ),
		container = jQuery( name );
	checkbox.type = 'checkbox';
	checkbox.value = val;
	checkbox.id = value;
	container.append( checkbox );
	//   container.appendChild(label);
	return false;
}

function getJsonProperties( type, query ) {
	var k = 0;
	for ( var j = selectedProp.length; j > 0; j-- ) {
		selectedProp.pop();
	}
	jQuery.getJSON( query, function ( data ) {
		jQuery.each( data.items, function ( i, item ) {
			var prop = item.label;
			if ( existsInTab( prop, selectedProp ) < 0 ) {
				//   alert(prop);
				flag = true;
				selectedProp.push( { name: prop, val: item.title } );
			}
			if ( i == 100 ) { return false; }
		} );
		//  if(flag){
		//     alert(selectedProp.length+"aici");
		initLexicons( type );
		flag = false;
		// }

		return false;
	} );
}

function existsInTab( optionl, arrObj ) {
	var res = -1;
	for ( var i = 0; i < arrObj.length; i++ ) {
		if ( arrObj[ i ].name == optionl ) { res = i; }
	}
	return res;
}

function initLexicons( type ) {
	var select_p = jQuery( '#selectLexicon' );
	if ( type == 'volume' ) { select_p = jQuery( '#selectVolume' ); } else if ( type == 'article' ) {

		showLemmata();
		return true;
	}
	//  alert(selectedProp.length+"prop");
	for ( var i = 0; i < selectedProp.length; i++ ) {
		var option_s = jQuery( '<option />' ),
			prop = selectedProp[ i ].name,
			val = selectedProp[ i ].val,
			option_p = jQuery( '<option />' );
		if ( i === 0 ) { option_p.attr( 'selected', true ); }
		option_p.attr( 'title', val );
		option_p.attr( 'value', prop ).html( prop ).appendTo( select_p );
	}
	select_p.trigger( 'change' );
	return true;
}

function newLexicon() {
	jQuery( '#btnIncremental' ).hide();
	jQuery( '#btnNext' ).hide();
	currentLexicon = {};
	renderDetails( currentLexicon ); // Display empty form
}

function renderList( data ) {
	// JAX-RS serializes an empty list as null, and a 'collection of one' as an object (not an 'array of one')
	/* jslint evil: true */
	var obj = eval( data );
	alert( obj[ 0 ].id );
	alert( obj[ 1 ].name );
	var list = obj == null ? [] : ( obj instanceof Array ? obj : [ obj ] );
	jQuery( '.rightArea' ).add( list );
	jQuery( '.rightArea' ).css( 'display', 'block' );
	from = list[ 1 ].volumeId[ 0 ];
	alert( from );
	jQuery( '#lexiconList li' ).remove();
	for ( var i = 0; i < list.length; i++ ) {
		for ( var j = 0; j < list[ i ].volumeId[ j ].length; j++ ) {
			jQuery( '#lexiconList' ).append( '<li><a href="#" data-identity="' + list[ i ].id + '">' + list[ i ].volumeId[ j ] + '</a></li>' );
		}
	}
	jQuery( '.rightArea' ).show();
	return false;
}

function renderDetails( lex ) {
	jQuery( '#lexiconId' ).val( lex.id );
	jQuery( '#name' ).val( lex.name );
	jQuery( '#volumeId' ).val( lex.volumeId );
}

// Helper function to serialize all the form fields into a JSON string
function formToJSON() {
	var lexiconId = jQuery( '#lexiconId' ).val();
	return JSON.stringify( {
		id: lexiconId == '' ? null : lexiconId,
		name: jQuery( '#name option:selected' ).val(),
		volumeId: jQuery( '#volumeID option:selected' ).val()
	} );
}

function fixedEncodeURIComponent( str ) {
	return encodeURIComponent( str ).replace( /!/g, '%21' ).replace( /'/g, '%27' ).replace( /\(/g, '%28' ).replace( /\)/g, '%29' ).replace( /\*/g, '%2A' );
}

function fixedDecodeURI( str ) {
	return decodeURI( str ).replace( /\+/g, ' ' );
}

function trim( stringToTrim ) {
	return stringToTrim.replace( /^\s+|\s+$/g, '' );
}
function ltrim( stringToTrim ) {
	return stringToTrim.replace( /^\s+/, '' );
}
function rtrim( stringToTrim ) {
	return stringToTrim.replace( /\s+$/, '' );
}

/* A status bar at the bottom of the window that let's the user know what's going on.
 * Thanks to http://www.west-wind.com/WebLog/posts/388213.aspx */
function StatusBar( sel, options ) {
	var _I = this,
		_sb = null;

	// options
	this.elementId = '_showstatus';
	this.prependMultiline = true;
	this.showCloseButton = false;
	this.afterTimeoutText = null;

	this.cssClass = 'statusbar';
	this.highlightClass = 'statusbarhighlight';
	this.errorClass = 'statuserror';
	this.closeButtonClass = 'statusbarclose';
	this.additive = false;

	jQuery.extend( this, options );

	if ( sel ) { _sb = jQuery( sel ); }

	// create statusbar object manually
	if ( !_sb ) {
		_sb = jQuery( "<div id='_statusbar' class='" + _I.cssClass + "'>" +
                "<div class='" + _I.closeButtonClass + "'>" +
                ( _I.showCloseButton ? ' X </div></div>' : '' ) )
			.appendTo( document.body )
			.show();
	}
	if ( _I.showCloseButton ) { jQuery( '.' + _I.cssClass ).on( 'click', function ( e ) { jQuery( _sb ).hide(); } ); }

	this.show = function ( message, timeout, isError ) {
		if ( _I.additive ) {
			var html = "<div style='margin-bottom: 2px;' >" + message + '</div>';
			if ( _I.prependMultiline ) { _sb.prepend( html ); } else { _sb.append( html ); }
		} else {

			if ( !_I.showCloseButton ) { _sb.text( message ); } else {
				var t = _sb.find( 'div.statusbarclose' );
				_sb.text( message ).prepend( t );
			}
		}

		_sb.show();

		if ( timeout ) {
			if ( isError ) { _sb.addClass( _I.errorClass ); } else { _sb.addClass( _I.highlightClass ); }

			setTimeout(
				function () {
					_sb.removeClass( _I.highlightClass );
					if ( _I.afterTimeoutText ) { _I.show( _I.afterTimeoutText ); }
				},
				timeout );
		}
	};
	this.release = function () {
		if ( _statusbar ) { jQuery( _statusbar ).remove(); }
	};
}
// use this as a global instance to customize constructor
// or do nothing and get a default status bar
var _statusbar = null;
function showStatus( message, timeout, additive, isError ) {
	if ( !_statusbar ) { _statusbar = new StatusBar(); }
	_statusbar.show( message, timeout, additive, isError );
}

function romanize( num ) {
	if ( !+num ) { return false; }
	var digits = String( +num ).split( '' ),
		key = [ '', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM',
			'', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC',
			'', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX' ],
		roman = '',
		i = 3;
	while ( i-- ) { roman = ( key[ +digits.pop() + ( i * 10 ) ] || '' ) + roman; }
	return Array( +digits.join( '' ) + 1 ).join( 'M' ) + roman;
}

function deromanize( str ) {
	var str = str.toUpperCase(),
		validator = /^M*(?:D?C{0,3}|C[MD])(?:L?X{0,3}|X[CL])(?:V?I{0,3}|I[XV])$/,
		token = /[MDLV]|C[MD]?|X[CL]?|I[XV]?/g,
		key = { M: 1000, CM: 900, D: 500, CD: 400, C: 100, XC: 90, L: 50, XL: 40, X: 10, IX: 9, V: 5, IV: 4, I: 1 },
		num = 0, m;
	if ( !( str && validator.test( str ) ) ) { return false; }
	var m = token.exec( str );
	while ( m ) { num += key[ m[ 0 ] ]; }
	return num;
}

function nextRoman( str ) {
	var numeric = deromanize( str );
	return romanize( numeric + 1 );
}
