/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$.fn.select2.defaults.defaults['language'].inputTooShort = function( args ){
	return mw.message(
		'bs-social-select2-overwrites-inputtooshort',
		args.minimum
	).parse();
};
$.fn.select2.defaults.defaults['language'].noResults = function(){
	return mw.message(
		'bs-social-select2-overwrites-noresults'
	).parse();
};
/*$.fn.select2.defaults.defaults['language'].inputTooLong = function( args ){
	return mw.message(
		'bs-social-select2-overwrites-inputtoolong',
		args.maximum
	).parse();
};*/
$.fn.select2.defaults.defaults['language'].errorLoading = function(){
	return mw.message(
		'bs-social-select2-overwrites-errorloading'
	).parse();
};
$.fn.select2.defaults.defaults['language'].loadingMore = function(){
	return mw.message(
		'bs-social-select2-overwrites-loadingmore'
	).parse();
};
$.fn.select2.defaults.defaults['language'].searching = function(){
	return mw.message(
		'bs-social-select2-overwrites-searching'
	).parse();
};
/*$.fn.select2.defaults.defaults['language'].maximumSelected = function(){
	return mw.message(
		'bs-social-select2-overwrites-maximumselected'
	).parse();
};*/