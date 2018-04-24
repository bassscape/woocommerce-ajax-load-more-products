<?php
	
	//-----------------------------------------------------
	// [1] Enqueue scripts and add localized parameters
	//-----------------------------------------------------
	add_action( 'wp_enqueue_scripts', 'pp_custom_scripts_enqueue' );
	function pp_custom_scripts_enqueue() {
	
		$theme = wp_get_theme(); // Get the current theme version numbers for bumping scripts to load
	
		// Make sure jQuery is being enqueued, otherwise you will need to do this.
	
		// Register custom scripts
		wp_register_script( 'woocommerce_load_more', get_stylesheet_directory_uri() . '/scripts/load_more.js', array( 'jquery' ), $theme['Version'], true); // Register script with depenancies and version in the footer
	
		// Enqueue scripts
		wp_enqueue_script('woocommerce_load_more');
	
		
		global $wp_query; // Make sure important query information is available
		
		// Use wp_localize_script to output some variables in the html of each WordPress page
		// These variables/parameters are accessible to the load_more.js file we enqueued above
		$localize_var_args = array(
			'posts' => json_encode( $wp_query->query_vars ), // everything about your loop is here
			'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
			'max_page' => $wp_query->max_num_pages,
			'ajaxurl' => admin_url( 'admin-ajax.php' )
	
		);
		wp_localize_script( 'woocommerce_load_more', 'wp_js_vars', $localize_var_args );
	
	}
	
	
	//-----------------------------------------------------
	// [3] Load More Products with AJAX
	//-----------------------------------------------------
	add_action('wp_ajax_loadmore', 'pp_loadmore_ajax_handler'); // wp_ajax_{action}
	add_action('wp_ajax_nopriv_loadmore', 'pp_loadmore_ajax_handler'); // wp_ajax_nopriv_{action}
	function pp_loadmore_ajax_handler(){
	
		// prepare our arguments for the query
		$args = json_decode( stripslashes( $_POST['query'] ), true );
		$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
		$args['post_status'] = 'publish';
	
		query_posts( $args );
	
		if( have_posts() ) :
	
			// run the loop
			while( have_posts() ): the_post();
	
				wc_get_template_part( 'content', 'product' );  // As we are loaded Woocommerce products we use wc_get_template_part 
				//echo '<p>'.get_the_title().'</p>'; // for the test purposes comment the line above and uncomment the below one
	
			endwhile;
	
		endif;
		die; // Exit the script, wp_reset_query() required!
	
	}


	//-----------------------------------------------------
	// [4] Remove Woocommerce pagination as we do not need it any more
	//-----------------------------------------------------
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
	
	
	//-----------------------------------------------------
	// [5] Add in our load more products container and button
	//-----------------------------------------------------
	add_action( 'woocommerce_after_shop_loop', 'pp_woocommerce_products_load_more', 9 );
	function pp_woocommerce_products_load_more(){
	
		echo '<div id="container_products_more">';
			woocommerce_product_loop_start();
			woocommerce_product_loop_end();
			//echo '<pre>' . var_export($wp_query, true) . '</pre>'; // For testing
			if (  $wp_query->max_num_pages > 1 ) {
				echo '<div id="pp_loadmore_products" class="button">LOAD MORE</div>';
			}
		echo '</div>';
	
	}


	//-----------------------------------------------------
	// [6] Add a new class to the woocommerce_product_loop, we need this to target it with jQuery in load_more.js
	//-----------------------------------------------------
	function woocommerce_product_loop_start() { echo '<ul class="products products_archive_grid">'; }
	function woocommerce_product_loop_end() { echo '</ul>'; }
	
?>