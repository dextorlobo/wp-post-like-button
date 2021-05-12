<?php
class MostLikedPostsWidget extends WP_Widget
{
	function __construct() {
		load_plugin_textdomain( 'apld-like-post', false, 'apld-like-post/lang' );
		$widget_ops = array('description' => __('Widget to display most liked posts for a given time range.', 'apld-like-post'));
		parent::__construct(false, $name = __('Most Liked Posts', 'apld-like-post'), $widget_ops);
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		global $MostLikedPosts;
		$MostLikedPosts->widget($args, $instance); 
	}
	
	function update($new_instance, $old_instance) {         
		if ( $new_instance['title'] == '' ) {
			$new_instance['title'] = __('Most Liked Posts', 'apld-like-post');
		}
		
		if ( empty( $new_instance['number']) ) {
			$new_instance['number'] = 10;
		}
		
		if ( !isset( $new_instance['show_count'] ) ) {
			$new_instance['show_count'] = 0;
		}
		
		return $new_instance;
	}
	
	function form($instance) {
		global $MostLikedPosts;
		
		/**
		* Define the array of defaults
		*/ 
		$defaults = array(
						'title' => __('Most Liked Posts', 'apld-like-post'),
						'number' => 10,
						'show_count' => ''
					);
		
		$instance = wp_parse_args( $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		
		$show_types = array('most_liked' => __('Most Liked', 'apld-like-post'), 'recent_liked' => __('Recently Liked', 'apld-like-post'));
		
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'apld-like-post'); ?>:<br />
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title;?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show', 'apld-like-post'); ?>:<br />
			<input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 40px;" value="<?php echo $instance['number'];?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show_count'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>" value="1" <?php if(isset($instance['show_count']) && $instance['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php _e('Show like count', 'apld-like-post'); ?></label>
		</p>
		<input type="hidden" id="apld-most-submit" name="apld-submit" value="1" />      
		<?php
	}
}

class ApldMostLikedPosts
{
	function __construct() {
		add_action( 'widgets_init', array(&$this, 'init') );
	}
	
	function init() {
		register_widget("MostLikedPostsWidget");
	}
	
	function widget($args, $instance = array() ) {
		global $wpdb;
		extract($args);
		
		$where = '';
		$title = $instance['title'];
		$show_count = $instance['show_count'];
		$order_by = 'ORDER BY like_count DESC, post_title';
		
		if( (int)$instance['number'] > 0 ) {
			$limit = "LIMIT " . (int)$instance['number'];
		}
		
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="apld-most-liked-posts">';
		
		// Getting the most liked posts
		$query = "SELECT post_id, SUM(value) AS like_count, post_title
					FROM `{$wpdb->prefix}apld_like_post` L, {$wpdb->prefix}posts P 
					WHERE L.post_id = P.ID AND post_status = 'publish' 
					$where GROUP BY post_id $order_by $limit";
		
		$posts = $wpdb->get_results($query);

		if ( count( $posts ) > 0 ) {
			foreach ( $posts as $post ) {
				$post_title = stripslashes($post->post_title);
				$permalink = get_permalink($post->post_id);
				$like_count = GetApldLikeCount($post->post_id);
				$unlike_count = GetApldUnlikeCount($post->post_id);
				
				$widget_data .= '<li><a href="' . $permalink . '" title="' . $post_title . '">' . $post_title . '</a>';
				$widget_data .= $show_count == '1' ? ' (Likes ' . $like_count . ') (Unlikes ' . $unlike_count . ')' : '';
				$widget_data .= '</li>';
			}
		} else {
			$widget_data .= '<li>';
			$widget_data .= __('No posts liked yet.', 'apld-like-post');
			$widget_data .= '</li>';
		}
		$widget_data .= '</ul>';
		$widget_data .= $after_widget;
		echo $widget_data;
	}
}

$MostLikedPosts = new ApldMostLikedPosts();