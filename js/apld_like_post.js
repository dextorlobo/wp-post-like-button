jQuery(document).ready(function(){
	jQuery(document).on("click", ".jlk", function(e){
		e.preventDefault();
		var task = jQuery(this).attr("data-task");
		var post_id = jQuery(this).attr("data-post_id");
		jQuery(".status-" + post_id).html("&nbsp;&nbsp;").addClass("loading-img").show();
		jQuery.ajax({
			type : "post",
			async : false,
			dataType : "json",
			url : apldlp.ajax_url,
			data : {action: "apld_like_post_process_vote", task : task, post_id : post_id},
			success: function(response) {
				jQuery(".lc-" + post_id).html(response.like);
				jQuery(".unlc-" + post_id).html(response.unlike);
				jQuery(".status-" + post_id).removeClass("loading-img").empty().html(response.msg);
			}
		});
	});
});