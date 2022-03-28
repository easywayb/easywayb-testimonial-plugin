<?php
 
/*
 
Plugin Name: EasyWayB Testimonials
 
Plugin URI: https://www.easywayb.com/discussion/easywayb-testimonials-plugin/
 
Description: Plugin to registers a post type as testimonial and display from shortcode as slider.
 
Version: 1.0
 
Author: EasyWayB
 
Author URI: https://easywayb.com/
 
License: GPLv2 or later
 
Text Domain: easywayb
 
*/

function easywayb_register_post_type() {

    $labels = array( 
        'name' => _x('Testimonials', 'post type general name'), 
        'singular_name' => _x('Testimonial', 'post type singular name'), 
        'add_new' => _x('Add New', 'Testimonial'), 
        'add_new_item' => __('Add New Testimonial'), 
        'edit_item' => __('Edit Testimonial'), 
        'new_item' => __('New Testimonial'), 

        'view_item' => __('View Testimonial'), 
        'search_items' => __('Search Testimonials'), 
        'not_found' => __('Nothing found'), 
        'not_found_in_trash' => __('Nothing found in Trash'), 
        'parent_item_colon' => '' 
    );   

    $args = array( 
        'labels' => $labels, 
        'public' => true, 
        'publicly_queryable' => true, 
        'show_ui' => true, 
        'query_var' => true, 
        'menu_icon' => 'dashicons-format-quote', 
        'rewrite' => array( 'slug' => 'easywayb_testimonial', 'with_front'=> false ), 
        'capability_type' => 'post', 
        'hierarchical' => true,
        'has_archive' => true,  
        'menu_position' => null, 
        'supports' => array('title','editor','thumbnail') 
    );   

    register_post_type( 'easywayb_testimonial' , $args ); 

    register_taxonomy( 'easywayb_testimonial_category', array('easywayb_testimonial'), array(
        'hierarchical' => true, 
        'label' => 'Categories', 
        'singular_label' => 'Category', 
        'rewrite' => array( 'slug' => 'easywayb_testimonial_category', 'with_front'=> false )
        )
    );

    register_taxonomy_for_object_type( 'easywayb_testimonial_category', 'easywayb_testimonial' ); // Better be safe than sorry
}

add_action( 'init', 'easywayb_register_post_type' );

function add_easywayb_testimonial_category_columns( $columns ) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter( 'manage_edit-easywayb_testimonial_category_columns', 'add_easywayb_testimonial_category_columns',10,1 );

function add_easywayb_testimonial_category_column_content($content,$column_name,$term_id){
    switch ($column_name) {
        case 'shortcode':
            //do your stuff here with $term or $term_id
            $content = '[ewb-testimonial cat-id="'.$term_id.'" order="ASC" orderby="date" count="-1"]';
            break;
        default:
            break;
    }
    return $content;
}
add_filter('manage_easywayb_testimonial_category_custom_column', 'add_easywayb_testimonial_category_column_content',10,3);

function easywayb_add_theme_scripts() {
	wp_enqueue_style( 'easywayb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
	
	wp_enqueue_style( 'easywayb-style', plugin_dir_url( __DIR__ ).'easywayb-testimonials/assets/css/style.css' );
	
	wp_enqueue_style( 'easywayb-materialize', plugin_dir_url( __DIR__ ).'easywayb-testimonials/assets/css/materialize.min.css');
	
	wp_enqueue_script( 'easywayb-materialize', plugin_dir_url( __DIR__ ).'easywayb-testimonials/assets/js/materialize.min.js','','',true);
	
	wp_enqueue_script( 'easywayb-custom', plugin_dir_url( __DIR__ ).'easywayb-testimonials/assets/js/custom.js','','',true);
	
}
add_action( 'wp_enqueue_scripts', 'easywayb_add_theme_scripts', 10, 3 );

add_shortcode( 'ewb-testimonial', 'func_ewb_testimonial' );
function func_ewb_testimonial( $atts ) {
	$cat_id = (!empty($atts['cat-id']) ? $atts['cat-id'] :'');
	$count = (!empty($atts['count']) ? $atts['count'] :'-1');
	$order = (!empty($atts['order']) ? $atts['order'] :'ASC');
	$orderby = (!empty($atts['orderby']) ? $atts['orderby'] :'date');
	
	$html = '<div class="easywayb-testimonials">';
	
	$args = array(
		'post_type' => 'easywayb_testimonial',
		'post_status' => 'publish',
		'order' => $order,
		'orderby' => $orderby,
		'posts_per_page' => $count
	);
	if(!empty($cat_id)){
		$args = array(
			'post_type' => 'easywayb_testimonial',
			'post_status' => 'publish',
			'order' => $order,
			'orderby' => $orderby,
			'posts_per_page' => $count,
			'tax_query' => array(
				'relation' => 'AND',
				array(
				  'taxonomy' => 'easywayb_testimonial_category',
				  'field' => 'id',
				  'terms' => array( $cat_id ),
				  'include_children' => true,
				  'operator' => 'IN'
				)
			)
		);
	}
	
	
    $the_query = new WP_Query( $args );
	// The Loop
	if ( $the_query->have_posts() ) :
	$i=1;
	$html .= '<div class="ewb-carousel carousel">';
	while ( $the_query->have_posts() ) : $the_query->the_post();
		$avatar = (!empty(get_the_post_thumbnail_url()) ? get_the_post_thumbnail_url() : plugin_dir_url( __DIR__ ).'easywayb-testimonials/assets/images/not-found.jpg');
		$html .= '<div class="carousel-item">
              <div class="shadow-effect">
				<p>
				<i class="fa fa-quote-left"></i><br>
                '.get_the_content().'</p>
              </div>
			  <div class="testimonial-avatar"><img  src="'.$avatar.'" alt="'.get_the_title().'"></div>
              <div class="testimonial-name">'.get_the_title().'</div>
            </div>';
	$i++;
	endwhile;
	//$html .= '<div class="ewb-carousel-nav"><span class="ewb-prev">&#8592;</span> <span class="ewb-next">&#8594;</span></div>';
	$html .= '</div>';
	else:
	$html .= '<h6>Nothing found</h6>';
	endif;
	
	$html .= '</div>';

	// Reset Post Data
	wp_reset_postdata();
	
	return $html;
}