<?php

namespace SEOCrawl;

class MySEOPlugin {
    public function __construct() {
        add_action('admin_menu', array($this, 'registerMenuPage'));
        add_action('my_seo_plugin_hourly_crawl', array($this, 'runSEOcrawl'));
    }

    public function registerMenuPage() {
        add_menu_page(
            'SEO Crawl',
            'SEO Crawl',
            'manage_options',
            'my-seo-plugin',
            array($this, 'displaySettingsPage'),
            'dashicons-admin-generic',
            20
        );
    }

    public function displaySettingsPage() {
        if (isset($_POST['crawl_trigger'])) {
            echo '<div class="updated"><p>Crawl triggered successfully!</p></div>';
            $this->runSEOcrawl();
            $this->scheduleSEOcrawl();
        }

        if (isset($_GET['view_results'])) {
            $this->displayCrawlResults();
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

    public function runSEOcrawl() {
        $this->deletePreviousCrawlResults();
        $this->deleteSitemapHTML();
        $home_url = get_home_url();
        $internal_links = $this->extractInternalLinks($home_url);
        $this->storeCrawlResults($internal_links);
        $this->displayCrawlResults();
        $this->saveHomePageAsHTML($home_url);
        $this->generateSitemapHTML($internal_links);
    }

    public function deletePreviousCrawlResults() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE wp_links");
    }

    public function deleteSitemapHTML() {
        $sitemap_file = ABSPATH . 'sitemap.html';
        if (file_exists($sitemap_file)) {
            unlink($sitemap_file);
        }
    }

    public function extractInternalLinks($url) {
        $response = wp_remote_get($url);
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }
        $html = wp_remote_retrieve_body($response);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $internal_links = array();
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');

            if ($this->isInternalLink($href)) {
                $internal_links[] = $href;
            }
        }

        return $internal_links;
    }

    public function isInternalLink($link) {
        $home_url = get_home_url();
        return strpos($link, $home_url) === 0;
    }

    public function displayCrawlResults() {
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

    public function storeCrawlResults($internal_links) {
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

    public function saveHomePageAsHTML($home_url) {
        $html_content = '<html><head><title>Home Page</title></head><body><h1>Welcome to the Home Page</h1></body></html>';
        file_put_contents(ABSPATH . 'index.html', $html_content);
    }

    public function generateSitemapHTML($internal_links) {
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

    public function scheduleSEOcrawl() {
        // Schedule the crawl to run every hour using WordPress Cron
        if (!wp_next_scheduled('my_seo_plugin_hourly_crawl')) {
            wp_schedule_event(time(), 'hourly', 'my_seo_plugin_hourly_crawl');
        }
    }
}

// Instantiate the plugin class
$my_seo_plugin = new \SEOCrawl\MySEOPlugin();
