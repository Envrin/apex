
# Integrate Existing Theme

You can easily integrate any HTML / CSS theme available on the internet from places such as ThemeForest.  It should generally only take about 30 minutes to integrate 
an existing theme.  To do so, follow the below steps.

1. In terminal, move to the Apex installation directory, and type `php apex.php create theme theme_alias`
2.  Upload all necessary CSS, Javascript and image files to the /public/themes/theme_alias/ directory.
3. Slice the main page into a header.tpl and footer.tpl files while removing the actual page contents.  Pleace these files in the /themes/theme_alias/sections/ directory.
4. Within the header.tpl and footer.tpl place the `~theme_dir ~` where needed, generally in the paths to Javascript and CSS files.
5. Add the `e:nav_menu>` HTML tag where needed in place of the navigation menu.
6. Replace the contents of the `<title> ... </title>` tags with the HTML tag `<e:page_title textonly="1">`.  Add the same HTML tag where necessary, generally right at the bottom of the header.tpl file.
7. Create a file at /themes/theme_alias/layouts/default.tpl as necessary.  Below shows some example code of what the file may look like.


**Example /layouts/default.tpl File**

~~~html
<e:theme section="header.tpl">


<e:page_contents>

<e:theme section="footer.tpl">
~~~

That's it!  Now activate the theme, and it should begin appearing on the web site without any issue.

