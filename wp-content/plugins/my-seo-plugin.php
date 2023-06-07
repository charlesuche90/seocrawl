<?php
/*
Plugin Name: My SEO Plugin
Version: 1.0.0
Author: Charles Uche
Description: A WordPress plugin for improving SEO rankings.
*/

// Register the admin menu page
function my_seo_plugin_menu_page() {
    add_menu_page(
        'SEO Crawl',                     // Page title
        'SEO Crawl',                     // Menu title
        'manage_options',                // Capability required to access the menu
        'my-seo-plugin',                 // Menu slug
        'my_seo_plugin_settings_page',   // Callback function to display the menu page content
        'dashicons-admin-generic',        // Menu icon (optional)
        20                               // Position in the menu
    );
}
add_action('admin_menu', 'my_seo_plugin_menu_page');

// Callback function to display the settings page
function my_seo_plugin_settings_page() {
    if (isset($_POST['crawl_trigger'])) {
        echo '<div class="updated"><p>Crawl triggered successfully!</p></div>';
        run_seo_crawl();
        schedule_seo_crawl();
    }

    if (isset($_GET['view_results'])) {
        display_crawl_results();
    }

    ?>
    <div class="wrap">
        <h1>SEO Crawl Settings</h1>
        <p>This is the settings page for the SEO Crawl plugin.</p>

        <h2>Trigger Crawl</h2>
        <form method="post" action="">
            <p>
                <input type="hidden" name="crawl_trigger" value="1">
                <input type="submit" class="button-primary" value="Start Crawl">
            </p>
        </form>

        <h2>Sitemap HTML</h2>
        <?php
        // Display the sitemap.html page
        $sitemap_url = get_site_url() . '/sitemap.html';
        echo '<p><a href="' . $sitemap_url . '" target="_blank">' . $sitemap_url . '</a></p>';
        ?>

        <h2>Crawl Results</h2>
        <p><a href="?page=my-seo-plugin&view_results=1">View Results</a></p>
    </div>
    <?php
}

// Function to run the crawl immediately
function run_seo_crawl() {
    delete_previous_crawl_results();
    delete_sitemap_html();
    $home_url = get_home_url();
    $internal_links = extract_internal_links($home_url);
    store_crawl_results($internal_links);
    display_crawl_results();
    save_home_page_as_html($home_url);
    generate_sitemap_html($internal_links);
}

// Function to delete the results from the last crawl
function delete_previous_crawl_results() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE wp_links");
}

// Function to delete the sitemap.html file
function delete_sitemap_html() {
    $sitemap_file = ABSPATH . 'sitemap.html';
    if (file_exists($sitemap_file)) {
        unlink($sitemap_file);
    }
}

// Function to extract internal links from a given URL
function extract_internal_links($url) {
    $response = wp_remote_get($url);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return array();
    }
    $html = wp_remote_retrieve_body($response);
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $internal_links = array();
    $anchors = $dom->getElementsByTagName('a');
    foreach ($anchors as $anchor) {
        $href = $anchor->getAttribute('href');

        if (is_internal_link($href)) {
            $internal_links[] = $href;
        }
    }

    return $internal_links;
}

// Function to check if a given link is internal
function is_internal_link($link) {
    $home_url = get_home_url();
    return strpos($link, $home_url) === 0;
}

// Function to display crawl results on the admin page
function display_crawl_results() {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM wp_links");

    if (count($results) > 0) {
        echo '<h3>Crawl Results</h3>';
        echo '<ul>';
        foreach ($results as $result) {
            echo '<li>' . $result->url . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No crawl results available.</p>';
    }
}

// Function to store crawl results in the database
function store_crawl_results($internal_links) {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE wp_links");

    foreach ($internal_links as $link) {
        $wpdb->insert(
            $wpdb->prefix . 'links',
            array(
                'url' => $link,
                'timestamp' => current_time('mysql')
            ),
            array(
                '%s',
                '%s'
            )
        );
    }
}

// Function to save the home page as a .html file
function save_home_page_as_html($home_url) {
    $html_content = '<html><head><title>Home Page</title></head><body><h1>Welcome to the Home Page</h1></body></html>';
    file_put_contents(ABSPATH . 'index.html', $html_content);
}

// Function to generate the sitemap.html file
function generate_sitemap_html($internal_links) {
    // Create the sitemap content
    $sitemap_content = '<ul>';
    foreach ($internal_links as $link) {
        $sitemap_content .= '<li><a href="' . $link . '">' . $link . '</a></li>';
    }
    $sitemap_content .= '</ul>';

    // Save the sitemap.html file
    $sitemap_file = ABSPATH . 'sitemap.html';
    file_put_contents($sitemap_file, $sitemap_content);
}

// Function to schedule the crawl to run every hour
function schedule_seo_crawl() {
    // Schedule the crawl to run every hour using WordPress Cron
    if (!wp_next_scheduled('my_seo_plugin_hourly_crawl')) {
        wp_schedule_event(time(), 'hourly', 'my_seo_plugin_hourly_crawl');
    }
}
add_action('my_seo_plugin_hourly_crawl', 'run_seo_crawl');
