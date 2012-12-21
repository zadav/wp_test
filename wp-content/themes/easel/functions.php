<?php

function easel_themeinfo($whichinfo = null) {
	global $easel_themeinfo;
	if (empty($easel_themeinfo) || $whichinfo == 'reset') {
		$easel_themeinfo = array();
		$easel_options = easel_load_options();
		$easel_coreinfo = wp_upload_dir();
		$easel_addinfo = array(
			'upload_path' => get_option('upload_path'),
			'version' => '3.2',
			'themepath' => get_template_directory(),
			'themeurl' => get_template_directory_uri(), 
			'stylepath' => get_stylesheet_directory(), 
			'styleurl' => get_stylesheet_directory_uri(),
			'uploadpath' => $easel_coreinfo['basedir'],
			'uploadurl' => $easel_coreinfo['baseurl'],
			'home' => untrailingslashit(home_url()),  
			'siteurl' => untrailingslashit(site_url()),
			'excerpt_length' => '40'
		);
		$easel_themeinfo = array_merge($easel_coreinfo, $easel_addinfo);
		$easel_themeinfo = array_merge($easel_themeinfo, $easel_options);
		if (!isset($easel_themeinfo['layout']) || empty($easel_themeinfo['layout']) || ($easel_themeinfo['layout'] == 'standard')) $easel_themeinfo['layout'] = '3c';
	}
	if ($whichinfo && $whichinfo !== 'reset')
		if (isset($easel_themeinfo[$whichinfo])) 
			return $easel_themeinfo[$whichinfo];
		else
			return false;
	return $easel_themeinfo;
}

// load up the addons that it finds, loads before functions just in case we want to rewrite a function
if (is_dir(easel_themeinfo('themepath') . '/addons')) {
	if (easel_themeinfo('enable_addon_page_options')) 
		@require_once(easel_themeinfo('themepath') . '/addons/page-options.php');
	if (easel_themeinfo('enable_addon_membersonly'))
		@require_once(easel_themeinfo('themepath') . '/addons/membersonly.php');
	if (easel_themeinfo('enable_addon_playingnow'))
		@require_once(easel_themeinfo('themepath') . '/addons/playingnow.php');
	if (easel_themeinfo('enable_addon_showcase'))
		@require_once(easel_themeinfo('themepath') . '/addons/showcase.php');
	if (easel_themeinfo('enable_addon_commpress'))
		@require_once(easel_themeinfo('themepath') . '/addons/commpress.php');
/*	if (easel_themeinfo('enable_wprewrite_posttype_control'))
		@require_once(easel_themeinfo('themepath') . '/addons/wp-rewrite.php'); */
}

// These autoload
foreach (glob(easel_themeinfo('themepath') . "/functions/*.php") as $funcfile) {
	@require_once($funcfile);
}

// Load all the widgets.
foreach (glob(easel_themeinfo('themepath')  . '/widgets/*.php') as $widgefile) {
	@require_once($widgefile);
}

// Dashboard Menu Easel Options
if (is_admin()) {
	@require_once(easel_themeinfo('themepath') . '/options.php');
}

add_action( 'comment_form_before', 'easel_enqueue_comment_reply' );

function easel_enqueue_comment_reply() {
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) && !easel_themeinfo('disable_comment_javascript')) wp_enqueue_script( 'comment-reply' );
}

// Load the text domain for translation
load_theme_textdomain( 'easel', get_template_directory() . '/lang' );

// the_post_thumbnail('thumbnail/medium/full');
add_theme_support( 'post-thumbnails' );

// Required by the wordpress review theme, it sucks donkey balls but is required.
add_theme_support( 'automatic-feed-links' );

register_nav_menus(array(
	'Primary' => __('Primary', 'easel'),
	'Footer' => __('Footer', 'easel')
	));

add_theme_support( 'custom-background');


/* this sets default video width */
if (!isset($content_width)) {
	$content_width = 500;
}

add_action('init', 'easel_init');

function easel_init() {
	global $is_IE;
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		if (!easel_themeinfo('disable_jquery_menu_code')) {
			wp_enqueue_script('ddsmoothmenu_js', easel_themeinfo('themeurl') . '/js/ddsmoothmenu.js'); 
			wp_enqueue_script('menubar_js', easel_themeinfo('themeurl') . '/js/menubar.js');
		}
		if (!easel_themeinfo('disable_scroll_to_top')) {
			wp_enqueue_script('easel_scroll', easel_themeinfo('themeurl') . '/js/scroll.js', null, null, true);
		}
		if (is_active_widget('easel_google_translate_widget', false, 'easel_google_translate_widget', true)) {
			wp_enqueue_script('google-translate', 'http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit', null, null, true);
			wp_enqueue_script('google-translate-settings', get_template_directory_uri() . '/js/googletranslate.js');
		}
		if (easel_themeinfo('enable_avatar_trick') && !$is_IE) {
			wp_enqueue_script('themetricks_historic1', easel_themeinfo('themeurl') . '/js/cvi_text_lib.js', null, null, true);
			wp_enqueue_script('themetricks_historic2', easel_themeinfo('themeurl') . '/js/instant.js', null, null, true);
		}
		if (easel_themeinfo('facebook_like_blog_post'))
			wp_enqueue_script('easel-facebook', 'http://connect.facebook.net/en_US/all.js#xfbml=1'); // force to the header instead of footer
			
		add_filter('pre_get_posts', 'easel_query_change_posts_per_page');
		// Set the post count on the home page
		function easel_query_change_posts_per_page($query) {
			if (is_home()) {
				$query->set('posts_per_page', easel_themeinfo('home_post_count'));
			}
			return $query;
		}
		
		add_filter('pre_get_posts', 'easel_query_change_archive_display_order');
		// Set the 'order' of the archive and search		
		function easel_query_change_archive_display_order($query) {
			if ((is_archive() || is_search()) && !isset($query->query_vars['feed'])) {
				$archive_display_order = easel_themeinfo('archive_display_order');
				if (empty($archive_display_order)) $archive_display_order = 'DESC';
				$order = '&order='.$archive_display_order;
				$query->set('order', $archive_display_order);
				return $query;
			}
		}
	}	
}

add_action('widgets_init', 'easel_register_sidebars');
if (!function_exists('easel_register_sidebars')) {
	function easel_register_sidebars() {
		$widgets_list = array(
			array('id' => 'left-sidebar', 'name' => __('Left Sidebar', 'easel'), 'description' => __('The sidebar that appears to the left of the content.','easel')),
			array('id' => 'right-sidebar', 'name' => __('Right Sidebar', 'easel'), 'description' => __('The sidebar that appears to the right of the content.','easel')),
			array('id' => 'above-header', 'name' => __('Above Header', 'easel'), 'description' => __('This sidebar appears to above all of the site information.  This sidebar is not encased in CSS, you will need to create CSS for it.','easel')),
			array('id' => 'header', 'name' => __('Header', 'easel'), 'description' => __('This sidebar appears inside the #header block.','easel')),
			array('id' => 'menubar', 'name' => __('Menubar', 'easel'), 'description' => __('This sidebar is under the header and above the content-wrapper block','easel')),
			array('id' => 'over-blog', 'name' => __('Over Blog', 'easel'), 'description' => __('This sidebar appears over the blog within the #column .narrowcolumn','easel')),
			array('id' => 'under-blog', 'name' => __('Under Blog', 'easel'), 'description' => __('This sidebar appears under the blog within the #column .narrowocolumn','easel')),
			array('id' => 'footer', 'name' => __('Footer', 'easel'), 'description' => __('This sidebar is below the #content-wrapper block at the bottom of the page','easel')),
		);
		foreach ($widgets_list as $widget_info) {
			register_sidebar(array(
						'name'=> $widget_info['name'],
						'id' => 'sidebar-'.sanitize_title($widget_info['id']),
						'description' => $widget_info['description'],
						'before_widget' => "<div id=\"".'%1$s'."\" class=\"widget ".'%2$s'."\">\r\n<div class=\"widget-head\"></div>\r\n<div class=\"widget-content\">\r\n",
						'after_widget'  => "</div>\r\n<div class=\"clear\"></div>\r\n<div class=\"widget-foot\"></div>\r\n</div>\r\n",
						'before_title'  => "<h2 class=\"widgettitle\">",
						'after_title'   => "</h2>\r\n"
						));
		}
	}
}

function easel_get_sidebar($location = '') {
	remove_filter('pre_get_posts', 'easel_query_change_posts_per_page');
	if (empty($location)) { get_sidebar(); return; }
	if (file_exists(get_template_directory().'/sidebar-'.$location.'.php') || file_exists(get_stylesheet_directory().'/sidebar-'.$location.'.php')) {
		get_sidebar($location);
	} elseif (is_active_sidebar('sidebar-'.$location)) { ?>
		<div id="sidebar-<?php echo $location; ?>" class="sidebar">
			<?php dynamic_sidebar('sidebar-'.$location); ?>
			<div class="clear"></div>
		</div>
	<?php }
	add_filter('pre_get_posts', 'easel_query_change_posts_per_page');
}

function easel_is_signup() {
	global $wp_query;
	if (strpos( $_SERVER['SCRIPT_NAME'], 'wp-signup.php' ) || strpos( $_SERVER['SCRIPT_NAME'], 'wp-activate.php' )) return true;
	return false;
}

function easel_load_options() {

	$easel_options = get_option('easel-options');
	if (empty($easel_options)) {
		
		foreach (array(
			'disable_jquery_menu_code' => false,
			'disable_scroll_to_top' => false,
			'enable_avatar_trick' => true,
			'disable_default_design' => false,
			'disable_comment_note' => false,
			'enable_numbered_pagination' => true,
			'disable_comment_javascript' => false,
			'enable_post_thumbnail_rss' => true,
			'disable_page_titles' => false,
			'disable_post_titles' => false,			
			'enable_post_calendar' => true,
			'enable_post_author_gravatar' => false,
			'disable_categories_in_posts' => false,
			'disable_tags_in_posts' => false,
			'disable_author_info_in_posts' => false,
			'disable_date_info_in_posts' => false,
			'home_post_count' => '5',
			'disable_footer_text' => false,
			'disable_default_menubar' => false,
			'enable_search_in_menubar' => false,
			'enable_rss_in_menubar' => true,
			'avatar_directory' => 'none',
			'enable_debug_footer_code' => false,
			'disable_blog_on_homepage' => false,
			'enable_comments_on_homepage' => false,
			'enable_addon_membersonly' => false,
			'non_members_message' => __('There is members only content here.','easel'),
			'enable_addon_showcase' => false,
			'enable_addon_playingnow' => false,
			'enable_addon_showcase_slider' => false,
			'enable_addon_commpress' => false,
			'enable_addon_page_options' => false,
			'custom_image_header_width' => '980',
			'custom_image_header_height' => '100',
			'copyright_name' => '',
			'copyright_url' => '',
			'facebook_like_blog_post' => false,
			'facebook_meta' => false,
			'display_archive_as_links' => false,
			'archive_display_order' => 'DESC',
			'layout' => '3c',
			'enable_wprewrite_posttype_control' => false,
			'force_active_connection_close' => false,
			'enable_addon_easel_slider' => true,
			'menubar_social_icons' => false,
			'menubar_social_twitter' => '',
			'menubar_social_facebook' => '',
			'enable_breadcrumbs' => false,
			'excerpt_or_content_in_archive' => 'excerpt',
			'enable_last_modified_in_posts' => false,
			'disable_posted_at_time_in_posts' => false,
			'menubar_social_googleplus' => '',
			'menubar_social_linkedin' => '',
			'menubar_social_pinterest' => '',
			'menubar_social_youtube' => '',
			'menubar_social_flickr' => '',
			'menubar_social_tumblr' => '',
			'menubar_social_deviantart' => '',
			'menubar_social_myspace' => '',
			'menubar_social_email' => ''
		) as $field => $value) {
			$easel_options[$field] = $value;
		}
		update_option('easel-options', $easel_options);
	}
	return $easel_options;
}

add_action('easel-post-info','easel_add_post_ratings');

if (!function_exists('easel_add_post_ratings')) {
	function easel_add_post_ratings() {
		global $post;
		if (function_exists('the_ratings') && $post->post_type == 'post') { the_ratings(); } 
	}
}

function easel_debug_page_foot_code() { ?>
	<p><?php echo get_num_queries() ?> queries. <?php if (function_exists('memory_get_usage')) { $unit=array('b','kb','mb','gb','tb','pb'); echo @round(memory_get_usage(true)/pow(1024,($i=floor(log(memory_get_usage(true),1024)))),2).' '.$unit[$i]; ?> Memory usage. <?php } timer_stop(1) ?> seconds.</p>
<?php }
if (easel_themeinfo('enable_debug_footer_code')) {
	add_action('easel-page-foot', 'easel_debug_page_foot_code');
}

add_filter('excerpt_length', 'easel_excerpt_length');

function easel_excerpt_length($length) {
	return easel_themeinfo('excerpt_length');
}

add_filter( 'excerpt_more', 'easel_auto_excerpt_more' );

if (!function_exists('easel_auto_excerpt_more')) {
	function easel_auto_excerpt_more( $more ) {
		return __(' [&hellip;]','easel') . ' <a class="more-link" href="'. get_permalink() . '">' . __('&darr; Read the rest of this entry...','easel') . '</a>';
	}
}

if (easel_themeinfo('force_active_connection_close')) 
	add_action('shutdown_action_hook','easel_close_up_shop');

function easel_close_up_shop() {
	@mysql_close();
}

if (!function_exists('easel_is_layout')) {
	function easel_is_layout($choices) {
		$choices = explode(",", $choices);
		if (in_array(easel_themeinfo('layout'), $choices)) return true;
		return false;
	}
}

function easel_is_bbpress() {
	if (function_exists('bbp_is_single_forum') &&
			(bbp_is_forum()
				|| bbp_is_forum_archive()
				|| bbp_is_topic_archive()
				|| bbp_is_single_forum() 
				|| bbp_is_single_topic()
				|| bbp_is_topic()
				|| bbp_is_topic_edit()
				|| bbp_is_topic_merge()
				|| bbp_is_topic_split()
				|| bbp_is_single_reply()
				|| bbp_is_reply_edit()
				|| bbp_is_reply_edit()
				|| bbp_is_single_view()
				|| bbp_is_single_user_edit()
				|| bbp_is_single_user()
				|| bbp_is_user_home()
				|| bbp_is_subscriptions()
				|| bbp_is_favorites()
				|| bbp_is_topics_created()))
		return true;
	return false;
}

if (!function_exists('easel_sidebars_disabled')) {
	function easel_sidebars_disabled() {
		global $post;
		if (is_page() && !empty($post)) {
			$sidebars_disabled = get_post_meta($post->ID, 'disable-sidebars', true);
			if ($sidebars_disabled) return true;
		}
//		if (easel_is_bbpress()) return true;
		return false;
	}
}

if (easel_themeinfo('menubar_social_icons')) 
	add_action('easel-menubar-menunav', 'easel_display_social_icons');

if (!function_exists('easel_display_social_icons')) {
	function easel_display_social_icons() {
		$twitter = easel_themeinfo('menubar_social_twitter');
		$facebook = easel_themeinfo('menubar_social_facebook');
		$googleplus = easel_themeinfo('menubar_social_googleplus');
		$linkedin = easel_themeinfo('menubar_social_linkedin');
		$pinterest = easel_themeinfo('menubar_social_pinterest');
		$youtube = easel_themeinfo('menubar_social_youtube');
		$flickr = easel_themeinfo('menubar_social_flickr');
		$tumblr = easel_themeinfo('menubar_social_tumblr');
		$deviantart = easel_themeinfo('menubar_social_deviantart');
		$myspace = easel_themeinfo('menubar_social_myspace');
		$email = easel_themeinfo('menubar_social_email');
		$output = '<div class="menunav-social-wrapper">';
		if (!empty($deviantart)) $output .= '<a href="'.$deviantart.'" title="'.__(' my DeviantART','easel').'" class="menunav-social menunav-deviantart">'.__('DeviantART','easel').'</a>'."\r\n";
		if (!empty($tumblr)) $output .= '<a href="'.$tumblr.'" title="'.__('Examine my Tumblr','easel').'" class="menunav-social menunav-tumblr">'.__('Tumblr','easel').'</a>'."\r\n";
		if (!empty($facebook)) $output .= '<a href="'.$facebook.'" title="'.__('Friend on Facebook','easel').'" class="menunav-social menunav-facebook">'.__('Facebook','easel').'</a>'."\r\n";
		if (!empty($myspace)) $output .= '<a href="'.$myspace.'" title="'.__('Make use of MySpace','easel').'" class="menunav-social menunav-myspace">'.__('MySpace','easel').'</a>'."\r\n";		
		if (!empty($linkedin)) $output .= '<a href="'.$linkedin.'" title="'.__('Look at my LinkedIn','easel').'" class="menunav-social menunav-linkedin">'.__('LinkedIn','easel').'</a>'."\r\n";
		if (!empty($twitter)) $output .= '<a href="'.$twitter.'" title="'.__('Follow me on Twitter','easel').'" class="menunav-social menunav-twitter">'.__('Twitter','easel').'</a>'."\r\n";
		if (!empty($flickr)) $output .= '<a href="'.$flickr.'" title="'.__('Gaze at my Flickr','easel').'" class="menunav-social menunav-flickr">'.__('Flickr','easel').'</a>'."\r\n";		
		if (!empty($email)) $output .= '<a href="'.$email.'" title="'.__('Email me','easel').'" class="menunav-social menunav-email">'.__('Email','easel').'</a>'."\r\n";
		if (!empty($googleplus)) $output .= '<a href="'.$googleplus.'" title="'.__('Check me out on Google+','easel').'" class="menunav-social menunav-googleplus">'.__('Google+','easel').'</a>'."\r\n";
		if (!empty($pinterest)) $output .= '<a href="'.$pinterest.'" title="'.__('Peruse my Pinterests','easel').'" class="menunav-social menunav-pinterest">'.__('pinterest','easel').'</a>'."\r\n";
		if (!empty($youtube)) $output .= '<a href="'.$youtube.'" title="'.__('View my YouTube','easel').'" class="menunav-social menunav-youtube">'.__('YouTube','easel').'</a>'."\r\n";
		$output .= '<a href="'.get_bloginfo('rss2_url').'" title="'.__('RSS Feed','easel').'" class="menunav-social menunav-rss2">'.__('RSS','easel').'</a>'."\r\n";
		$output .= '<div class="clear"></div>';
		$output .= '</div>'."\r\n";
		echo $output;
	}
}

/**
 * This is function ceo_clean_filename
 *
 * @param string $filename the BASE filename
 * @return string returns the rawurlencoded filename with the %2F put back to /
 *
 */
function easel_clean_filename($filename) {
	return str_replace("%2F", "/", rawurlencode($filename));
}

/**
 * Retrieve adjacent post link.
 *
 * Can either be next or previous post link.
 * chapters is for the comic post type
 */
function easel_get_adjacent_post_type($previous = true, $taxonomy = 'post', $in_same_chapter = false) {
	global $post, $wpdb;
	
	if ( empty( $post ) ) return null;

	$current_post_date = $post->post_date;

	$join = '';

	if ( $in_same_chapter ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";

		if ( $in_same_chapter ) {
			$chapt_array = wp_get_object_terms($post->ID, 'chapters', array('fields' => 'ids'));
			if (!empty($chapt_array))
				$join .= " AND tt.taxonomy = 'chapters' AND tt.term_id IN (" . implode(',', $chapt_array) . ")";
		}
	}

	$adjacent = $previous ? 'previous' : 'next';
	$op = $previous ? '<' : '>';
	$order = $previous ? 'DESC' : 'ASC';

	$join  = apply_filters( "get_{$adjacent}_{$taxonomy}_join", $join, $in_same_chapter, $excluded_chapters );
	$where = apply_filters( "get_{$adjacent}_{$taxonomy}_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type = %s AND p.post_status = 'publish' $posts_in_ex_cats_sql", $current_post_date, $post->post_type), $in_same_chapter, $excluded_chapters );
	$sort  = apply_filters( "get_{$adjacent}_{$taxonomy}_sort", "ORDER BY p.post_date $order LIMIT 1" );

	$query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";
	$query_key = "adjacent_{$taxonomy}_" . md5($query);
	$result = wp_cache_get($query_key, 'counts');
	if ( false !== $result )
		return $result;

	$result = $wpdb->get_row("SELECT p.* FROM $wpdb->posts AS p $join $where $sort");
	if ( null === $result )
		$result = '';

	wp_cache_set($query_key, $result, 'counts');
	return $result;
}

function easel_filter_wp_title( $title ) {
	global $wp_query, $s, $paged, $page;
	if (!is_feed()) {
		$sep = __('&raquo;','easel');
		$new_title = get_bloginfo('name').' ';
		$bloginfo_description = get_bloginfo('description');	
		if ((is_home () || is_front_page()) && !empty($bloginfo_description) && !$paged && !$page) {
			$new_title .= $sep.' '.$bloginfo_description;
		} elseif (is_single() || is_page()) { 
			$new_title .= $sep.' '.single_post_title('', false);		
		} elseif (is_search() ) { 
			$new_title .= $sep.' '.sprintf(__('Search Results: %s','easel'), esc_html($s));
		} else
			$new_title .= $title;
		if ( $paged || $page ) {
			$new_title .= ' '.$sep.' '.sprintf(__('Page: %s','easel'),max( $paged, $page ));
		}
		$title = $new_title;
	}
    return $title;
}

add_filter( 'wp_title', 'easel_filter_wp_title' );
