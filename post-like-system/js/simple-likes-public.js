(function( $ ) {
	'use strict';

	// On page load, fetch a fresh nonce + current like state for every button on the
	// page. This makes the like system work correctly when a caching plugin serves
	// stale HTML (the admin-ajax.php endpoint is never cached).
	$(document).ready(function() {
		var buttons = $('.sl-button');
		if ( ! buttons.length ) { return; }

		var items = [];
		buttons.each(function() {
			items.push({
				post_id:    $(this).data('post-id'),
				is_comment: $(this).data('iscomment')
			});
		});

		$.ajax({
			type: 'POST',
			url:  simpleLikes.ajaxurl,
			data: { action: 'get_simple_like_statuses', items: items },
			success: function( response ) {
				$.each( response, function( i, data ) {
					var selector = data.is_comment == 1
						? '.sl-comment-button-' + data.post_id
						: '.sl-button-'         + data.post_id;
					var btn  = $( selector );
					var wrap = btn.closest('.meta__item--likes');
					// Refresh nonce so the click handler sends a valid nonce.
					btn.attr( 'data-nonce', data.nonce );
					// Also update the no-JS fallback href nonce param.
					var href = btn.attr('href') || '';
					btn.attr( 'href', href.replace( /nonce=[^&]*/, 'nonce=' + data.nonce ) );
					btn.html( data.icon + data.count );
					btn.prop( 'title', data.status === 'liked' ? simpleLikes.unlike : simpleLikes.like );
					if ( data.status === 'liked' ) {
						btn.addClass('liked');
						wrap.addClass('meta__item--likes--active');
					} else {
						btn.removeClass('liked');
						wrap.removeClass('meta__item--likes--active');
					}
				});
			}
		});
	});

	$(document).on('click', '.sl-button', function() {
		var button = $(this);
		var post_id = button.attr('data-post-id');
		var security = button.attr('data-nonce');
		var iscomment = button.attr('data-iscomment');
		var allbuttons;
		if ( iscomment === '1' ) { /* Comments can have same id */
			allbuttons = $('.sl-comment-button-'+post_id);
		} else {
			allbuttons = $('.sl-button-'+post_id);
		}
		var loader = allbuttons.next('.sl-loader');
		if (post_id !== '') {
			$.ajax({
				type: 'POST',
				url: simpleLikes.ajaxurl,
				data : {
					action : 'process_simple_like',
					post_id : post_id,
					nonce : security,
					is_comment : iscomment,
				},
				beforeSend:function(){
					loader.html('&nbsp;<div class="like-loader"></div>');
				},
				success: function(response){
					var icon = response.icon;
					var count = response.count;
					allbuttons.html(icon+count);
					if(response.status === 'unliked') {
						var like_text = simpleLikes.like;
						allbuttons.prop('title', like_text);
						allbuttons.removeClass('liked');
					} else {
						var unlike_text = simpleLikes.unlike;
						allbuttons.prop('title', unlike_text);
						allbuttons.addClass('liked');
					}
					loader.empty();
				}
			});

		}
		return false;
	});
})( jQuery );
