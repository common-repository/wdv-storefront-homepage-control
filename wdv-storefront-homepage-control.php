<?php
/**
 * Plugin Name:     WDV Storefront Homepage Control
 * Plugin URI:      https://webdesignvista.com/wdv-plugins/wdvsf-homepage-control
 * Description:     Show/Hide sections on Woocommerce Storefront Theme default homepage template
 * Author:          Lakhya Phukan
 * Author URI:      https://webdesignvista.com/
 * Text Domain:     web_design_vista
 * Version:         0.1.0
 *
 * @package         Web_Design_Vista
 */
class WDV_Storefront_Homepage_Control_Plugin {

    public function __construct() {
        // hook into the admin menu
        add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        add_action( 'admin_init', array( $this, 'setup_sections' ) );
        add_action( 'admin_init', array( $this, 'setup_fields' ) );

        add_action('wp_head', array($this, 'manage_homepage_frontend'));
    }

    public function setup_fields() {
        $fields = array(
            array(
        		'uid' => 'wdvsf_homepage_checks',
        		'label' => 'Storefront Homepage Blocks',
        		'section' => 'homepage_blocks_section',
        		'type' => 'checkbox',
        		'options' => array(
        			'hide_welcome' => 'Hide Welcome',
        			'hide_categories' => 'Hide Categories',
        			'hide_new' => 'Hide New Items',
        			'hide_recommended' => 'Hide Recommended Items',
                    'hide_onsale' => 'Hide On Sale Items',
                    'hide_favorites' => 'Hide Favorite Items',
                    'hide_bestsellers' => 'Hide Bestseller Items',
        		),
                'default' => array()
            ),
            array(
        		'uid' => 'wdvsf_homepage_categories_checks',
        		'label' => 'Storefront Homepage Categories',
        		'section' => 'homepage_categories_section',
        		'type' => 'checkbox',
        		'options' => $this->get_product_cats(),
                'default' => array()
        	)
        );
        foreach( $fields as $field ){
            add_settings_field( 
                $field['uid'], 
                $field['label'], 
                array( $this, 'field_callback' ), 
                'wdvsf_homepage_fields', 
                $field['section'], 
                $field 
            );
            register_setting( 'wdvsf_homepage_fields', $field['uid'] );
        }
    }

    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] ); // Get the current value, if there is one

        if( ! $value ) { // If no value exists
            $value = $arguments['default']; // Set to our default
        }
    
        // Check which type of field we want
        switch( $arguments['type'] ){
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( 
                            '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', 
                            $arguments['uid'], 
                            $arguments['type'], 
                            $key, 
                            checked( $value[ array_search( $key, $value, true ) ], $key, false ), 
                            $label, 
                            $iterator 
                        );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
            break;
        }
    
        // If there is help text
        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper ); // Show it
        }
    
        // If there is supplemental text
        if( $supplimental = $arguments['supplemental'] ){
            printf( '<p class="description">%s</p>', $supplimental ); // Show it
        }

    }

    public function setup_sections() {
        add_settings_section( 
            'homepage_blocks_section', 
            'Hide Blocks', 
            array($this, 'section_callback'), 
            'wdvsf_homepage_fields' 
        );
        add_settings_section( 
            'homepage_categories_section', 
            'Display Categories', 
            array($this, 'section_callback'), 
            'wdvsf_homepage_fields' 
        );
    }

    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'homepage_blocks_section':
    			echo 'Select blocks to hide on homepage';
    			break;
    		case 'homepage_categories_section':
    			echo 'Select categories to display on homepage';
    			break;
    	
    	}
    }  

    public function create_plugin_settings_page() {
        add_menu_page(
            'Storefront Homepage Control Settings',
            'Storefront Homepage',
            'manage_options',
            'wdvsf_homepage_fields',
            array($this, 'plugin_settings_page_content'),
            '',
            20
        );
    }

    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

    public function plugin_settings_page_content() {?>
        <div class="wrap">
            <h2>Storefront Homepage Settings</h2>

            <?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>

            <form method="post" action="options.php">
                <?php
                    settings_fields( 'wdvsf_homepage_fields' );
                    do_settings_sections( 'wdvsf_homepage_fields' );
                    submit_button();
                ?>
            </form>
        </div> <?php
    }

    public function get_product_cats() {

        $taxonomy     = 'product_cat';
        $orderby      = 'name';  
        $show_count   = 0;      // 1 for yes, 0 for no
        $pad_counts   = 0;      // 1 for yes, 0 for no
        $hierarchical = 0;      // 1 for yes, 0 for no  
        $title        = '';  
        $empty        = 1;

        $args = array(
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li'     => $title,
            'hide_empty'   => $empty
        );

       $cats = get_categories( $args );

       $show_cats = array();

       foreach( $cats as $cat ) {
            $show_cats[ 'cat-' . $cat->term_id ] = $cat->name;
       }

       return $show_cats;

    }


    public function manage_homepage_frontend() {

        $options = get_option('wdvsf_homepage_checks');
        $hide_cats = false;

        if ( $options ) {
    
        foreach ( $options as $option ) {
            switch ( $option ) {
                case 'hide_welcome':
                    # code...
                    remove_action('homepage', 'storefront_homepage_content', 10);
                    break;
                case 'hide_categories':
                    # code...
                    remove_action('homepage','storefront_product_categories', 20);
                    $hide_cats = true;
                    break;
                case 'hide_new':
                    # code...
                    remove_action('homepage','storefront_recent_products', 30);
                    break;
                case 'hide_recommended':
                    # code...
                    remove_action('homepage','storefront_featured_products', 40);
                    break;
                case 'hide_favorites':
                    # code...
                    remove_action('homepage','storefront_popular_products', 50);
                    break;
                case 'hide_onsale':
                    # code...
                    remove_action('homepage','storefront_on_sale_products', 60);
                    break;
                case 'hide_bestsellers':
                    # code...
                    remove_action('homepage','storefront_best_selling_products', 70);
                    break;                    
                
                default:
                    # code...
                    break;
            }
        }

        }

        /* Filter product categories if categories section is shown */
        if ( ! $hide_cats )
        add_filter('storefront_product_categories_shortcode_args', [$this, 'filter_product_cats_homepage'] );	

    }

    public function filter_product_cats_homepage( $args ) {
        
        $cats = get_option('wdvsf_homepage_categories_checks');

        if ( ! $cats ) return $args;
                
        $cats1 = array_map( array($this, 'get_cat_ids'), $cats );

		$total = count ( $cats );

		$args['number'] = $total;
		$args['columns'] = $total;
		$args['ids'] = implode( ",", $cats1 );
		$args['parent'] = "";

		return $args;
		//
	
    }

    public function get_cat_ids( $item ) {
        return str_replace( "cat-", "", $item );
    }

}

new WDV_Storefront_Homepage_Control_Plugin();