//CREDITS: http://codyhouse.co/demo/back-to-top/index.html
(function(mw, $, bs, d, undefined ){
	$(function(){
		//Inject link to <body>
		var anchor = mw.html.element(
			'a',
			{
				class: 'bs-top bs-icon-arrow-o-up',
				id: 'bs-top',
				title: mw.message('bs-to-top-desc').plain(),
				'aria-label': mw.message('bs-to-top-desc').plain()
			}
		);
		$(anchor).appendTo($('body'));

		//Wire up show/hide functions for main link
		$(window).scroll(function(){
			if( $(this).scrollTop() > bs.skin.scrollToTop.offset ) {
				$('#bs-top').addClass('bs-top-is-visible');
			}
			else {
				$('#bs-top').removeClass('bs-top-is-visible');
			}
		});

		//Every .bs-top will scroll up
		$(d).on('click', '.bs-top', function(e){
			e.preventDefault();
			$('body,html').animate(
				{
					scrollTop: 0
				},
				bs.skin.scrollToTop.duration
			);
		});
	});

	bs.skin = bs.skin || {};
	bs.skin.scrollToTop = bs.skin.scrollToTop || {
		duration: 400,
		offset: 300
	};

})( mediaWiki, jQuery, blueSpice, document);
