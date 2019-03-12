
# Themes and Layouts

All themes are stored within the /themes/ and /public/themes/ directories, with each theme residing in its own 
sub-directory.  The only files that should reside within the /public/ directory are publicly accessible assets such as CSS, Javascript and images.  Below explains the structure of a 
theme, and you can find more information on themes at the below links:

1. [Create and Publish Themes](themes_create.md)
2. [Integrate Existing Theme](themes_integrate.md)
3. [Envata / ThemeForest Designers, Sell More Themes!](themes_envato.md)


### Directory Structure

There are a few standard sub-directories contained within the /themes/THEME_ALIAS/ directory as 
explained in the below table.

Directory | Description
------------- |------------- 
/components | Used for a few components that are generated with the special HTML tags, and are only needed when the theme uses different HTML than the default.  See below for more info.
/layouts | The various page layouts that are supported by the theme (eg. full_width.tpl, 2_col_right_sidebar.tpl, etc.) and can be named anything you wish.  You can then define which layout each page of the web site uses.
/sections | The various sections the theme uses.  This is always header.tpl and footer.tpl, but can also include any additional sections you would like (eg. search_bar.tpl).  Basically, any chunks of HTML code that are used on many different pages.
/tpl | Optionaly, and mirrors the /views/tpl/ directory.  Any .tpl files placed within this directory will be automatically copied over during theme installation, and will replace the existing .tpl files.  Useful if you want a certain /public/index.tpl page to be installed. 
/theme.php | The PHP class for the theme, contains basic information on the theme, and allows you to override how any of the special HTML tags are generated.


### Sections

In the /sections/ sub-directory you will place all section files, which are basically chunks of HTML that are included on 
multiple pages.  This always includes the header.tpl and footer.tpl files, but can also include any other files you would like such as 
for example, search_bar.tpl, right_seidebar.tpl, etc.


Within the section files, use the **~theme_uri ~** merge field to link to the /public/themes/ALIAS/ directory for public assets such as Javascript, CSS and images.  For example:

~~~
<script src="~theme_uri~/js/myscript.js" type="text/javascript"></script>
<link href="~theme_uri~/css/styles.css" rel="stylesheet" type="text/css" />
<img src="~theme_uri~/images/logo.png">
~~~


### Layouts

Inside the /layouts/ directory is where all page layouts will be stored.  There must be a default.tpl file, and any other layouts can be named anything you wish, such as for 
example, right_sidebar.tpl, gallery.tpl, etc.  You can then specify which layout to use for each page of the web site via the CMS->Pages menu of the administration panel.  Any pages without a layout defined will use the default.tpl layout.

Here's an example of a small layout file:

~~~html
<e:theme section="header.tpl">

<e:page_contents>

<e:theme section="footer.tpl">
~~~

The above layout simply includes the header.tpl and footer.tpl files from the /sections/ directory, and the page contents is then replaced with the middle tag.  It's simple as that.  Then 
for example, you may want to create a layout that splits the page into a sidebar and main contents, then include a sidebar.tpl section file within the sidebar. 


### Special HTML Tags

There are a few special HTML tags available you will want to use within your layout files, as explained in the below table.

HTML Tag | Description
------------- |------------- 
`<e:page_title textonly="1">` | Replaced with The page title.  When parsing a .tpl template file, the first occurrence of `<h1> ... </h1>` tags will be removed, and used as the page title, as many times the page title within a layout is in a different position than the top of the body contents.
`<e:page_contents>` | Replaced with the body contents of the .tpl template file being displayed.
`<e:nav_menu>` | Replaced with the navigation menu, see the below components section for more details.  If you only want the navigation menu displayed to users who are logged in, use: `<e:if ~userid~ != 0> <e:nav_menu> </e:if>`
`<e:theme section="FILE.tpl">` | Replaced with the contents of FILE.tpl within the /sections/ directory.


### Merge Fields

The below merge fields are available system-wide and help personalize the site to the client's business.  The values of these fields are 
defined within the administration panel, and should be self explanatory.

* ~domain_name~
* ~current_year~
* ~site_name~
* ~site_address~
* ~site_address2~
* ~site_email~
* ~site_phone~
* ~site_tagline~
* ~site_facebook~
* ~site_twitter~
* ~site_linkedin~
* ~site_instagram~


### /components directory

These are for various components that are generated with the special HTML tags, and are only needed if the theme doesn't support the standard HTML / CSS code.  The below table lists the available components, all of which are named by the name of the special HTML tag.

Component | Description
------------- |------------- 
nav_menu.tpl | Generally required for all themes, and the HTML code in this file is used to generate the navigation menu for the them.  Check the nav_menu.tpl file in other themes to see how it is formatted.
box.tpl | Used for the `<e:box>` tag, and is for a box / panel.  Simply put the `~contents~` merge field anywhere within this file, and it will be replaced with the contents of the box / panel.
box_header.tpl | Used as the HTML for the `<e:box_header>` special HTML tag, and allows for two merge fields -- ~title~ and ~contents~.
data_table.tpl | Used for the data tables, and only requires the opening and closing `<table>` tag.  The only merge field supported is ~contents~, which fills in the header columns and rows.
tab_control.tpl | Used for tab controls and tab pages.  Please refer to the tab_control.tpl files in other themes to see how it's formatted.


