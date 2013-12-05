<?php
/*
Plugin Name: Sketchbook Plugin
Plugin URI: http://takahironoguchi.com
Description: A custom tumblr style wall plugin. Requires installation of Elliot Condon's ACF plugin: www.advancedcustomfields.com. And uses sy4mil's Aqua-Resizer https://github.com/sy4mil/Aqua-Resizer
Version: 1.0
Author: Takahiro Noguchi
Author URI: http://takahironoguchi.com/
License: GPLv2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 *  
 * 
 * Copyright (c) 2013, Takahiro Noguchi
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     Redistributions of source code must retain the above copyright notice, this
 *         list of conditions and the following disclaimer.
 *     Redistributions in binary form must reproduce the above copyright notice,
 *         this list of conditions and the following disclaimer in the documentation
 *         and/or other materials provided with the distribution.
 *     Neither the name of Takahiro Noguchi nor the names of its
 *         contributors may be used to endorse or promote products derived from this
 *         software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if (!class_exists("Custom_Sketchbook_Plugin")) {

    class Custom_Sketchbook_Plugin {

        /** Absolute path to the work sketchbook directory.
         * @var string
         */

        protected static $dir = '';
    
        /** URL to the work sketchbook directory.
         * @var string
         */
        protected static $url = '';


        public function __construct() {

            self::$dir    = plugin_dir_path( __FILE__ );
            self::$url    = plugin_dir_url( __FILE__ );
            add_action( 'init', array($this, 'create_work_taxonomies'), 0 );
            add_action( 'init', array($this, 'create_tag_taxonomies'), 0 );
            add_action( 'init', array($this, 'create_sketchbook_work' ));
            add_action( 'init', array($this, 'register_fields' ));
            add_action( 'init', array($this, 'add_large_sketch' ));
            add_action( 'do_meta_boxes', array($this, 'image_box' ));
            add_action( 'admin_head-post-new.php', array($this, 'add_featured_image_filter' ));
            add_action( 'admin_head-post.php', array($this, 'add_featured_image_filter' ));
            $this->tn_cstmdisp_admin_init();
            add_filter( 'manage_edit-tn_cstm_sketchbook_sortable_columns', array($this, 'sort_me' ));
            add_filter( 'request', array($this, 'column_orderby' ));
            add_action( 'restrict_manage_posts', array($this, 'filter_list' ));
            add_filter( 'parse_query', array($this, 'perform_filtering' ));
            add_action( 'wp_enqueue_scripts', array($this, 'include_scripts' ));
            //add_action( 'acf/save_post', array($this, 'acf_create_post_content' ));
            add_action( 'wp_ajax_load-content', array($this, 'load_ajax_content' ));
            add_action( 'wp_ajax_nopriv_load-content', array($this, 'load_ajax_content' ));
            add_action( 'wp_footer', array($this, 'custom_sketchbook_js'),100 );
        }


        // Register Custom Post Taxonomy
         public  function create_work_taxonomies() {
                register_taxonomy(
                    'tn_cstm_sketchbook_plugin',
                    'tn_cstm_sketchbook',
                    array(
                        'labels' => array(
                            'name' => 'Sketchbook Categories',
                            'add_new_item' => 'Add New Sketchbook Category',
                            'new_item_name' => "New Sketchbook Category"
                        ),
                        'show_ui' => true,
                        'show_in_nav_menus' => true,
                        'show_admin_column' => true, //Show custom taxonomy in admin columns
                        'show_tagcloud' => false,
                        'hierarchical' => true,
                        'args' => array( 'orderby' => 'term_order' ),
                        'rewrite' => array('slug' => 'sketchbook-categories', 'with_front' => false )
                    )
                );
            }

        
            public function create_tag_taxonomies() {
              // Add new taxonomy, NOT hierarchical (like tags)
              $labels = array(
                'name' => _x( 'Sketchbook Tags', 'taxonomy general name' ),
                'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Tags' ),
                'popular_items' => __( 'Popular Tags' ),
                'all_items' => __( 'All Sketchbook Tags' ),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __( 'Edit Sketchbook Tag' ), 
                'update_item' => __( 'Update Sketchbook Tag' ),
                'add_new_item' => __( 'Add New Sketchbook Tag' ),
                'new_item_name' => __( 'New Sketchbook Tag Name' ),
                'separate_items_with_commas' => __( 'Separate sketchbook tags with commas' ),
                'add_or_remove_items' => __( 'Add or remove sketchbook tags' ),
                'choose_from_most_used' => __( 'Choose from the most used sketchbook tags' ),
                'menu_name' => __( 'Tags' ),
              ); 

              register_taxonomy('tag','tn_cstm_sketchbook',array(
                'hierarchical' => false,
                'labels' => $labels,
                'show_ui' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array( 'slug' => 'tag' ),
              ));
            }



        // Register Custom Post Type
           public function create_sketchbook_work() {
                register_post_type( 'tn_cstm_sketchbook',
                    array(
                        'labels' => array(
                            'name' => 'Sketchbook',
                            'singular_name' => 'Sketchbook Page',
                            'add_new' => 'Add New Sketchbook Page',
                            'add_new_item' => 'Add New Sketchbook Page',
                            'edit' => 'Edit Sketchbook Page',
                            'edit_item' => 'Edit Sketchbook Page',
                            'new_item' => 'New Sketchbook Page',
                            'view' => 'View Sketchbook Page',
                            'view_item' => 'View Sketchbook Pages',
                            'search_items' => 'Search Sketchbook Pages',
                            'not_found' => 'No Sketchbook Pages found',
                            'not_found_in_trash' => 'No Sketchbook Pages Found in Trash',
                            'parent' => 'Parent Sketchbook Page'
                        ),
             
                        'public' => true,
                        'show_in_menu' => true,
                        'menu_position' => 29,
                        'taxonomies' => array( '' ),
                        'menu_icon' => plugins_url( 'images/sketchbook-icon.png', __FILE__ ),
                        'publicly_queryable' => true,
                        'query_var' => true,
                        'rewrite' => array( 'slug' => 'sketchbook', 'with_front' => false ),  
                        'has_archive' => true,
                        'hierarchical' => false,
                        'supports' => array( 'title', 'page-attributes' )
                    )
                );
            }

            public function register_fields() { 

                /**
                 *  Register Field Groups 
                 *  Using ACF register_field_group
                 *  The register_field_group function accepts 1 array which holds the relevant data to register a field group
                 *  You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
                 */

                if(function_exists("register_field_group"))
                {
                   
                }
            }


            // Add large sketch size for modal popups
            public function add_large_sketch() {

                if ( function_exists( 'add_theme_support' ) ) {
                add_image_size( 'medium-sketch', 450, 550 ); //800 pixels wide (and unlimited height)
                add_image_size( 'large-sketch', 800, 9999 ); //800 pixels wide (and unlimited height)

                }
            }


            // Add sketch using existing featured image metabox; remove default featured image box.
            public function image_box() {
                remove_meta_box( 'postimagediv', 'tn_cstm_sketchbook', 'side' );
                add_meta_box('postimagediv', __('Sketch'), 'post_thumbnail_meta_box', 'tn_cstm_sketchbook', 'normal', 'high');
            }

            // Conditionally filter featured image text only for sketchbook cpt
            public function add_featured_image_filter() {

                if ('tn_cstm_sketchbook' == get_post_type() ) {

                    add_filter('admin_post_thumbnail_html', array($this, 'do_thumb' ));
                    add_filter( 'admin_post_thumbnail_html', array($this, 'remove_featured'), 9999, 1 );

                }
            }


            public function remove_featured( $content ) {
                return str_replace( 'Remove featured image', 'Remove sketch', $content );
            }

            public function do_thumb($content){
                 return str_replace(__('Set featured image'), __('Add a sketch'), $content);
            }



          // Add custom columns to CPT admin dashboard with filter manage_$post_type_posts_columns
            public function tn_cstmdisp_admin_init() {
                add_filter( 'manage_tn_cstm_sketchbook_posts_columns', array($this, 'custom_columns' ));
                add_action( 'manage_tn_cstm_sketchbook_posts_custom_column', array($this, 'columns_content'), 5, 2 );
            }


            /* Admin Dashboard Customizations */

            public function custom_columns( $columns ) {
                $columns['tn_cstm_sketchbook_image'] = 'Cover Image';
                $columns['tn_cstm_sketchbook_featured'] = 'Featured';
                return $columns;
            }

            // Display image in edit column

            // Display image in edit column
            public  function columns_content($column_name, $post_ID) {
                $post_id = null;
                if ($column_name == 'tn_cstm_sketchbook_image') {
                    $img_id = get_post_thumbnail_id();
                    $image = wp_get_attachment_image_src($img_id, 'thumbnail');
                    $alt = get_post_meta($img_id , '_wp_attachment_image_alt', true);
             
                    if ($image) { ?>            
                        <img src="<?php echo $image[0]; ?>" alt="<?php echo $alt ?>" />
                        <?php 
                    }
                }

                if ($column_name == 'tn_cstm_sketchbook_featured') {

                    if( get_field( 'featured_work' )) {
                        the_field( 'featured_work'); 
                    }
                }
            }

        

            // Sortable CPT columns in admin
            public function sort_me( $columns ) {
                $columns['tn_cstm_sketchbook_featured'] = 'tn_cstm_sketchbook_featured';
                
                return $columns;
            }


            // Add custom fields to query array
            public function column_orderby ( $vars ) {
                if ( !is_admin() )
                    return $vars;

                if ( isset( $vars['orderby'] ) && 'tn_cstm_sketchbook_featured' == $vars['orderby'] ) {
                    $vars = array_merge( $vars, array( 'meta_key' => 'featured_work', 'orderby' => 'meta_value' ) );
                }
               
                return $vars;
            }


            // Filter CPT by custom taxonomy
            public function filter_list() {
                $screen = get_current_screen();
                global $wp_query;
                if ( $screen->post_type == 'tn_cstm_sketchbook' ) {
                    wp_dropdown_categories( array(
                        'show_option_all' => 'Show All Sketchbook Categories',
                        'taxonomy' => 'tn_cstm_sketchbook_plugin',
                        'name' => 'tn_cstm_sketchbook_plugin',
                        'orderby' => 'name',
                        'selected' => ( isset( $wp_query->query['tn_cstm_sketchbook_plugin'] ) ? $wp_query->query['tn_cstm_sketchbook_plugin'] : '' ),
                        'hierarchical' => false,
                        'depth' => 3,
                        'show_count' => false,
                        'hide_empty' => true,
                    ) );
                }
            }

            // Display filtered results
            public function perform_filtering( $query ) {
                $qv = &$query->query_vars;
                if (isset( $qv['tn_cstm_sketchbook_plugin'] ) && is_numeric( $qv['tn_cstm_sketchbook_plugin'] ) ) {
                    $term = get_term_by( 'id', $qv['tn_cstm_sketchbook_plugin'], 'tn_cstm_sketchbook_plugin' );
                    $qv['tn_cstm_sketchbook_plugin'] = $term->slug;
                }
            }



           public function include_scripts() {

                if ( is_page('sketchbook-pages' ) ) {

                    // embed the javascript file to make the AJAX request
                    wp_register_script( 'reveal', get_template_directory_uri() . '/js/foundation/foundation.reveal.js', array( 'jquery', 'reverie-js' ), '', true );
                    wp_enqueue_script( 'reveal' );

                    wp_register_script( 'infinite-scroll', self::$url . 'js/jquery.infinitescroll.min.js', array( 'jquery' ), '', true );
                    wp_enqueue_script( 'infinite-scroll' );

                    wp_register_script( 'my-ajax-request', self::$url . 'js/sketchbook_ajax.js', array( 'jquery', 'reverie-js', 'reveal' ), '', true );
                    wp_enqueue_script( 'my-ajax-request' );

                    wp_localize_script( 'my-ajax-request', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

                }
            }

        
            

            private function paging_link_nav( $post_id ) {

                global $post;

                $post = get_post( $post_id );

                return '<nav><ul><li class="next"><a href="#" class="button radius next-sketch secondary" data-id="'. next_post_link_plus( array('loop' => 'true', 'return' => 'id') ) .'">Next</a></li><li class="prev"><a href="#" class="button radius previous-sketch secondary" data-id="'. previous_post_link_plus( array('loop' => 'true', 'return' => 'id') ) .'">Previous</a></li>
                </ul></nav>'; 
            
            }



            /**
             * Function to call the content loaded for logged-in and anonymous users
            */
            public function load_ajax_content ( $post_id ) {
                 
                $post_id = $_POST[ 'post_id' ];
                
                if (has_post_thumbnail($post_id)) {
                    $sketch_id = get_post_thumbnail_id($post_id);  
                    $attachment = get_post( $sketch_id );
                    $caption = $attachment->post_excerpt;
                    $response = '<figure>'. get_the_post_thumbnail($post_id, 'large-sketch') .'<figcaption><p>'. $caption .'</p></figcaption></figure>' . $this->paging_link_nav( $post_id );
                    echo $response;
                }

                die(1);
             }


             /**
             * Infinite Scroll
             */
            public function custom_sketchbook_js() {
                if( is_page('sketchbook-pages' )) { ?>
                <script type="text/javascript">
                //<![CDATA[
                var infinite_scroll = {
                    loading: {
                        img: "<?php echo get_stylesheet_directory_uri(); ?>/img/ajax-loader.gif",
                        msgText: "<?php _e( ' ', 'custom' ); ?>",
                        finishedMsg: "<?php _e( ' ', 'custom' ); ?>"
                    },
                    "nextSelector":"#sketchbook-nav .next-leaf a",
                    "navSelector":"#sketchbook-nav",
                    "itemSelector":".sketch-leaf",
                    "contentSelector":"#sketchbook-container"
                };
                jQuery( infinite_scroll.contentSelector ).infinitescroll( infinite_scroll );

                (function(d){
                  var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
                  p.type = 'text/javascript';
                  p.setAttribute('data-pin-hover', true);
                  p.async = true;
                  p.src = '//assets.pinterest.com/js/pinit.js';
                  f.parentNode.insertBefore(p, f);
                }(document));

                //]]>
                </script>
                <?php
                }
            }
           


    }// end class Custom_Sketchbook_Plugin

} // end if (!class_exists('Custom_Sketchbook_Plugin'))




//Template display tags

function tn_cstm_sketchbook_menu() {
    $CustomSketchbook = new Custom_Sketchbook_Plugin();
    $CustomSketchbook->sketchbook_menu();

}


$CustomSketchbook = new Custom_Sketchbook_Plugin();


?>
