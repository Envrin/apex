
# Request Routing (http / cli)

All incoming requests to Apex are handled either via HTTP or CLI (command line). Regardless of request origin,
the [app class](app.md) remains the main / central class which helps facilitate the request and response.


## Http / Middleware Controllers

How the HTTP requests are routed depends directly on the first segment of the URI, and the http / middleware
controllers installed within the */src/core/controller/http_requests/* directory.  If there is a controller
named the same as the first segment of the UIR, the request will be passed off to it.  Otherwise the request
will be passed to the default "http.php" controller and treated as a page of the public web site.

For example, if accessing */admin/users/create*, the request will be handled by the "admin.php" controller. If
accessing */image/product/53/thumb.jpg* the request will be handled by the "image.php" controller.  All
requests for which the first segment of the URI does not match a specific controller will be handled by the
default "http.php" controller and treated as a page of the public web site.


### Views Directory Structure

Many of the requests will output a view to the web browser (administration panel, public web site, etc.), and
the view displayed also depends directly on the URI being accessed. All views are stored within the
*/views/tpl/* directory, and Apex will simply display the .tpl file relative to the URI.  For example, if
accessing the */admin/users/create* URI, the view at */views/tpl/admin/users/create.tpl* will be displayed.

The one exception to this is the public web site, where the view displayed is the URI relative to the
*/views/tpl/public/* directory.  For example, if accessing the */services* URI, the view located at
*/views/tpl/public/services.tpl* will be displayed.  If no .tpl file exists at the correct location, the
correct 404.tpl view will be displayed.

##### Views PHP Code

On top of the .tpl files, all views also have an associated .php file located within the */views/php/*
directory at the same relative location.  Any PHP code placed in these files is automatically executed when
the view is displayed, and is specific to that view. Please recall the [Apex Request
Variables](app.md#apex_request) within the app class, as they will be used often within these PHP files.


### Create Http / Middleware Controller







