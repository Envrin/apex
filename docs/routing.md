
# HTTP Routing

Although HTTP routing is very flexible, at the same time, Apex is designed specifically for typical online operations that 
consist of an administration panel, public web site, and if needed member's area.  Instead of 
forcing you to hard code every single routing URI, Apex provides some basic routing rules to provide efficient and quick 
development.


### URI Routing  

There are various controllers within the */src/core/controller/http_requests/* directory which handle 
all incoming HTTP requests.  Apex will check the first segment of the ERI, and if a *http_requests* controller exists for it, will pass the HTTP request to it.  Otherwise, 
if no controller exists, the request will be handled by the default *http.php* controller, and treated as a page of the public web site.

For example, if visiting http://domain.com/admin/users/create, Apex will check for an "admin.php" controller, see that one exists, and pass the request off to it meaning the administration 
panel.  If visiting */ajax/core/delete_rows*, the request will be passed off to the ajax.php controller, and treated as an AJAX request 
which only provides JSON responses.  If visiting */about_us*, since no about_us.php controller exists, it will be passed to the default http.php controller, 
and treated as a page of the public web site.

If desired, you can easily create your own *http_requests* controller with the following command in terminal:

    php apex.php create controller core:http_requestsALIAS PACKAGE

For example, if you wanted to create a blog.php controller within your package named "myblog", you would use:

    php apex.php create controller core:http_requests:blog myblog

A new file will be created at */src/core/http_requests/blog.php*, and all HTTP requests that have /blog/ as the first segment of the URI will be passed 
to it.  The additional segments of the URI can be called via the `registry::$uri` array.


### Template Routing

Three of the *http_requests* controller parse all requests through the template engine.  These are admin.php for the administration panel, 
members.php for the member's area, and http.php for the public web site.  Every template has both, a .tpl file for the TPL / HTML code, and a .php file for 
any PHP code that needs to be executed for the template.  

All templates are located relative to their URI within the */views/tpl/* and */views/php/* directory.  For example, if visting the 
/admin/users/create URI, the TPL file located at */views/tpl/admin/users/create.tpl* will be displayed.  Also, if exists, 
any PHP code found at */views/php/admin/users/create.php* will be executed.

For another example, if visiting the /servers URI, the TPL file located at 
*/views/tpl/public/services.tpl* will be displayed, but any PHP code found at */views/php/public/services.php* will 
be executed.

If a .tpl file does not exist for the URI, the appropriate 404.tpl template will be displayed.  If no 404.tpl file exists, 
a simple text message will be displayed.

This helps provide efficiency during development as both, Apex and the developer know exactly where the TPL 
and PHP code reside for any given URI, without having to expend time specifying a route for every individual URI.  For full 
details on the templates, please visit the [Template Engine](templates.md) page of this manual.


