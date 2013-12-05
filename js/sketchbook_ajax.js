
(function($) {

	$.fn.displayPost = function() {

		event.preventDefault();

		var post_id = $(this).data("id");
		var id = "#" + post_id;

		// Check if the reveal modal for the specific post id doesn't already exist by checking for it's length
		if($(id).length == 0 ) {
			// We'll add an ID to the new reveal modal; we'll use that same ID to check if it exists in the future.
			var modal = $('<div>').attr('id', post_id ).addClass('reveal-modal').appendTo('body');
		  	var ajaxURL = MyAjax.ajaxurl;
			 $.ajax({
	            type: 'POST',
	            url: ajaxURL,
	            data: {"action": "load-content", post_id: post_id },
	            success: function(response) {
		            modal.empty().html(response).append('<a class="close-reveal-modal">&#215;</a>').foundation('reveal', 'open');
		            modal.bind('opened', function() {
		            	// Reset visibility to hidden and set display: none on closed reveal-modal divs, for some reason not working by default when reveal close is triggered on .secondary links	
		            	$(".reveal-modal:not('.reveal-modal.open')").css({'visibility': 'hidden', 'display' : 'none'})
		            	// Trigger resize 
	            		$(window).trigger('resize');
			        return false;
			  		});
		 		}
			});
		}
		 //If the div with the ID already exists just open it.
	     else {
		     $(id).foundation('reveal', 'open');
	     }

	     // Recalculate left margin on window resize to allow for absolute centering of variable width elements
	     $(window).resize(function(){
	    	 var left;
			    left = Math.max($(window).width() - $(id).outerWidth(), 0) / 2;
			    $(id).css({
			        left:left + $(window).scrollLeft()
			    });
		 });
	}

})(jQuery);

// Apply the function when we click on the .reveal link
jQuery(document).on("click", ".reveal,.secondary", function() {
	jQuery(this).displayPost();

});

// Open new modals on secondary paging links in open modal window
jQuery(document).on("click", ".secondary", function() {
	var	id = jQuery(this).closest("div").attr("id");
	 	jQuery(id).foundation('reveal', 'close');
});


