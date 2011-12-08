<?php
/**
 * Plugin Name: Arconix Shortcode Collection
 * Plugin URI: http://arconixpc.com/plugins/arconix-shortcodes
 * Description: A handy collection of shortcodes for your site.
 *
 * Version: 0.9.5
 *
 * Author: John Gardner
 * Author URI: http://johngardner.co/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */


/* Allow shortcodes to be used in text widgets */
add_filter( 'widget_text', 'do_shortcode' );

/* Remove the wpautop from shortcodes */
function arconix_remove_wpautop( $content ) {
    $content = do_shortcode( shortcode_unautop( $content ) );
    $content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content );
    return $content;
}

/* Start the plugin
 * shortcode javascript can be overriden by putting your own file in the root of your theme's folder
 */
add_action( 'init', 'arconix_shortcode_init' );
function arconix_shortcode_init() {

    define( 'ASC_VERSION', '0.9.5' );

    wp_register_script( 'jquery-tools', 'http://cdn.jquerytools.org/1.2.6/tiny/jquery.tools.min.js', array( 'jquery' ), '1.2.6', true );

    if( file_exists( get_stylesheet_directory() . "/arconix-shortcodes.js" ) ) {
	wp_register_script( 'arconix-shortcode-js', get_stylesheet_directory_uri() . '/arconix-shortcodes.js', array( 'jquery-tools'), ASC_VERSION, true );
    }
    elseif( file_exists( get_template_directory() . "/arconix-shortcodes.js" ) ) {
	wp_register_script( 'arconix-shortcode-js', get_template_directory_uri() . '/arconix-shortcodes.js', array('jquery-tools'), ASC_VERSION, true );
    }
    else {
	wp_register_script( 'arconix-shortcode-js', plugins_url('arconix-shortcodes.js', __FILE__), array( 'jquery-tools' ), ASC_VERSION, true );
    }
}

/* Load the plugin script (when defined to do so) */
add_action( 'wp_footer', 'arconix_shortcode_load_scripts' );
function arconix_shortcode_load_scripts() {
    global $_asc_load;

    if ( ! $_asc_load )
	return;

    wp_print_scripts( 'arconix-shortcode-js' );
}



/* Enqueue CSS (can be overriden by including your own css file in the root of your theme's folder) */
add_action( 'wp_enqueue_scripts', 'arconix_enqueue_shortcode_css' );
function arconix_enqueue_shortcode_css() {
    if( file_exists( get_stylesheet_directory() . "/arconix-shortcodes.css" ) ) {
	wp_enqueue_style( 'arconix-shortcodes', get_stylesheet_directory_uri() . '/arconix-shortcodes.css', array(), ASC_VERSION );
    }
    elseif( file_exists( get_template_directory() . "/arconix-shortcodes.css" ) ) {
	wp_enqueue_style( 'arconix-shortcodes', get_template_directory_uri() . '/arconix-shortcodes.css', array(), ASC_VERSION );
    }
    else {
	wp_enqueue_style( 'arconix-shortcodes', plugins_url( '/arconix-shortcodes.css', __FILE__ ), array(), ASC_VERSION );
    }
}

/* Register shortcodes. */
add_action( 'init', 'arconix_add_shortcodes' );

/**
 * Creates new shortcodes for use in any shortcode-ready area.  This function uses the add_shortcode()
 * function to register new shortcodes with WordPress.
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @link    http://codex.wordpress.org/Shortcode_API
 */
function arconix_add_shortcodes() {

    /* Utility */
    add_shortcode( 'loginout-link', 'arconix_loginout_link_shortcode' );
    add_shortcode( 'map', 'arconix_googlemap_shortcode');
    add_shortcode( 'site-link', 'arconix_site_link_shortcode' );
    add_shortcode( 'the-year', 'arconix_the_year_shortcode' );
    add_shortcode( 'wp-link', 'arconix_wp_link_shortcode' );

    /* Styles */
    add_shortcode( 'abbr', 'arconix_abbr_shortcode' );
    add_shortcode( 'accordions', 'arconix_accordions_shortcode', 90 );
    add_shortcode( 'accordion', 'arconix_accordion_shortcode', 99 );
    add_shortcode( 'box', 'arconix_box_shortcode' );
    add_shortcode( 'button', 'arconix_button_shortcode' );
    add_shortcode( 'highlight', 'arconix_highlight_shortcode' );
    add_shortcode( 'list', 'arconix_list_shortcode' );
    add_shortcode( 'tabs', 'arconix_tabs_shortcode', 90 );
    add_shortcode( 'tab', 'arconix_tab_shortcode', 99 );
    add_shortcode( 'toggle', 'arconix_toggle_shortcode' );

    /* Content Columns */
    /* = Two Columns */
    add_shortcode( 'one-half', 'arconix_one_half_shortcode' );
    /* = Three Columns */
    add_shortcode( 'one-third', 'arconix_one_third_shortcode' );
    add_shortcode( 'two-thirds', 'arconix_two_thirds_shortcode' );
    /* = Four Columns */
    add_shortcode( 'one-fourth', 'arconix_one_fourth_shortcode' );
    add_shortcode( 'two-fourths', 'arconix_two_fourths_shortcode' );
    add_shortcode( 'three-fourths', 'arconix_three_fourths_shortcode' );
    /* = Five Columns */
    add_shortcode( 'one-fifth', 'arconix_one_fifth_shortcode' );
    add_shortcode( 'two-fifths', 'arconix_two_fifths_shortcode' );
    add_shortcode( 'three-fifths', 'arconix_three_fifths_shortcode' );
    add_shortcode( 'four-fifths', 'arconix_four_fifths_shortcode' );

}


/**
 * Shortcode to display a login link or logout link.
 *
 * @package arconix-shortcodes
 * @since   0.9
 */
function arconix_loginout_shortcode() {
    $textdomain = 'arconix-shortcodes';
    if ( is_user_logged_in() )
	$return = '<a class="arconix-logout-link" href="' . esc_url( wp_logout_url( site_url( $SERVER['REQUEST_URI'] ))) . '" title="' . esc_attr__( 'Log out of this site', $textdomain ) . '">' . __( 'Log out', $textdomain ) . '</a>';
    else
	$return = '<a class="arconix-login-link" href="' . esc_url( wp_login_url( site_url( $SERVER['REQUEST_URI'] ))) . '" title="' . esc_attr__( 'Log in to this site', $textdomain ) . '">' . __( 'Log in', $textdomain ) . '</a>';

    return $return;
}

/**
 * Shortcode a Google Map based on the URL provided
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [map w="640" h="400" url="htp://..."]
 */
function arconix_googlemap_shortcode( $atts ) {

    $defaults = apply_filters( 'arconix_googlemap_shortcode_args',
	array(
	    'w' => '640',
	    'h' => '400',
	    'url' => ''
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    return '<iframe width="'.$w.'" height="'.$h.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$url.'&amp;return=embed"></iframe>';
}

/**
 * Shortcode to display a link back to the site.
 *
 * @package arconix-shortcodes
 * @since   0.9
 */
function arconix_site_link_shortcode() {
    return '<a class="arconix-site-link" href="' . home_url() . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" rel="home"><span>' . get_bloginfo( 'name' ) . '</span></a>';
}

/**
 * Shortcode to display the current 4-digit year.
 *
 * @package arconix-shortcodes
 * @since   0.9
 */
function arconix_the_year_shortcode() {
    return '<span class="arconix-the-year">'. date( 'Y' ). '</span>';
}

/**
 * Shortcode to return a link to WordPress.org.
 *
 * @package arconix-shortcodes
 * @since   0.9
 */
function arconix_wp_link_shortcode() {
    return '<a class="arconix-wp-link" href="http://wordpress.org" title="' . esc_attr__( 'This site is powered by WordPress', 'arconix-shortcodes' ) . '"><span>' . __( 'WordPress', 'arconix-shortcodes' ) . '</span></a>';
}

/**
 * Shortcode to handle abbreviations
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [abbr title="Frequently Asked Questions"]FAQ[/abbr]
 */
function arconix_abbr_shortcode ( $atts, $content = null ) {
	$defaults = apply_filters( 'arconix_abbr_shortcode_args', array( 'title' => '' ) );
	extract( shortcode_atts( $defaults, $atts ) );

    return '<abbr class="arconix-abbr" title="' . $title . '">' . $content . '</abbr>';
}

/**
 * Shortcode to produce jQuery-powered accordion group
 *
 * @package arconix-shortcodes
 * @since	0.9
 * @version	0.9.1
 *
 */
function arconix_accordions_shortcode( $atts, $content = null ) {
    global $_asc_load;
    $_asc_load = true;

    /*
    Supported Attributes
	type	=>  vertical
	start	=>  none, 1, 2, 3, 4, 5
    */
    $defaults = apply_filters( 'arconix_accordions_shortcode_args',
	array(
	    'type' => 'vertical',
	    'load' => '1',
	    'css' => ''
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    if ( $css != '' ) { $css = ' ' . $css; }

    return '<div class="arconix-accordions arconix-accordions-' . $type . ' arconix-accordions-'. $load . $css .'">'. arconix_remove_wpautop( $content ) . '</div>';
}

/**
 * Shortcode to produce jQuery-powered accordion
 *
 * @package arconix-shortcodes
 * @since	0.9
 *
 */
function arconix_accordion_shortcode( $atts, $content = null ) {
    $defaults = apply_filters( 'arconix_accordion_shortcode_args',
	array(
	    'title' => '',
	    'last' => ''
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    if ( $last != '' ) { $last = ' arconix-accordion-last'; }

    $return = '<h3 class="arconix-accordion-title accordion-'. sanitize_title( $title ) . $last . '><a href="#">' . $title .'</a></h3>';
    $return .= '<div class="arconix-accordion-content'. $last . '">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to produce a styled box
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [box style="comment"]my content[/box]
 */
function arconix_box_shortcode( $atts, $content = null ) {
    /*
    Supported Attributes
	style   =>  blue, green, grey, red, tan, yellow	-> creates boxes using only those colors
	    OR
	style   =>  alert, comment, download, info, tip	-> boxes with the corresponding icon to the left of the text
    */
    $defaults = apply_filters( 'arconix_box_shortcode_args',
	array(
	    'style' => 'grey'
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    return '<p class="arconix-box arconix-box-'. $style .'">'. arconix_remove_wpautop( $content ) .'</p>';
}

/**
 * Shortcode to produce a styled button
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [button size="small" color="red"]My Button Text[/button]
 */
function arconix_button_shortcode( $atts, $content = null ) {
    /*
    Supported Attributes
	size    =>  large, medium, small
	color   =>  black, blue, green, grey, orange, pink, red, white
    */
    $defaults = apply_filters( 'arconix_button_shortcode_args',
	array(
	    'size' => 'medium',
	    'color' => 'white',
	    'url' => '#'
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    return '<a class="arconix-button arconix-button-'. $size .' arconix-button-'. $color .'" href="'. $url .'">'. $content .'</a>';
}

/**
 * Shortcode to highlight text
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [highlight]
 * @example [highlight color="color-name"]my content[/highlight]
 */
function arconix_highlight_shortcode( $atts, $content = null ) {
    /*
    Supported Attributes
	color   =>  yellow
    */
    $defaults = apply_filters( 'arconix_highlight_shortcode_args', array( 'color' => 'yellow' ) );

    extract( shortcode_atts( $defaults, $atts ) );

    return '<span class="arconix-highlight arconix-highlight-'. $color .'">' . do_shortcode( $content ) . '</span>';
}

/**
 * Shortcode outputs a styled unordered list
 *
 * @package arconix-shortcodes
 * @since   0.9
 * @example [list style="arrow-green"]unordered list here[/list]
 */
function arconix_list_shortcode( $atts, $content = null ) {
    /*
    Supported Attributes
	style   =>  arrow-black, arrow-blue, arrow-green, arrow-grey, arrow-orange, arrow-pink, arrow-red, arrow-white, check, close, star
    */

    $defaults = apply_filters( 'arconix_list_shortcode_args', array( 'style' => 'arrow-white' ) );

    extract( shortcode_atts( $defaults, $atts ) );

    return '<div class="arconix-list arconix-list-' . $style . '">' . arconix_remove_wpautop( $content ) . '</div>';
}

/**
 * Shortcode to produce a jQuery-powered tabbed group
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_tabs_shortcode( $atts, $content = null ) {
    global $_asc_load;
    $_asc_load = true;

    $defaults = apply_filters( 'arconix_tabs_shortcode_args',
	array(
	    'style' => 'horizontal',
	    'css' => ''
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    if ( $css != '' ) { $css = ' ' . $css; }

    $GLOBALS['tab_count'] = 0;

    do_shortcode( $content );

    if (is_array($GLOBALS['tabs'])) {
	foreach ($GLOBALS['tabs'] as $tab) {
	    $tabs[] = '<li class="arconix-tab tab-'. sanitize_title( $tab['title'] ). '"><a class="" href="#">' . $tab['title'] . '</a></li>';
	    $panes[] = '<div class="arconix-pane pane-' . sanitize_title( $tab['title'] ) .'">' . arconix_remove_wpautop( $tab['content'] ) . '</div>';
	}
	$return = "\n" . '<div class="arconix-tabs-'. $style . $css .'"><ul class="arconix-tabs">' . implode("\n", $tabs) . '</ul>' . "\n" . '<div class="arconix-panes">' . implode("\n", $panes) . '</div></div>' . "\n";
    }
    return $return;
}

/**
 * Shortcode to produce a jQuery-powered tab as part of a tabbed group
 *
 * @package arconix-shortcodes
 * @since 	0.9
 *
 */
function arconix_tab_shortcode( $atts, $content = null ) {
    $defaults = apply_filters( 'arconix_tab_shortcode_args', array( 'title' => 'Tab' ) );
    extract( shortcode_atts( $defaults, $atts ) );

    $x = $GLOBALS['tab_count'];
    $GLOBALS['tabs'][$x] = array( 'title' => sprintf( $title, $GLOBALS['tab_count'] ), 'content' =>  $content );

    $GLOBALS['tab_count']++;
}

/**
 * Shortcode to produce a jQuery-powered toggle-box
 *
 * @package arconix-shortcodes
 * @since 	0.9
 * @example [toggle heading="h3" title="My Toggle Title" css="my-custom-class"]My Text to toggle[/toggle]
 */
function arconix_toggle_shortcode( $atts, $content = null ) {
    global $_asc_load;
    $_asc_load = true;

    $defaults = apply_filters( 'arconix_toggle_shortcode_args',
	array(
	    'heading' => 'h4',
	    'title' => '',
	    'css' => ''
	)
    );
    extract( shortcode_atts( $defaults, $atts ) );

    $return = '<div class="arconix-toggle-wrap"><'. $heading .' class="arconix-toggle-title">'. $title .'</'. $heading .'><div class="arconix-toggle-content">'. arconix_remove_wpautop( $content ) .'</div></div>';
    $css_start = '<div class="'. $css . '">';
    $css_end = '</div>';

    if( $css ) $return = $css_start . $return . $css_end;

    return $return;
}

/**
 * Shortcode to display a 1/2 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_one_half_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-one-half'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 1/3 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_one_third_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-one-third'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 2/3 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_two_thirds_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-two-thirds'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 1/4 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_one_fourth_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-one-fourth'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 2/4 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_two_fourths_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-two-fourths'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 3/4 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_three_fourths_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-three-fourths'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 1/5 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_one_fifth_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-one-fifth'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 2/5 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_two_fifths_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-two-fifths'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 3/5 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_three_fifths_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-three-fifths'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

/**
 * Shortcode to display a 4/5 column
 *
 * @package arconix-shortcodes
 * @since   0.9
 *
 */
function arconix_four_fifths_shortcode( $atts, $content ) {
    extract( shortcode_atts( array( 'last' => '' ), $atts ) );

    if ( $last != '' ) { $last = ' arconix-column-last'; }

    $return = '<div class="arconix-column-four-fifths'. $last .'">'. arconix_remove_wpautop( $content ) . '</div>';

    return $return;
}

?>