
# CLI -- Components

There are a couple commands you will be using very frequently during development to create and delete components, which are 
explained below.  Always ensure to create all components via the apex.php script, and NEVER manually create the files as they wwill not get registered to the package for publication.


### `create TYPE PACKAGE:[PARENT:]ALIAS [OWNER]`

**Description:** Creates a new component on the system.  Creating new components must adhere to the following rules:

* The `TYPE` variable is the type of the component, and must be one of the following:

    * ajax
    * autosuggest
    * controller
    * cron
    * form
    * htmlfunc
    * lib
    * modal
    * tabcontrol
    * tabpage
    * table
    * template
    * test
    * worker
* The `PARENT` variable is only required for the component types of `controller` and `tabpage`.  For "tabpage" components, naturally this needs to be the alias of the tab control to place the new tab page into.  For the type "controller", this can be left blank, and it will create a new controller parent (eg. directory within /src/PACKAGE/controller/), otherwise is the parent controller to place the new controller into.
* The `OWNER` variable is required for the types "controller", "tabpage", and "template".  This is the package who owns the component, as these components can and will end up in a different pacakges /src/ directory.  Upon publishing, this component will be included in the `OWNER` package.
* The `OWNER` variable is also required for the "worker" component type, but instead of being the owner package, is the routing key for the worker.  This defines which messages to route to the worker.  For more information, please visit the [worker Component](components/worker.md) page of this manual.
* For the "template" component type, the `PACKAGE:PARENT:ALIAS` element of the command is simply the URI of the new template (eg. admin/users/some_page)

**Example (lib:** `php apex.php create lib casino:games`

**Example (template:** `php apex.php create template admin/games/bets casino`

**Example (worker):** `php apex.php create worker casino:user users.user`

**Example (controller):** `php apex.php create controller core:http_requests:games casino`


### `delete TYPE PACKAGE:[PARENT:]ALIAS`

**Description:** Deletes a component from the system.  The two variables passed are the exact same as the `create` command above.  This will permanently remove the component from the filesystem and database.

**Example (lib):** `php apex.php delete lib casino:games`

**Example (template):** `php apex.php delete template admin/casino/bets`

**Example (controller):** `php apex.php delete controller core:http_requests:games`


### `scan PACKAGE`

**Description:** Scans the */etc/PACKAGE/packge.php* configuration file, and updates the database as needed.  Use this during development, after you have updated the package.php file with new information such as config variables or menus, run this to reflect the changes within the database and system.

**Example:** `php apex.php scan casino`




