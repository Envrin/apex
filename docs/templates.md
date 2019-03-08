
# Templates and Themes

Apex offers a very straight forward and clean template / theme system, which should be very easy for anyone to learn.  This page provides a brief overview of each and the location of template / theme files, but please 
view the other pages within this section for full details such as the HTML tags that can be used, how to create and publish themes, etc.


### Templates

All templates have a .tpl extension, are stored within the /views/tpl/ directory of the software, and named relative to the URI being displayed within the web browser.  For example, if viewing the 
page at http://localhost/admin/casino/bets, the software will look for and display the template located at /views/tpl/admin/casino/bets.tpl.  Plase note, these template files are only the body contents 
of the web pages, while the overall layout and design is handled by the themes.

Then obviously, all .tpl template files for the public web site are located within the /views/tpl/public/ directory, and work the same way.  For example, if you want to add a page 
on your site at http://domain.com/services, simple place a file at /views/tpl/public/services.tpl and it will be displayed when viewing the URL.


### Themes and Layouts

All themes are located within the, the /thmes/ and /public/themes/ directory.  Within each directory there is a sub-directory for every theme that is installed on the system.  The 
files within the /themes/ directories are the .tpl files that define the various sections such as page header / footer, the actual layouts, the HTML code for various components, and more.  The files located within the /public/themes/ directory are 
all publically available assets, such as CSS, images, Javascript, and so on.  This is done to limit the number of files that are accessible via HTTP.


### More Information

For more information on templates, themes and layouts, please click on one of the below links.

1. [Template Functions](templates_functions.md)
2. [Template HTML Tags](templates_tags.md)
3. [Template HTML Forms](templates_forms.md)
4. [Execute PHP on Existing Template](templates_execute_php.md)
5. [Themes and Layouts](themes.md)
6. [Integrate Existing Theme](themes_integrate.md)


