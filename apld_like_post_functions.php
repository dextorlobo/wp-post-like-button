<?php
/**
* Get the like output on site
* @param array
* @return string
*/
function GetApldLikePost($arg = null) {
	global $wpdb;
	$post_id = get_the_ID();
	$apld_like_post = "";
	$title_text_like = __('Like', 'apld-like-post');
	$title_text_unlike = __('Unlike', 'apld-like-post');
	$like_count = GetApldLikeCount($post_id);
	$unlike_count = GetApldUnlikeCount($post_id);
	$msg = GetApldVotedMessage($post_id);
	$alignment = "align-left";
	$style = 'style1';
	$apld_like_post .= "<div class='apld-action'>";
	$apld_like_post .= "<div class='apld-position " . $alignment . "'>";

	$apld_like_post .= "<div class='action-like " . $alignment . "'>";
	$apld_like_post .= "<a class='lbg-" . $style . " like-" . $post_id . " jlk' href='javascript:void(0)' data-task='like' data-post_id='" . $post_id . "' data-nonce='" . $nonce . "' rel='nofollow'>";
	$apld_like_post .= "<span class='dashicons dashicons-thumbs-up'>";
	$apld_like_post .= "<span class='lc-" . $post_id . " lc'>" . $like_count . "</span>";
	$apld_like_post .= "</a></div>";


	$apld_like_post .= "<div class='action-unlike " . $alignment . "'>";
	$apld_like_post .= "<a class='unlbg-" . $style . " unlike-" . $post_id . " jlk' href='javascript:void(0)' data-task='unlike' data-post_id='" . $post_id . "' data-nonce='" . $nonce . "' rel='nofollow'>";
	$apld_like_post .= "<span class='dashicons dashicons-thumbs-down'>";
	$apld_like_post .= "<span class='unlc-" . $post_id . " unlc'>" . $unlike_count . "</span>";
	$apld_like_post .= "</a></div> ";


	$apld_like_post .= "</div> ";
	$apld_like_post .= "<div class='status-" . $post_id . " status " . $alignment . "'>" . $msg . "</div>";
	$apld_like_post .= "</div><div class='apld-clear'></div>";

	if ($arg == 'put') {
		return $apld_like_post;
	} else {
		echo $apld_like_post;
	}
}

/**
* Show the like content after post content
* @param $content string
* @param $param string
* @return string
*/
function PutApldLikePost($content) {
	$show_on_posts = false;

	if( is_single() ) {
		$show_on_posts = true;
	}

	if ($show_on_posts) {
		$apld_like_post_content = GetApldLikePost('put');
		$content = $content . $apld_like_post_content;
	}
	return $content;
}

//add_filter('the_content', 'PutApldLikePost');

/**
* Show the like content after excerpt
* @param $content string
* @param $param string
* @return string
*/
function PutApldLikePostExcerpt($content) {
	$apld_like_post_content = GetApldLikePost('put');
	$content = $content . $apld_like_post_content;
	return $content;
}

//add_filter('the_excerpt', 'PutApldLikePostExcerpt');


/**
* Show the like by shortcode
* @param $content string
* @param $param string
* @return string
*/
function ApldButtonsShortcode($content) {
	$apld_like_post_content = GetApldLikePost('put');
	$content = $apld_like_post_content;
	return $content;
}

add_shortcode('apld_like_post_buttons', 'ApldButtonsShortcode');

/**
 * Check whether user has already voted or not
 * @param $post_id integer
 * @param $ip string
 * @return integer
 */
function HasApldAlreadyVoted($post_id, $ip = null) {
	global $wpdb, $apld_ip_address;
	if (null == $ip) {
		$ip = $apld_ip_address;
	}

	$apld_has_voted = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(id) AS has_voted FROM {$wpdb->prefix}apld_like_post
			WHERE post_id = %d AND ip = %s",
			$post_id, $ip
		)
	);
	return $apld_has_voted;
}

/**
 * Get like count for a post
 * @param $post_id integer
 * @return string
 */
function GetApldLikeCount($post_id) {
	global $wpdb; 
	$apld_like_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(value) FROM {$wpdb->prefix}apld_like_post
			WHERE post_id = %d AND value >= 0",
			$post_id
		)
	);
	 
	if (!$apld_like_count) {
		$apld_like_count = 0;
	} else {
		$apld_like_count = $apld_like_count;
	}
	return $apld_like_count;
}

/**
 * Get unlike count for a post
 * @param $post_id integer
 * @return string
 */
function GetApldUnlikeCount($post_id) {
	global $wpdb;

	$apld_unlike_count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(value) FROM {$wpdb->prefix}apld_like_post
			WHERE post_id = %d AND value <= 0",
			$post_id
		)
	);

	if (!$apld_unlike_count) {
		$apld_unlike_count = 0;
	} else {
		$apld_unlike_count = $apld_unlike_count;
	}
	return $apld_unlike_count;
}

/**
 * Get already voted message
 * @param $post_id integer
 * @param $ip string
 * @return string
 */
function GetApldVotedMessage($post_id, $ip = null) {
	global $wpdb, $apld_ip_address;
	$apld_voted_message = '';

	if (null == $ip) {
		$ip = $apld_ip_address;
	}

	$query = $wpdb->prepare(
		"SELECT COUNT(id) AS has_voted FROM {$wpdb->prefix}apld_like_post
		WHERE post_id = %d AND ip = %s",
		$post_id, $ip
	);

	$apld_has_voted = $wpdb->get_var($query);

	if ($apld_has_voted > 0) {
		$apld_voted_message = 'You have already voted.';
	}
	return $apld_voted_message;
}