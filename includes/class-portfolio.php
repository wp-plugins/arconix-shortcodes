<?php
/**
 * This file contains the Arconix_Portfolio class.
 *
 * This class handles the creation of the "Portfolio" post type, and creates a
 * UI to display the Portfolio-specific data on the admin screens.
 */

class Arconix_Portfolio {

    /**
     * Construct Method
     */
    function __construct() {

	add_action( 'init', array( $this, 'create_post_type' ) );
	add_action( 'init', array( $this, 'create_taxonomy' ) );

	add_filter( 'manage_edit-portfolio_columns', array( $this, 'columns_filter' ) );
	add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );

	add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

	add_action( 'after_setup_theme', array( $this, 'add_post_thumbnail_support' ), '9999' );
	add_image_size( 'portfolio-mini', 125, 125, TRUE );
	add_image_size( 'portfolio-thumb', 275, 200, TRUE );
	add_image_size( 'portfolio-large', 620, 9999 );

	add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_plugin_css' ) );
	add_action( 'admin_head', array( $this, 'admin_style' ) );

	add_action( 'right_now_content_table_end', array( $this, 'add_portfolio_counts' ) );
	add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );

	add_filter( 'widget_text', 'do_shortcode' );
	add_shortcode('portfolio', array( $this, 'portfolio_shortcode') );

	// Add the theme name as a filter to the body class for styling purposes
	add_filter( 'body_class', array( $this, 'filter_body_class' ) );

    }

    /**
     * Create Portfolio Post Type
     *
     * @since 0.9
     */
    function create_post_type() {

	$args = apply_filters( 'arconix_portfolio_post_type_args',
	    array(
		'labels' => array(
		    'name' => __( 'Portfolio', 'acp' ),
		    'singular_name' => __( 'Portfolio', 'acp' ),
		    'add_new' => __( 'Add New', 'acp' ),
		    'add_new_item' => __( 'Add New Portfolio Item', 'acp' ),
		    'edit' => __( 'Edit', 'acp' ),
		    'edit_item' => __( 'Edit Portfolio Item', 'acp' ),
		    'new_item' => __( 'New Item', 'acp' ),
		    'view' => __( 'View Portfolio', 'acp' ),
		    'view_item' => __( 'View Portfolio Item', 'acp' ),
		    'search_items' => __( 'Search Portfolio', 'acp' ),
		    'not_found' => __( 'No portfolio items found', 'acp' ),
		    'not_found_in_trash' => __( 'No portfolio items found in Trash', 'acp' )
		),
		'public' => true,
		'query_var' => true,
		'menu_position' => 20,
		'menu_icon' => ACP_URL . 'images/portfolio-icon-16x16.png',
		'has_archive' => true,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		'rewrite' => array( 'slug' => 'portfolio' )
	    )
	);

	register_post_type( 'portfolio' , $args);
    }

    /**
     * Create the Custom Taxonomy
     *
     * @since 0.9
     */
    function create_taxonomy() {

	$args = apply_filters( 'arconix_portfolio_taxonomy_args',
	    array(
		'labels' => array(
		    'name' => __( 'Features', 'acp' ),
		    'singular_name' => __( 'Feature', 'acp' ),
		    'search_items' =>  __( 'Search Features', 'acp' ),
		    'popular_items' => __( 'Popular Features', 'acp' ),
		    'all_items' => __( 'All Features', 'acp' ),
		    'parent_item' => null,
		    'parent_item_colon' => null,
		    'edit_item' => __( 'Edit Feature' , 'acp' ),
		    'update_item' => __( 'Update Feature', 'acp' ),
		    'add_new_item' => __( 'Add New Feature', 'acp' ),
		    'new_item_name' => __( 'New Feature Name', 'acp' ),
		    'separate_items_with_commas' => __( 'Separate features with commas', 'acp' ),
		    'add_or_remove_items' => __( 'Add or remove features', 'acp' ),
		    'choose_from_most_used' => __( 'Choose from the most used features', 'acp' ),
		    'menu_name' => __( 'Features', 'acp' ),
		),
		'hierarchical' => false,
		'show_ui' => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var' => true,
		'rewrite' => array( 'slug' => 'feature' )
	    )
	);


	register_taxonomy('feature', 'portfolio', $args );

    }

    /**
     * Correct messages when Portfolio post type is saved
     *
     * @global type $post
     * @global type $post_ID
     * @param type $messages
     * @return type
     * @since 0.9
     */
    function updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['portfolio'] = array(
	    0 => '', // Unused. Messages start at index 1.
	    1 => sprintf( __('Portfolio Item updated. <a href="%s">View portfolio item</a>'), esc_url( get_permalink($post_ID) ) ),
	    2 => __('Custom field updated.'),
	    3 => __('Custom field deleted.'),
	    4 => __('Portfolio item updated.'),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __('Portfolio item restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __('Portfolio item published. <a href="%s">View portfolio item</a>'), esc_url( get_permalink($post_ID) ) ),
	    7 => __('Portfolio item saved.'),
	    8 => sprintf( __('Portfolio item submitted. <a target="_blank" href="%s">Preview portfolio item</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	    9 => sprintf( __('Portfolio item scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview portfolio item</a>'),
	      // translators: Publish box date format, see http://php.net/date
	      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	    10 => sprintf( __('Portfolio item draft updated. <a target="_blank" href="%s">Preview portfolio item</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);

      return $messages;
    }

    /**
     * Filter the columns on the admin screen and define our own
     *
     * @param type $columns
     * @return string
     * @since 0.9
     */
    function columns_filter ( $columns ) {

	$columns = array(
	    'cb' => '<input type="checkbox" />',
	    'portfolio_thumbnail' => __( 'Image', 'acp' ),
	    'title' => __( 'Title', 'acp' ),
	    'portfolio_description' => __( 'Description', 'acp' ),
	    'portfolio_features' => __( 'Features', 'acp' )
	);

	return $columns;
    }

    /**
     * Filter the data that shows up in the columns we defined above
     *
     * @global type $post
     * @param type $column
     * @since 0.9
     */
    function columns_data( $column ) {

	global $post;

	switch( $column ) {
	    case "portfolio_thumbnail":
		printf( '<p>%s</p>', the_post_thumbnail('portfolio-mini' ) );
		break;
	    case "portfolio_description":
		the_excerpt();
		break;
	    case "portfolio_features":
		echo get_the_term_list( $post->ID, 'feature', '', ', ', '' );
		break;
	}
    }

    /**
     * Check for post-thumbnails and add portfolio post type to it
     *
     * @global type $_wp_theme_features
     * @since 0.9
     */
    function add_post_thumbnail_support() {

	global $_wp_theme_features;

	if( !isset( $_wp_theme_features['post-thumbnails'] ) ) {

	    $_wp_theme_features['post-thumbnails'] = array( array( 'portfolio' ) );
	}

	elseif( is_array( $_wp_theme_features['post-thumbnails'] ) ) {

	    $_wp_theme_features['post-thumbnails'][0][] = 'portfolio';
	}
}

    /**
     * Portfolio Shortcode
     *
     * @param type $atts
     * @param type $content
     * @since 0.9
     */
    function portfolio_shortcode( $atts, $content = null ) {
	/*
	Supported Attributes
	    link =>  page, image
	    thumb => any built-in image size
	    full => any built-in image size (this setting is ignored of 'link' is set to 'page')
	    display => , content, excerpt
	*/

	/**
	 * Currently 'image' is the only supported link option right now
	 *
	 * While 'page' is an available option, it can potentially require a lot of work on the part of the
	 * end user since the plugin can't possibly know what theme it's being used with and create the necessary
	 * page structure to properly integrate into the theme. Selecting page is only advised for advanced users.
	 */


	$defaults = apply_filters( 'arconix_portfolio_shortcode_args',
	    array(
		'link' => 'image',
		'thumb' => 'portfolio-thumb',
		'full' => 'portfolio-large',
		'display' => ''
	    )
	);
	extract( shortcode_atts( $defaults, $atts ) );

	$args = apply_filters( 'arconix_portfolio_shortcode_query_args',
	    array(
		'post_type' => 'portfolio',
		'posts_per_page' => -1, // show all
		'orderby' => 'date',
		'order' => 'DESC'
	    )
	);

	/** create a new query bsaed on our own arguments */
	$portfolio_query = new WP_Query( $args );

	if( $portfolio_query->have_posts() ) {
	    global $post;
	    echo '<ul class="arconix-portfolio-list">';

	    while( $portfolio_query->have_posts() ) : $portfolio_query->the_post();
		echo '<li class="arconix-portfolio-list-item">';
		the_title( '<h3 class="arconix-portfolio-title">', '</h3>' ) ;

		switch ( $link ) {
		    case "page" :
			echo '<a href="';
			the_permalink();
			echo '" rel="bookmark"';
			the_title_attribute('echo=0');
			echo '>';
			the_post_thumbnail( $thumb );
			echo '</a>';
			break;

		    case "image" :
			$_portfolio_img_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $full );

			echo '<a href="' . $_portfolio_img_url[0] . '" title="' . the_title_attribute('echo=0') . '" >';
			the_post_thumbnail( $thumb );
			echo '</a>';
			break;

		    default : // If it's anything else, return nothing.
			break;

		}

		// Display the custom taxonomy
		echo get_the_term_list( $post->ID, 'feature', '<div class="arconix-portfolio-tax-list"><span class="arconix-portfolio-tax-title">Features: </span>', ', ', '</div>' );

		// Display the content
		switch ( $display ) {
		    case "content" :
			echo '<div class="arconix-portfolio-content">';
			the_content();
			echo '</div>';
			break;

		    case "excerpt" :
			echo '<div class="arconix-portfolio-excerpt">';
			the_excerpt();
			echo '</div>';
			break;

		    default : // If it's anything else, return nothing.
			break;

		}
		echo '</li>';

	    endwhile;
		echo '</ul>';

	} else {

	    _e( "There are no portfolio items yet" , 'arconix-portfolio' );
	}
	/** destroy our query so that nothing else gets messed up */
	wp_reset_postdata();

    }

    /**
     * Add the Portfolio Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     * @return type
     */
    function add_portfolio_counts() {

	$args = array(
	    'public' => true ,
	    '_builtin' => false
	);
	$output = 'object';
	$operator = 'and';

	$num_posts = wp_count_posts( 'portfolio' );
	$num = number_format_i18n( $num_posts->publish );
	$text = _n( 'Portfolio Item', 'Portfolio Items', intval($num_posts->publish) );
	if ( current_user_can( 'edit_posts' ) ) {

	    $num = "<a href='edit.php?post_type=portfolio'>$num</a>";
	    $text = "<a href='edit.php?post_type=portfolio'>$text</a>";

	}
	echo '<td class="first b b-portfolio">' . $num . '</td>';
	echo '<td class="t portfolio">' . $text . '</td>';
	echo '</tr>';

	if ( $num_posts->pending > 0 ) {
	    $num = number_format_i18n( $num_posts->pending );
	    $text = _n( 'Portfolio Item Pending', 'Portfolio Items Pending', intval($num_posts->pending) );
	    if ( current_user_can( 'edit_posts' ) ) {
		$num = "<a href='edit.php?post_status=pending&post_type=portfolio'>$num</a>";
		$text = "<a href='edit.php?post_status=pending&post_type=portfolio'>$text</a>";
	    }
	    echo '<td class="first b b-portfolio">' . $num . '</td>';
	    echo '<td class="t portfolio">' . $text . '</td>';

	    echo '</tr>';
	}

	$taxonomies = get_taxonomies( $args , $output , $operator );

	foreach( $taxonomies as $taxonomy ) {
	    $num_terms  = wp_count_terms( $taxonomy->name );
	    $num = number_format_i18n( $num_terms );
	    $text = _n( $taxonomy->labels->singular_name, $taxonomy->labels->name , intval( $num_terms ) );
	    if ( current_user_can( 'manage_categories' ) ) {

	      $num = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$num</a>";
	      $text = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$text</a>";

	    }
	    echo '<tr><td class="first b b-' . $taxonomy->name . '">' . $num . '</td>';
	    echo '<td class="t ' . $taxonomy->name . '">' . $text . '</td></tr>';
      }

    }


    function admin_style() {
	printf( '<style type="text/css" media="screen">.icon32-posts-portfolio { background: transparent url(%s) no-repeat !important; }</style>', ACP_URL . 'images/portfolio-icon-32x32.png' );
    }

    /**
     * Load the plugin css. If the css file is present in the theme directory, it will be loaded instead,
     * allowing for an easy way to override the default template
     *
     * @since 0.9
     */
    function enqueue_plugin_css() {

	if( file_exists( get_stylesheet_directory() . "/arconix-portfolio.css" ) ) {
	    wp_enqueue_style( 'arconix-portfolio', get_stylesheet_directory_uri() . '/arconix-portfolio.css', array(), ACP_VERSION );
	}
	elseif( file_exists( get_template_directory() . "/arconix-shortcodes.css" ) ) {
	    wp_enqueue_style( 'arconix-portfolio', get_template_directory_uri() . '/arconix-portfolio.css', array(), ACP_VERSION );
	}
	else {
	    wp_enqueue_style( 'arconix-portfolio', plugins_url( '/arconix-portfolio.css', __FILE__), array(), ACP_VERSION );
	}
    }

    /**
     * Filter the body class and add the themename for easy plugin styling
     *
     * @param array $classes
     * @return type array
     *
     * @since 0.9
     */
    function filter_body_class( $classes ) {

	$theme_info = get_theme_data( STYLESHEETPATH . '/style.css' );
	$theme_name = $theme_info['Name'];

	/* normalize the theme name by replacing spaces with dashes and forcing to lowercase
	 * this will help with base plugin styling as 1 stylesheet can contain multiple themes defaults
	 */
	$theme_name = strtolower(
	    str_replace( " ", "-", $theme_name )
	);

	/** add the theme name to the classes array */
	$classes[] = $theme_name;

	return $classes;
    }

    /**
     * Adds a widget to the dashboard.
     *
     * @since 0.9.1
     */
    function register_dashboard_widget() {
        /*if ( ! isset( $widget_options['ac-portfolio'] ) ) {
		$update = true;
		$widget_options['ac-portfolio'] = array(
                    'link' => 'http://arconixpc.com/tag/arconix-portfolio/',
                    'url' => 'http://arconixpc.com/tag/arconix-portfolio/feed/',
                    'title' => 'Arconix Portfolio',
                    'items' => 4,
                    'show_summary' => 1,
                    'show_author' => 0,
                    'show_date' => 1,
		);
	}*/

        wp_add_dashboard_widget('ac-portfolio', 'Arconix Portfolio', array( $this, 'dashboard_widget_output' ) );
    }

    /**
     * Output for the dashboard widget
     *
     * @since 0.9.1
     */
    function dashboard_widget_output() {
        //echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
        echo '<div class="rss-widget">';

        wp_widget_rss_output( array(
            'url' => 'http://arconixpc.com/tag/arconix-portfolio/feed', // feed url
            'title' => 'Arconix Portfolio Posts', // feed title
            'items' => 3, //how many posts to show
            'show_summary' => 1, // display excerpt
            'show_author' => 0, // display author
            'show_date' => 1 // display post date
        ) );

        echo '<div class="acp-widget-bottom"><ul>'; ?>
            <li><img src="<?php echo ACP_URL . 'images/page_16.png'?>"><a href="http://arcnx.co/apwiki">Wiki Page</a></li>
            <li><img src="<?php echo ACP_URL . 'images/help_16.png'?>"><a href="http://wordpress.org/tags/arconix-portfolio?forum_id=10">Support Forum</a></li>
        <?php echo '</ul></div>';
        echo "</div>";

        // handle the styling
        echo '<style type="text/css">
            #ac-portfolio .rsssummary { display: block; }
            #ac-portfolio .acp-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
            #ac-portfolio .acp-widget-bottom ul { list-style: none; }
            #ac-portfolio .acp-widget-bottom ul li { display: inline; padding-right: 9%; }
            #ac-portfolio .acp-widget-bottom img { padding-right: 3px; vertical-align: middle; }
        </style>';
    }

    /**
     * Callback function for the "configure" option on the dashboard widget
     *
     * @since
     */
    function dashboard_widget_control() {
        //wp_dashboard_rss_control( 'ac-portfolio', array( 'link' => false, 'title' => false, 'show_author' => false ) );
    }

}
?>