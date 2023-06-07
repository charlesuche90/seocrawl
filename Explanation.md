Problem to be Solved:

The problem is to build a WordPress plugin that allows an administrator to crawl their website's pages, extract internal links, store the results, and display them for SEO analysis. The goal is to help the administrator improve their website's SEO rankings by understanding the website's link structure.

Technical Spec:

The technical spec for solving the problem includes the following steps:

    Set up a back-end admin page where the administrator can trigger a crawl and view the results.
    On crawl trigger, delete the previous crawl results and sitemap.html file if they exist.
    Start the crawl from the website's root URL (home page).
    Extract all internal hyperlinks from the home page.
    Store the extracted links temporarily in a database.
    Display the crawl results on the admin page.
    Save the home page as an HTML file.
    Generate a sitemap.html file that lists the internal links.
    Allow the administrator to view the sitemap.html page on the front-end.
    Schedule the crawl to run every hour using WordPress Cron.

Technical Decisions:

    The plugin is implemented as a class called MySEOPlugin under the SEOCrawl namespace.
    WordPress actions admin_menu and my_seo_plugin_hourly_crawl are used to register the menu page and schedule the crawl.
    The crawl results are stored in a database table named wp_links using the $wpdb global object.
    The DOMDocument class is used to extract internal links from the home page's HTML.
    The extracted links are filtered to include only internal links based on the home URL.
    The crawl results and sitemap HTML file are stored and displayed using file operations.
    The sitemap.html file is generated as an unordered list (<ul>) containing the internal links.

Code Explanation:

    The code registers the menu page using the add_menu_page function and displays the settings page using the displaySettingsPage method.
    On crawl trigger, the runSEOcrawl method is called, which performs the crawling, storage, and display of results.
    The deletePreviousCrawlResults method truncates the wp_links table to remove previous crawl results.
    The extractInternalLinks method uses wp_remote_get to fetch the home page's HTML and then extracts internal links using DOMDocument.
    The crawl results are stored in the database using $wpdb->insert in the storeCrawlResults method.
    The displayCrawlResults method retrieves and displays the crawl results from the wp_links table.
    The saveHomePageAsHTML method saves the home page's content as an HTML file.
    The generateSitemapHTML method generates the sitemap.html file by creating an unordered list of internal links.
    The scheduleSEOcrawl method schedules the crawl to run every hour using WordPress Cron.

Solution and Desired Outcome:

The solution addresses the problem by providing an admin page where the administrator can trigger a crawl and view the crawl results. The crawl extracts internal links from the home page, stores them temporarily, and displays them for SEO analysis. The crawl also saves the home page as an HTML file and generates a sitemap.html file. The scheduled hourly crawl ensures that the administrator regularly receives updated crawl results.

By following the user story, the solution provides the desired outcome of allowing the administrator to understand their website's link structure, identify SEO improvement areas, and manually search for ways to enhance SEO rankings. The crawl results and sitemap.html page help the administrator visualize the internal linking pattern, assess page relevance, and optimize the website's SEO strategy accordingly.
Approach and Rationale:

In approaching the problem, the solution focuses on simplicity and effectiveness. It crawls only the home page instead of recursively crawling all internal links to keep the implementation manageable. Temporary storage in a database table and file-based operations enable the extraction, storage, and display of crawl results. The use of WordPress actions and scheduling via WordPress Cron ensures seamless integration with the WordPress environment.

The chosen approach strikes a balance between functionality and maintainability. It leverages existing WordPress capabilities and APIs, making it compatible with WordPress 5.0 and above. The use of procedural code where appropriate and the adherence to OOP principles provide a structured and extensible solution. The technical decisions made prioritize practicality, performance, and ease of use, enabling the administrator to effectively analyze and improve the website's SEO rankings.