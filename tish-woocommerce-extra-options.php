<?php
/*
Plugin Name: Tish WooCommerce Extra Options
Description: Extra Options for WooCommerce plugin.
Author: tishonator
Version: 1.0.0
Author URI: http://tishonator.com/
Contributors: tishonator
Text Domain: tish-woocommerce-extra-options
*/

if ( !class_exists('tish_woocommerce_extra_options') ) :

    /**
     * Register the plugin.
     *
     * Display the administration panel, insert JavaScript etc.
     */
    class tish_woocommerce_extra_options {
        
    	/**
    	 * Instance object
    	 *
    	 * @var object
    	 * @see get_instance()
    	 */
    	protected static $instance = NULL;


        /**
         * Constructor
         */
        public function __construct() {}

        /**
         * Setup
         */
        public function setup() {

            add_action('customize_register', array(&$this, 'customize_register') );

            add_filter( 'loop_shop_per_page', array(&$this, 'tish_woocommerce_products_per_page' ), 20 );

            add_filter('loop_shop_columns', array(&$this, 'tish_woo_loop_columns' ), 999 );

            add_action( 'wp_enqueue_scripts', array(&$this, 'tish_woo_inline_css' ) );

            if ( tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_show_blnk_rtng_shp', 0) == 1 ) {

                add_filter( 'woocommerce_product_get_rating_html',
                    array(&$this, 'tish_woocommerce_product_get_rating_html' ), 10, 3 );
            }

            if ( tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_show_blnk_rtng_sngl', 0) == 1 ) {

                add_action('woocommerce_single_product_summary',
                        array(&$this, 'tish_change_single_product_ratings' ), 2 );
            }
        }

        public function customize_register( $wp_customize ) {

            tish_woocommerce_extra_options::tish_woo_customize_register_woocommerce_settings( $wp_customize );
        }

        public static function tish_customizer_add_section( $wp_customize, $sectionId, $sectionTitle ) {

            $wp_customize->add_section(
                $sectionId,
                array(
                    'title'       => $sectionTitle,
                    'capability'  => 'edit_theme_options',
                )
            );
        }

        private static function tish_customizer_add_customize_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar, $sanitizeCallback, $type ) {

            if ($controlDefaultVar) {

                $wp_customize->add_setting( $controlId, array(
                                            'sanitize_callback' => $sanitizeCallback,
                                            'default'           => $controlDefaultVar,
                        ) );
            } else {

                $wp_customize->add_setting( $controlId, array(
                                            'sanitize_callback' => $sanitizeCallback,
                        ) );
            }

            $wp_customize->add_control( new WP_Customize_Control( $wp_customize, $controlId,
                array(
                    'label'          => $controlLabel,
                    'section'        => $sectionId,
                    'settings'       => $controlId,
                    'type'           => $type,
                    )
                )
            );
        }

        private static function tish_customizer_add_checkbox_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar ) {

            tish_woocommerce_extra_options::tish_customizer_add_customize_control( $wp_customize, $sectionId, $controlId,
                $controlLabel, $controlDefaultVar, 'esc_attr', 'checkbox' );
        }

        private static function tish_customizer_add_text_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar ) {

            tish_woocommerce_extra_options::tish_customizer_add_customize_control( $wp_customize, $sectionId, $controlId,
                $controlLabel, $controlDefaultVar, 'balanceTags', 'text' );
        }

        private static function tish_customizer_add_number_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar ) {

            tish_woocommerce_extra_options::tish_customizer_add_customize_control( $wp_customize, $sectionId, $controlId,
                $controlLabel, $controlDefaultVar, 'balanceTags', 'number' );
        }

        private static function tish_customizer_add_color_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar ) {

            if ($controlDefaultVar) {

                $wp_customize->add_setting( $controlId, array(
                            'sanitize_callback' => 'esc_attr',
                            'default'           => $controlDefaultVar,
                        ) );

            } else {

                $wp_customize->add_setting( $controlId, array(
                            'sanitize_callback' => 'esc_attr',
                        ) );
            }
            
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $controlId,
                array(
                    'label'          => $controlLabel,
                    'section'        => $sectionId,
                    'settings'       => $controlId,
                    )
                )
            );
        }

        private static function tish_customizer_add_select_control( $wp_customize, $sectionId, $controlId, $controlLabel, $controlDefaultVar, $controlChoices ) {

            $wp_customize->add_setting(
                $controlId,
                array(
                    'default'           => $controlDefaultVar,
                    'sanitize_callback' => 'esc_attr',
                )
            );

            $wp_customize->add_control( new WP_Customize_Control( $wp_customize, $controlId,
                array(
                    'label'          => $controlLabel,
                    'section'        => $sectionId,
                    'settings'       => $controlId,
                    'type'           => 'select',
                    'choices'        => $controlChoices
                    )
                )
            );
        }

        public static function tish_woo_customize_register_woocommerce_settings( $wp_customize ) {

            // Add WooCommerce Settings Section
            tish_woocommerce_extra_options::tish_customizer_add_section( $wp_customize, 'tish_woocommerce_settings', __( 'Tish WooCommerce Extra Options', 'tishonator' ) );

            // Add Number of Products per Page
            tish_woocommerce_extra_options::tish_customizer_add_number_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_productsperpage', __( 'Products per Page', 'tishonator' ), 10 );

            // Number of Columns in Footer
            tish_woocommerce_extra_options::tish_customizer_add_select_control( $wp_customize, 'tish_woocommerce_settings',
                    'tish_woocommerce_productsperrow', __( 'Number of Products per Row', 'tishonator' ),
                    '4', array( '1'    => '1',
                                '2'    => '2',
                                '3'    => '3',
                                '4'    => '4',
                                '5'    => '5',
                                '6'    => '6',
                            ) );

            // On Sale Badge Background color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_onsale_bckgr', __( 'On Sale Badge Background Color', 'tishonator' ), '#cd2653' );

            // On Sale Badge Text color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_onsale_txt', __( 'On Sale Badge Text Color', 'tishonator' ), '#FFFFFF' );

            // Ratings color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_ratings_col', __( 'Ratings Color', 'tishonator' ), '#111111' );

            // Add Price color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_pricecolor', __( 'Price Color', 'tishonator' ), '#111111' );

            // Add Deleted Price color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_del_pricecolor', __( 'Deleted Price Color', 'tishonator' ), '#444444' );

            // Add Buttons Background Color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_btn_backgr',
                __( 'Buttons Background Color', 'tishonator' ), '#cd2653' );

            // Add Buttons Text Color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_btn_txt_col',
                __( 'Buttons Text Color', 'tishonator' ), '#FFFFFF' );

            // Add Buttons Hover Background Color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_btn_hover_backgr',
                __( 'Buttons Background Color', 'tishonator' ), '#cd2653' );

            // Add Buttons Hover Text Color
            tish_woocommerce_extra_options::tish_customizer_add_color_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_btn_hover_txt_col',
                __( 'Buttons Hover Text Color', 'tishonator' ), '#FFFFFF' );

            // On Sale Badge Font Size
            tish_woocommerce_extra_options::tish_customizer_add_number_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_onsale_fs', __( 'On Sale Badge Font Size (in pixels)', 'tishonator' ), '16' );

            // Ratings Font Size
            tish_woocommerce_extra_options::tish_customizer_add_number_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_ratings_fs', __( 'Ratings Font Size (in pixels)', 'tishonator' ), '13' );

            // Price Font Size
            tish_woocommerce_extra_options::tish_customizer_add_number_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_price_fs', __( 'Price Font Size (in pixels)', 'tishonator' ), '16' );

            // Buttons Font Size
            tish_woocommerce_extra_options::tish_customizer_add_number_control( $wp_customize, 'tish_woocommerce_settings', 'tish_woocommerce_buttons_fs', __( 'Buttons Font Size (in pixels)', 'tishonator' ), '18' );

            // Add Display Blank Ratings in Shop Page
            tish_woocommerce_extra_options::tish_customizer_add_checkbox_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_show_blnk_rtng_shp', __( 'Display Blank Ratings in Shop and Category Pages', 'tishonator' ), 0 );

            // Add Display Blank Ratings in Single Product Page
            tish_woocommerce_extra_options::tish_customizer_add_checkbox_control( $wp_customize, 'tish_woocommerce_settings',
                'tish_woocommerce_show_blnk_rtng_sngl', __( 'Display Blank Ratings in Single Product Pages', 'tishonator' ), 0 );
        }

        public function tish_woocommerce_products_per_page() {

            return tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_productsperpage', 10);
        }

        public function tish_woo_loop_columns() {

            return tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_productsperrow', 4);
        }

        public function tish_woocommerce_product_get_rating_html( $rating_html, $rating, $count ) {
            $rating_html  = '<div class="star-rating">';
            $rating_html .= wc_get_star_rating_html( $rating, $count );
            $rating_html .= '</div>';

            return $rating_html;
        }

        // Display Blank Ratings in Single Product Pages
        public function tish_display_single_product_ratings(){
            global $product;

            $rating_count = $product->get_rating_count();

            if ( $rating_count >= 0 ) {
                $review_count = $product->get_review_count();
                $average      = $product->get_average_rating();
                $count_html   = '<div class="count-rating">' . array_sum($product->get_rating_counts()) . '</div>';
                ?>
                <div class="woocommerce-product-rating">
                    <div class="container-rating"><div class="star-rating">
                    <?php echo wc_get_rating_html( $average, $rating_count ); ?>
                    </div><?php /*t echo  $count_html ; t*/ ?>
                    <?php if ( comments_open() ) : ?><a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a><?php endif ?>
                </div></div>
                <?php
            }
        }

        public function tish_change_single_product_ratings(){
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_rating', 10 );
            add_action('woocommerce_single_product_summary', array(&$this, 'tish_display_single_product_ratings'), 10 );
        }

        public function tish_woo_inline_css() {

            wp_enqueue_style( 'tish-woocommerce-style', plugins_url('css/tish-woocommerce-style.css', __FILE__), array( ) );

            $custom_css = '';

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_onsale_bckgr', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce .onsale, .woocommerce-page .onsale{background:'.$colorVal.' !important;background-color:'.$colorVal.'; !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_onsale_txt', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce .onsale, .woocommerce-page .onsale{color:'.$colorVal.' !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_ratings_col', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .single-product .woocommerce-product-rating .star-rating{color:'.$colorVal.' !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_pricecolor', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce ul.products li.product .price, .woocommerce div.product p.price, .woocommerce div.product span.price, .woocommerce-Price-amount{color:'.$colorVal.' !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_del_pricecolor', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce ul.products li.product .price del, .woocommerce div.product p.price del, .woocommerce div.product span.price del, del .woocommerce-Price-amount{color:'.$colorVal.' !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_btn_backgr', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce button.button:disabled[disabled]{background-color:'.$colorVal.' !important;border:none !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_btn_txt_col', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce button.button:disabled[disabled]{color:'.$colorVal.' !important;text-decoration:none !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_btn_hover_backgr', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce #respond input#submit:hover, .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover, .woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, .woocommerce button.button:disabled[disabled]:hover{background-color:'.$colorVal.' !important;border:none !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_btn_hover_txt_col', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce #respond input#submit:hover, .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce input.button:hover, .woocommerce #respond input#submit.alt:hover, .woocommerce a.button.alt:hover, .woocommerce button.button.alt:hover, .woocommerce input.button.alt:hover, .woocommerce button.button:disabled[disabled]:hover{color:'.$colorVal.' !important;text-decoration:none !important;}';
            }
 
            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_onsale_fs', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce .onsale, .woocommerce-page .onsale{font-size:'.$colorVal.'px !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_ratings_fs', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .single-product .woocommerce-product-rating .star-rating{font-size:'.$colorVal.'px !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_price_fs', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce ul.products li.product .price, .woocommerce div.product p.price, .woocommerce div.product span.price, .woocommerce-Price-amount{font-size:'.$colorVal.'px !important;}';
            }

            $colorVal = tish_woocommerce_extra_options::tish_read_customizer_option('tish_woocommerce_buttons_fs', null);
            if ( !empty( $colorVal ) ) {
                $custom_css .= ' .woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt, .woocommerce button.button:disabled[disabled]{font-size:'.$colorVal.'px !important;}';
            }


            wp_add_inline_style( 'tish-woocommerce-style', $custom_css );
        }

        public static function tish_read_customizer_option($name, $default) {

            return get_theme_mod($name, $default);
        }

    	/**
    	 * Used to access the instance
         *
         * @return object - class instance
    	 */
    	public static function get_instance() {

    		if ( NULL === self::$instance ) {
                self::$instance = new self();
            }

    		return self::$instance;
    	}
    }

endif; // tish_woocommerce_extra_options

add_action('plugins_loaded', array( tish_woocommerce_extra_options::get_instance(), 'setup' ), 10);
