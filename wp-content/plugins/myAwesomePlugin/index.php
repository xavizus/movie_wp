<?php

namespace test;

/**
 *
 * @since             0.0.1
 * @package           myAwesomePlugin
 *
 * @wordpress-plugin
 * Plugin Name:       myAwesomePlugin
 * Description:       This plugin adds movie rating functions
 * Version:           0.0.1
 * Author:            Stephan Ljungros
 */

if (!class_exists('test\MovieRajtingPlugin')) {
    class MovieRajtingPlugin
    {

        private $metaDataFields = array(
            "_movies_imdb" => "movie_imdb_id",
            "_movies_released" => "movie_released",
            "_movies_actors" => "movie_actors"
        );

        public function __construct()
        {
            add_action('the_post', array($this, 'loadScriptsAndStyles'));
            add_action('init', array($this, 'myCptInit'));
            register_activation_hook(__FILE__, array($this, 'myRewriteFlush'));
            register_deactivation_hook(__FILE__, array($this, 'unloadCpt'));
            add_action('add_meta_boxes', array($this, 'addCustomMetaBox'));
            add_action('rest_api_init', array($this, 'loadCustomApi'));
            add_action('save_post_movies', array($this, 'save_meta_data'), 10, 2);
            add_action('wp_insert_post_data', array($this, 'my_save_post'));
        }

        public function my_save_post($post)
        {
            error_log(print_r($post,true));
            if ($post['post_type'] == 'movies') {
                if ($_POST['movie_autofilled'] == '0' && !empty($_POST['movie_imdb_id'])) {
                    $data = $this->fetchIMDBData(sanitize_text_field($_POST['movie_imdb_id']));
                    if ($data->Response == 'False') {
                        // Gutenberg api sucks... Can't find any information on how to send an error to Gutenberg, to let it know something went wrong...
                        return new \WP_Error('rest_invalid_param', 'E-Mail not found in directory', array( 'status' => 400));
                    }
                    $_POST['movie_released'] = $data->Released;
                    $_POST['movie_actors'] = $data->Actors;
                    $post['post_content'] = "<!-- wp:paragraph --><p>" . sanitize_text_field($data->Plot) . "</p><!-- /wp:paragraph -->" . '<figure class="wp-block-image"><img src="' . sanitize_text_field($data->Poster) . '" alt=""/></figure>';
                    $post['post_title'] = sanitize_text_field($data->Title);
                }
            }
            return $post;
        }

        
        // Works, but need to reload the editor to see the changes.
        public function save_meta_data($post_ID, $post)
        {
            foreach ($this->metaDataFields as $key => $field) {
                if (array_key_exists($field, $_POST) && !empty($_POST[$field])) {
                    update_post_meta(
                        $post_ID,
                        sanitize_text_field($key),
                        $_POST[$field]
                    );
                }
            }
            return $post;
        }

        public function loadCustomApi()
        {
            register_rest_route('myAwesomePlugin/v1', '/movies/(?P<imdbid>[a-zA-Z0-9]+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'fetchIMDBData')
              ));
        }

        public function fetchIMDBData($request)
        {
            $IMDBID = is_object($request) ? $request['imdbid'] : $request;

            #$data = json_decode(file_get_contents("https://www.omdbapi.com/?i=$IMDBID=ec2a3250"), true);
            if ($IMDBID == 'tt3896198') {
                $data = json_decode(file_get_contents(plugin_dir_url(__FILE__) . 'testdata.json', true));
            } else {
                $data = json_decode(file_get_contents(plugin_dir_url(__FILE__) . 'faileddata.json', true));
            }
            return $data;
        }

        public function loadScriptsAndStyles($post_id)
        {
            if (get_post_type($post_id) == 'movies') {
                wp_enqueue_style('movies-style', plugin_dir_url(__FILE__) . "/css/style.css", array(), '1.0');
                wp_enqueue_style('movies-bootstrap', plugin_dir_url(__FILE__) . "/css/bootstrap.min.css", array(), '1.0');
                wp_enqueue_script('movies-latest-jquery', plugin_dir_url(__FILE__) . '/js/jquery.js');
                #wp_enqueue_script('movies-main-script', plugin_dir_url(__FILE__) . '/js/main.js', array('movies-latest-jquery'));
                add_action('enqueue_block_editor_assets', array($this, 'myplugin_enqueue_block_editor_assets'));
            }
        }

        public function myplugin_enqueue_block_editor_assets()
        {
            wp_enqueue_script(
                'myplugin-block',
                plugins_url('js/main.js', __FILE__),
                array( 'wp-blocks', 'wp-element' )
            );
        }

        public function myCptInit()
        {
 
            // Set UI labels for Custom Post Type
            $labels = array(
            'name'                => _x('Movies', 'Post Type General Name', 'myAwesomeTheme'),
            'singular_name'       => _x('Movie', 'Post Type Singular Name', 'myAwesomeTheme'),
            'menu_name'           => __('Movies', 'myAwesomeTheme'),
            'parent_item_colon'   => __('Parent Movie', 'myAwesomeTheme'),
            'all_items'           => __('All Movies', 'myAwesomeTheme'),
            'view_item'           => __('View Movie', 'myAwesomeTheme'),
            'add_new_item'        => __('Add New Movie', 'myAwesomeTheme'),
            'add_new'             => __('Add New', 'myAwesomeTheme'),
            'edit_item'           => __('Edit Movie', 'myAwesomeTheme'),
            'update_item'         => __('Update Movie', 'myAwesomeTheme'),
            'search_items'        => __('Search Movie', 'myAwesomeTheme'),
            'not_found'           => __('Not Found', 'myAwesomeTheme'),
            'not_found_in_trash'  => __('Not found in Trash', 'myAwesomeTheme'),
            );
         
            // Set other options for Custom Post Type
         
            $args = array(
            'label'               => __('movies', 'myAwesomeTheme'),
            'description'         => __('Movie news and reviews'),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            'supports'            => array(
                'title',
                'editor',
                'excerpt',
                'author',
                'thumbnail',
                'revisions',
                'custom-fields'
            ),
            // You can associate this CPT with a taxonomy or custom taxonomy.
            'taxonomies'          => array( 'genres' ),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest' => true,
     
            );
         
            // Registering your Custom Post Type
            register_post_type('movies', $args);
        }

        public function myRewriteFlush()
        {
            $this->myCptInit();

            flush_rewrite_rules();
        }

        public function unloadCpt()
        {
            $this->removeCustomMetaBox();
            unregister_post_type('movies');
        }

        public function addCustomMetaBox()
        {
            add_meta_box(
                'myAwesomePluginMovieBox',
                'Movie Data',
                array($this, 'addCustomMetaBoxHtml'),
                'movies'
            );
        }

        public function removeCustomMetaBox()
        {
            remove_meta_box('myAwesomePluginMovieBox', 'movies', 'advanced');
        }

        public function addCustomMetaBoxHtml($post)
        {

            foreach ($this->metaDataFields as $arrayKey => $field) {
                $key = $arrayKey;
                $$key = get_post_meta($post->ID, $arrayKey, true);
            }
            ?>
            <input type=hidden id="movie_autofilled" name="movie_autofilled" value="0">
            <div class="form-group row">
                <label for="movie_imdb_id" class="col-sm-2 col-form-label">IMDb-ID</label>
                <div class="col">
                    <input type="text" name="movie_imdb_id" id="movie_imdb_id" class="form-control" value="<?=$_movies_imdb?>">
                    <div class="invalid-feedback">
                        Not a vaild IMDb-ID
                    </div>
                </div>
                <div class="col-2">
                    <input id="movie_autoFill" class="btn btn-primary" type="button" value="Auto Fill"> 
                </div>
            </div>

            <div class="form-group row">
                <label for="movie_released" class="col-sm-2 col-form-label">Released</label>
                <div class="col">
                    <input name="movie_released" id="movie_released" class="form-control" value="<?=$_movies_released?>">
                </div>
            </div>

            <div class="form-group row">
                <label for="movie_actors" class="col-sm-2 col-form-label">Actors</label>
                <div class="col">
                    <input name="movie_actors" id="movie_actors" class="form-control" value="<?=$_movies_actors?>">
                </div>
            </div>

            <?php
        }
    }
}
$obj = new MovieRajtingPlugin();