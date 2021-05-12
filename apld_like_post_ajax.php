<?php
// Associate the respective functions with the ajax call
add_action('wp_ajax_apld_like_post_process_vote', 'ApldLikePostProcessVote');
add_action('wp_ajax_nopriv_apld_like_post_process_vote', 'ApldLikePostProcessVote');

function ApldLikePostProcessVote(){

	global $wpdb, $apld_ip_address;
	
	// Get request data
	$post_id = (int)$_REQUEST['post_id'];
	$task = $_REQUEST['task'];
	$can_vote = false;

	$has_already_voted = HasApldAlreadyVoted( $post_id, $apld_ip_address );
	$datetime_now = date( 'Y-m-d H:i:s' );
	
	if ( $has_already_voted ) {
		// User can vote only once and has already voted.
		$error = 1;
		$msg = "You have alread voted.";
	} else {
		$can_vote = true;
	}
	
	if ( $can_vote ) {
		$current_user = wp_get_current_user();
		$user_id = (int)$current_user->ID;
		 
		 if ( $task == "like" ) {
			if ( $has_already_voted ) {
				$success = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}apld_like_post SET 
						value = value + 1,
						date_time = '" . date( 'Y-m-d H:i:s' ) . "',
						user_id = %d WHERE post_id = %d AND ip = %s",
						$user_id, $post_id, $apld_ip_address
					)
				);
			} else {
				$success = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}apld_like_post SET 
						post_id = %d, value = '1',
						date_time = '" . date( 'Y-m-d H:i:s' ) . "',
						user_id = %d, ip = %s",
						$post_id, $user_id, $apld_ip_address
					)
				);
			}
		} else {
			if ( $has_already_voted ) {
				$success = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}apld_like_post SET 
						value = value - 1,
						date_time = '" . date( 'Y-m-d H:i:s' ) . "',
						user_id = %d WHERE post_id = %d AND ip = %s",
						$user_id, $post_id, $apld_ip_address
					)
				);
			} else {
				$success = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}apld_like_post SET 
						post_id = %d, value = '-1',
						date_time = '" . date( 'Y-m-d H:i:s' ) . "',
						user_id = %d, ip = %s",
						$post_id, $user_id, $apld_ip_address
					)
				);
			}
		}

		if ($success) {
			$error = 0;
			$msg = "Vote successfully.";
		} else {
			$error = 1;
			$msg = __( 'Could not process your vote.', 'apld-like-post' );
		}
	}
	
	$apld_like_count = GetApldLikeCount( $post_id );
	$apld_unlike_count = GetApldUnlikeCount( $post_id );
	
	// Check for method of processing the data
	if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
		$result = array(
			"msg" => $msg,
			"error" => $error,
			"like" => $apld_like_count,
			"unlike" => $apld_unlike_count
		);
		echo json_encode($result);
	} else {
		header( "location:" . $_SERVER["HTTP_REFERER"] );
	}
	exit;
}