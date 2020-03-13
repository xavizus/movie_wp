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

 /**
  * Todo:
  * Add settings Page: https://codex.wordpress.org/Creating_Options_Pages
  */
if (!class_exists('test\MovieRajtingPlugin')) {
    class MovieRajtingPlugin
    {

        /**
         * Used for set max / min values for the rating.
         */
        private $ratingRange = array(
            'max' => 5,
            'min' => 1
        );

        /**
         * Default metaDataFields
         */
        private $metaDataFields = array(
            "_movies_imdb" => "movie_imdb_id",
            "_movies_released" => "movie_released",
            "_movies_actors" => "movie_actors",
            "_movies_poster" => "movie_poster"
        );

        public function __construct()
        {
            add_filter('pre_get_posts', array($this, 'changeHomeDefaultPostType'));
            add_action('the_post', array($this, 'loadScriptsAndStyles'));
            add_action('init', array($this, 'myCptInit'));
            register_activation_hook(__FILE__, array($this, 'myRewriteFlush'));
            register_deactivation_hook(__FILE__, array($this, 'unloadCpt'));
            add_action('add_meta_boxes', array($this, 'addCustomMetaBox'));
            add_action('rest_api_init', array($this, 'loadCustomApi'));
            add_action('save_post_movies', array($this, 'save_meta_data'), 10, 2);
            add_action('wp_insert_post_data', array($this, 'my_save_post'));
        }

        /**
         * called by hook wp_insert_post_data.
         */
        public function my_save_post($post)
        {
            // run only for post_type movies
            if ($post['post_type'] == 'movies') {
                // Controll if user used autofill button.
                if ($_POST['movie_autofilled'] == '0' && !empty($_POST['movie_imdb_id'])) {
                    $data = $this->fetchIMDBData(sanitize_text_field($_POST['movie_imdb_id']));
                    if ($data->Response == 'False') {
                        // Gutenberg api sucks... Can't find any information on how to send an error to Gutenberg, to let it know something went wrong...
                        return new \WP_Error('rest_invalid_param', 'E-Mail not found in directory', array( 'status' => 400));
                    }
                    $_POST['movie_released'] = $data->Released;
                    $_POST['movie_actors'] = $data->Actors;
                    $_POST['movie_poster'] = $data->Poster;
                    $post['post_content'] = "<!-- wp:paragraph --><p>" . sanitize_text_field($data->Plot) . "</p><!-- /wp:paragraph -->";
                    $post['post_title'] = sanitize_text_field($data->Title);
                }
            }
            return $post;
        }

        
        /**
         * called by hook save_post_movies
         */
        public function save_meta_data($post_ID, $post)
        {
            // Check if user posted a movie_poster, and make sure that the post not already got a poster.
            if (isset($_POST['movie_poster']) && !empty($_POST['movie_poster'] && !has_post_thumbnail($post_ID))) {
                $attachmentData = $this->uploadImageURLToMedia($_POST['movie_poster']);
                $this->assignFeatureImageToPost($attachmentData, $post_ID);
            }

            /**
             * Loop through meta_keys and update them.
             */
            foreach ($this->metaDataFields as $key => $field) {
                if (array_key_exists($field, $_POST)) {
                    update_post_meta(
                        $post_ID,
                        sanitize_text_field($key),
                        $_POST[$field]
                    );
                }
            }
            return $post;
        }

        /**
         * https://www.wpexplorer.com/wordpress-featured-image-url/
         * Upload an url-image to WP media.
         */
        private function uploadImageURLToMedia($url)
        {
            $imageName = basename($url);
            $imageData = file_get_contents($url);
            $uploadDir = wp_upload_dir();
            $uqFilename = wp_unique_filename($uploadDir['path'], $imageName);
            $newImageName = basename($uqFilename);

            $file = $uploadDir['path'] . '/' . $newImageName;
            file_put_contents($file, $imageData);
            $wp_filetype = wp_check_filetype($newImageName, null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name($newImageName),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            return array(
                "attachment" => $attachment,
                "file" => $file
            );
        }

        /**
         * Sets the media image as a featured image.
         */
        private function assignFeatureImageToPost(array $attachmentData, $post_id)
        {
            $attach_id = wp_insert_attachment($attachmentData['attachment'], $attachmentData['file'], $post_id);

            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attach_data = wp_generate_attachment_metadata($attach_id, $attachmentData['file']);

            wp_update_attachment_metadata($attach_id, $attach_data);

            set_post_thumbnail($post_id, $attach_id);
        }

        /**
         * Called by rest_api_init hook.
         */
        public function loadCustomApi()
        {

            register_rest_route('myAwesomePlugin/v1', '/movies/(?P<imdbid>[a-zA-Z0-9]+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'fetchIMDBData')
              ));

              register_rest_route('myAwesomePlugin/v1', '/setRating/post_id=(?P<post_id>[0-9]+)/rating=(?P<rating>[0-9])', array(
                'methods' => 'GET',
                'callback' => array($this, 'setRatingToPost')
              ));
        }

        /**
         * Sets rating to post (If it's successfully inserted)
         */
        public function setRatingToPost($request)
        {
            /**
             * Store the request data in a data array.
             */
            $data = [];
            $data['post_id'] = $request['post_id'];
            $data['rating'] =  $request['rating'];
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['Response'] = "True";

            try {
                //get post data.
                $the_post = get_post($data['post_id']);

                //If the post data is empty or the post is not published
                if (!$the_post || $the_post->post_status != 'publish') {
                    throw new \Exception("Post with id: $data[post_id] does not exist or is not published!");
                }

                // check if the rating is within the limit.
                if (!$this->isRatingWithinRange($data['rating'], $this->ratingRange['max'], $this->ratingRange['min'])) {
                    throw new \Exception("Rating not within range. Submitted rating: $data[rating], allwoed range: {$this->ratingRange['min']} - {$this->ratingRange['max']}");
                }

                // retrive metadata of the post
                $currentMetaData = get_post_meta($data['post_id'], '_movies_ratings');

                // Check if we got metadata (otherwise it's an empty array)
                if ($currentMetaData) {
                    // decode the metaData, and make it all arrays instead of array of objects.
                    $currentMetaData = json_decode($currentMetaData[0], true);

                    // check if the ip-adress have rated before (Not the best solution, but hey :D )
                    $data['key'] = $this->searchForIp($data['ip'], $currentMetaData);
                }

                // If the ip-adress have rated before, check if the new rating is the same as the old rating.
                if ((isset($data['key']) && $data['key'] !== false) && ($currentMetaData[$data['key']]['score'] == $data['rating'])) {
                    throw new \Exception('You cannot change rating to the same rating!');
                }

                // if there have not been any ratings on the post, or the IP-adress have not rated the post before
                if (empty($currentMetaData) || $data['key'] === false) {
                    $currentMetaData[] = array(
                        "ip" => $data['ip'],
                        'score' => $data['rating']
                    );
                } else {
                    // if the ip-adress have rated before, update that rating.
                    $currentMetaData[$data['key']]['score'] = $data['rating'];
                }
                // Update post meta
                $status = update_post_meta($data['post_id'], '_movies_ratings', json_encode($currentMetaData));

                // if the update went wrong.
                if (!$status) {
                    throw new \Exception("Could not update or create post meta! $status");
                }
            } catch (\Exception $error) {
                //Clear all info we already had.
                $data = [];
                $data['Response'] = "False";
                $data['Error'] = $error->getMessage();
            }
            // return response.
            return $data;
        }


        /**
         * Custom array search for function setRatingToPost.
         */
        private function searchForIp($needle, $array)
        {
            $key = array_search($needle, array_column($array, 'ip'));
            return $key;
        }

        /**
         * check if value is within range, for setRatingToPost
         */
        private function isRatingWithinRange($rating, $max, $min)
        {
            return ($rating >= $min && $rating <= $max) ? true : false;
        }

        /**
         * Get data from omdbapi.
         */
        public function fetchIMDBData($request)
        {
            $IMDBID = is_object($request) ? $request['imdbid'] : $request;

            #$data = json_decode(file_get_contents(sprintf("https://www.omdbapi.com/?i=%s&apikey=%s", $IMDBID, OMDBAPIKEY)), true);
            if ($IMDBID == 'tt3896198') {
                $data = json_decode(file_get_contents(plugin_dir_url(__FILE__) . 'testdata.json', true));
            } else {
                $data = json_decode(file_get_contents(plugin_dir_url(__FILE__) . 'faileddata.json', true));
            }
            return $data;
        }

        /**
         * Loads admin view scripts and css for movies type.
         */
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

        /**
         * Injects javascript file with Gutenberg Api (some part of it).
         */
        public function myplugin_enqueue_block_editor_assets()
        {
            wp_enqueue_script(
                'myplugin-block',
                plugins_url('js/main.js', __FILE__),
                array( 'wp-blocks', 'wp-element', 'wp-hooks' )
            );
        }

        /**
         * Register custom post type
         */
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

        /**
         * When plugin is activated, and wp is used another type of permalinks.
         */
        public function myRewriteFlush()
        {
            $this->myCptInit();
            flush_rewrite_rules();
        }

        /**
         * Remove Custom post type on deactivate plugin.
         */
        public function unloadCpt()
        {
            $this->removeCustomMetaBox();
            unregister_post_type('movies');
        }

        /**
         * Adds meta-box to custom post type.
         */
        public function addCustomMetaBox()
        {
            add_meta_box(
                'myAwesomePluginMovieBox',
                'Movie Data',
                array($this, 'addCustomMetaBoxHtml'),
                'movies'
            );
        }
        /**
         * Remove metabox when plugin is deactivated
         */
        public function removeCustomMetaBox()
        {
            remove_meta_box('myAwesomePluginMovieBox', 'movies', 'advanced');
        }

        /**
         * Custom meta box html code.
         */
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

            <div class="form-group row">
                <label for="movie_poster" class="col-sm-2 col-form-label">Poster URL</label>
                <div class="col">
                    <input name="movie_poster" id="movie_poster" class="form-control" value="<?=$_movies_poster?>">
                </div>
            </div>

            <?php
        }

        /**
         * Change default post type for main query
         */
        public function changeHomeDefaultPostType($query)
        {
            if (!is_admin() &&  $query->is_main_query() && $query->is_front_page()) {
                $query->set('post_type', 'movies');
                $query->set('post_status', 'publish');
            }
        }
    }
}
$obj = new MovieRajtingPlugin();