<?php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});
add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary' => 'Menu principal ALC'
    ]);
});
add_action('init', function() {
    register_block_pattern('alc/hero-alc', [
        'title'       => 'Hero ALC',
        'description' => 'Un bloc hero avec titre, texte et bouton',
        'content'     => '
            <!-- wp:cover {"url":"https://via.placeholder.com/1200x400","dimRatio":50,"overlayColor":"black","minHeight":400,"align":"full"} -->
            <div class="wp-block-cover alignfull" style="min-height:400px">
                <span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim"></span>
                <div class="wp-block-cover__inner-container">
                    <!-- wp:heading {"textAlign":"center","level":1} --> 
                    <h1 class="has-text-align-center">Bienvenue sur ALC</h1>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph {"align":"center"} -->
                    <p class="has-text-align-center">Explorez le meilleur du cinéma africain</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                    <div class="wp-block-buttons">
                        <div class="wp-block-button"><a class="wp-block-button__link" href="#">Découvrir</a></div>
                    </div>
                    <!-- /wp:buttons -->
                </div>
            </div>
            <!-- /wp:cover -->
        ',
        'categories'  => ['hero', 'featured'],
    ]);
});
function alc_register_testimonial_cpt() {
    register_post_type('testimonial', [
        'label' => 'Témoignages',
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-format-quote',
        'show_in_rest' => true,
    ]);
}
add_action('init', 'alc_register_testimonial_cpt');

function alc_register_testimonial_meta() {
    register_post_meta('testimonial', 'rating', [
        'type' => 'integer',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'absint',
        'auth_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('init', 'alc_register_testimonial_meta');

function alc_add_rating_meta_box() {
    add_meta_box('alc_rating', 'Note du client (1 à 5)', 'alc_rating_meta_box_callback', 'testimonial');
}
add_action('add_meta_boxes', 'alc_add_rating_meta_box');

function alc_rating_meta_box_callback($post) {
    $rating = get_post_meta($post->ID, 'rating', true);
    echo '<input type="number" name="alc_rating" value="' . esc_attr($rating) . '" min="1" max="5" />';
}

function alc_save_rating_meta($post_id) {
    if (isset($_POST['alc_rating'])) {
        update_post_meta($post_id, 'rating', absint($_POST['alc_rating']));
    }
}
add_action('save_post', 'alc_save_rating_meta');


function alc_testimonials_shortcode() {
    ob_start();
    ?>
    <div id="alc-testimonials">
        <?php alc_render_testimonials(6); ?>
    </div>
    <button id="load-more-testimonials">Charger plus</button>
    <?php
    return ob_get_clean();
}
add_shortcode('alc_testimonials', 'alc_testimonials_shortcode');

function alc_render_testimonials($count = 6) {
    $query = new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => $count,
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $rating = get_post_meta(get_the_ID(), 'rating', true);
        echo '<div class="testimonial">';
        echo '<h3>' . get_the_title() . '</h3>';
        echo '<p>' . get_the_content() . '</p>';
        echo '<p>Note : ' . str_repeat('⭐', intval($rating)) . '</p>';
        echo '</div>';
    }
    wp_reset_postdata();
}
function alc_enqueue_testimonials_script() {
    wp_enqueue_script(
        'alc-testimonials',
        get_stylesheet_directory_uri() . '/js/testimonials.js',
        [],
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'alc_enqueue_testimonials_script');

function alc_ajax_load_more_testimonials() {
    $offset = isset($_GET['offset']) ? absint($_GET['offset']) : 0;

    $query = new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => 6,
        'offset' => $offset,
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $rating = get_post_meta(get_the_ID(), 'rating', true);
        echo '<div class="testimonial">';
        echo '<h3>' . get_the_title() . '</h3>';
        echo '<p>' . get_the_content() . '</p>';
        echo '<p>Note : ' . str_repeat('⭐', intval($rating)) . '</p>';
        echo '</div>';
    }
    wp_die();
}
add_action('wp_ajax_load_more_testimonials', 'alc_ajax_load_more_testimonials');
add_action('wp_ajax_nopriv_load_more_testimonials', 'alc_ajax_load_more_testimonials');

add_action('rest_api_init', function () {
    register_rest_route('alc/v1', '/testimonials', [
        'methods' => 'GET',
        'callback' => 'alc_get_testimonials',
        'permission_callback' => '__return_true',
        'args' => [
            'page' => [
                'default' => 1,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'default' => 6,
                'sanitize_callback' => 'absint',
            ],
        ],
    ]);
});

function alc_get_testimonials($request) {
    $page = $request['page'];
    $per_page = $request['per_page'];

    $query = new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => $per_page,
        'paged' => $page,
    ]);

    $results = [];

    foreach ($query->posts as $post) {
        $results[] = [
            'id' => $post->ID,
            'title' => get_the_title($post),
            'content' => apply_filters('the_content', $post->post_content),
            'rating' => get_post_meta($post->ID, 'rating', true),
        ];
    }

    return rest_ensure_response($results);
}

add_action('admin_menu', function () {
    add_options_page(
        'Réglages ALC',
        'ALC',
        'manage_options',
        'alc-settings',
        'alc_settings_page'
    );
});

function alc_settings_page() {
    ?>
    <div class="wrap">
        <h1>Réglages ALC</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('alc_settings_group');
            do_settings_sections('alc-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {
    register_setting('alc_settings_group', 'alc_slogan');

    add_settings_section('alc_main_section', 'Slogan du site', null, 'alc-settings');

    add_settings_field('alc_slogan_field', 'Slogan', function () {
        $value = get_option('alc_slogan', '');
        echo '<input type="text" name="alc_slogan" value="' . esc_attr($value) . '" class="regular-text">';
    }, 'alc-settings', 'alc_main_section');
});

add_action('init', function () {
    register_post_type('testimonial', [
        'labels' => [
            'name' => 'Témoignages',
            'singular_name' => 'Témoignage',
            'add_new_item' => 'Ajouter un témoignage',
            'edit_item' => 'Modifier le témoignage',
            'new_item' => 'Nouveau témoignage',
            'view_item' => 'Voir le témoignage',
            'search_items' => 'Rechercher des témoignages',
        ],
        'public' => true,
        'has_archive' => false,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-testimonial',
        'supports' => ['title', 'editor', 'custom-fields'],
        'show_in_rest' => true,
    ]);
});
?>