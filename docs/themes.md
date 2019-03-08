
# Themes and Layouts

All themes are stored within the /themes/ and /public/themes/ directories.  Each theme has its own sub-directory within 
the two directories.  The only files that should reside within the /public/ directory are files that need to be accessible to the 
web browser such as CSS, Javascript and images.


### Directory Structure

There are a few standard sub-directories contained within the /thmes/THEME_ALIAS/ directory as 
explained in the below table.

Directory | Description
------------- |------------- 
/components | Used for a few components that are generated with the special HTML tags, and are only needed when the theme uses different HTML than the default.  See below for more info.
/layouts | The various page layouts that are supported by the theme (eg. full_width.tpl, 2_col_right_sidebar.tpl, etc.) and can be named anything you wish.  You can then define which layout each page of the web site uses.
/sections | The various sections the theme uses.  This is always header.tpl and footer.tpl, but can also include any additional sections you would like (eg. search_bar.tpl).  Basically, and chunks of HTML code that are used on many different pages.
/theme.php | The PHP class for the theme, contains basic information on the theme, and allows you to override how any of the special HTML tags are generated.


### /components directory

These are for various components that are generated with the special HTML tags, and are only needed if the theme doesn't support the standard HTML / CSS code.  The below table lists the available components, all of which are named by the name of the special HTML tag.

Component | Description
------------- |------------- 
nav_bar.tpl | Generally required for all themes, and the HTML code in this file is used to generate the navigation menu for the them.  Check the nav_menu.tpl file in other themes to see how it is formatted.
box.tpl | Used for the `<e:box>` tag, and is for a box / panel.  Simply put the `~contents~` merge field anywhere within this file, and it will be replaced with the contents of the box / panel.
box_header.tpl | Used as the HTML for the `<e:box_header>` special HTML tag, and allows for two merge fields -- ~title~ and ~contents~.
data_table.tpl | Used for the data tables, and only requires the opening and closing `<table>` tag.  The only merge field supported is ~contents~, which fills in the header columns and rows.
tab_control.tpl | Used for tab controls and tab pages.  Please refer to the tab_control.tpl files in other themes to see how it's formatted.


### Layouts

In the /sections/ directory you will place any desired sections, which is generally always header.tpl and footer.tpl, but others can be added as desired.  These files can be named anything you want.

Inside the /layouts/ directory is where all page layouts will be stored.  There must be a default.tpl file, and any other layouts can be named anything you wish.  You can then specify which layout to use for each page of the web site, and any pages without a layout defined will use the default.tpl layout.

Here's an example of a small layout file:

~~~html
<e:theme section="header.tpl">

<e:page_contents>

<e:theme section="footer.tpl">
~~~

The above layout simply includes the header.tpl and footer.tpl files from the /sections/ directory, and the page contents is then replaced with the middle tag.  It's simply as that!


### Theme Manager

Although not complete as of this writing, there is a Maintenance->Theme Manager menu in the administration panel.  This will connect to any repositories configured on the system, and download all available themes, allowing for easy one-click install of any theme.  It will also integrate with the Envato / ThemeForest API, so if there is a theme integrated that 
you wish to use, you can simply purchase a license from ThemeForest, and enter your license key into the admin panel to unlock the theme.


