
# Template Structure / Engine

Apex offers a very straight forward and clean template structure, which should be very easy for anyone to learn.  All templates 
have a .tpl extension, are stored within the /views/tpl/ directory of the software, and named relative to the URI being displayed within the web browser.  For example, if viewing the 
page at http://localhost/admin/casino/bets, the software will look for and display the template located at /views/tpl/admin/casino/bets.tpl.

Then obviously, all .tpl template files for the public web site are located within the /views/tpl/public/ directory, and work the same way.  For example, if you want to add a page 
on your site at http://domain.com/services, simply place a file at /views/tpl/public/services.tpl and it will be displayed when viewing the URL.  When visiting a URI for which the .tpl file does not 
exist, the 404.tpl file will be displayed.

Please note, these template files are only the body contents of the web pages, while the overall layout and design is handled by the themes.  The .tpl files are 
simply standard HTML code, but with some special tags supported as explained below.


### PHP Code

There is a corresponding .php file for every .tpl file, located within the /views/php/ directory, and once again relative to the URI being 
displayed.  For example, if the .tpl file at /views/tpl/admin/casino/bets.tpl is being displayed, if it exists, the .php fiel at /views/php/admin/casino/bets.php will be 
automatically executed.  This is the PHP code for this specific template.


### Create Template

When developing a package, you need to create all templates via the apex.php script to ensure they're packaged correctly during publication.  Simply open up terminal, 
change to the installation directory and type:

`php apex.php create template URI PACKAGE`

You need to specify the URI without .tpl extension, and the package you want the template included in during publication.  For example, if 
developing a package called "casino", and you can to create a template at http://localhost/admin/casino/games, you would use:

`php apex.php create template admin/casino/games casino`


### More Information

For more information on the templates including the various special HTML tags supported, 
merge fields, and HTML forms, please click one of the below links.

1. [Template PHP Functions](templates_functions.md)
2. [Template HTML Tags](templates_tags.md)
3. [Template HTML Forms](templates_forms.md)
4. [Execute PHP on Existing Template](templates_execute_php.md)


