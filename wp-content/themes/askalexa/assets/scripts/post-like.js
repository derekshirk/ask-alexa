jQuery(document).ready(function() {
 
	jQuery(".post--rating .thumbs-up").click(function(){
		console.log("thumbs-up clicked") ;
		thumbsUp = jQuery(this);
	 
		// Retrieve post ID from data attribute
		post_id = thumbsUp.data("post_id");
		 
		// Ajax call
		jQuery.ajax({
			type: "post",
			url: ajax_var.url,
			data: "action=post-like&nonce="+ajax_var.nonce+"&post_like=&post_id="+post_id,
			success: function(count){
				// If vote successful
				if(count != "already")
				{
					thumbsUp.addClass("voted");
					$(".up-count").text(count);
				}
			}
		});
		 
		return false;
	})

	jQuery(".post--rating .thumbs-down").click(function(){
		console.log("thumbs-down clicked") ;
		thumbsDown = jQuery(this);
	 
		// Retrieve post ID from data attribute
		post_id = thumbsDown.data("post_id");
		 
		// Ajax call
		jQuery.ajax({
			type: "post",
			url: ajax_var.url,
			data: "action=post-dislike&nonce="+ajax_var.nonce+"&post_dislike=&post_id="+post_id,
			success: function(count){
				// If vote successful
				if(count != "already")
				{
					thumbsDown.addClass("voted");
					thumbsDown.siblings(".down-count").text(count);
				}
			}
		});
		 
		return false;
	})
})