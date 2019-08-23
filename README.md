# ExternList-Wordpress-Plugin
Shortcode implementation of a external file parsing plugin. This allows the plugin to grab data from external websites and include it within a wordpress page of the site. This plugin was built for openhardware.science, to create a list of projects/residencies, curated by a public git repository.

## Compiling for Wordpress
1. Create a ZIP file of the parsefileplugin folder.
2. The name of the ZIP file should match the main PHP file located within it.
3. In the Wordpress administrator panel, go to Plugins -> "Add New" (tab located on the left hand side)
4. Click on "Upload Plugin" at the top of the page. This will allow you to upload the aforementioned ZIP. Remember to activate the plugin after uploading it.

If errors occur during the uploading/activation step, fix the code and try again.

NOTE: You must delete the plugin of the same name before uploading a newer or older version.
