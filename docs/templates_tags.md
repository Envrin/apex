
# Template HTML Tags

Apex offers a very straight forward and easy to use template engine, allowing for quick and efficient writing of templates that any theme can be overlayed on.  Various 
special HTML tags are supported, all beginning with `e:` (eg. `e:textbox>`).  This document outlines all the various HTML tags and functions available.


### `<e:user_message>`

**Description:** Is replaced with the success / error / warning messages on the top of the page contents, alerting the user if an action was successful, or if any errors occurred, etc.  This should generally always be placed within the */themes/THEME/sections/header.tpl* file, so it is automatically included in every page.


### `<e:page_title>`

**Description:** This tag is replaced with the title of the current page being displayed.  Apex will first check the database to see if a page title has been specifically defined for the page, and if not, will check the TPL code if any `<h1> ... </h1>` tags exist and use that, and otherwise will just default to the site name configuration variable.

**Attributes**

Attribute | Required | Description
------------- |------------- |------------- 
textonly | No | If set to 1, will only display the text of the page title.  Otherwise, will return the title surrounded by `<h1>` tags.

### `<e:nav_menu>`

**Description:** Generates the navigation menus for the given area (admin panel, public site, etc.).  This should always be placed within the /sections/header.tpl file of the theme, and if necessary surround it by `<e:if>` tags, so the menus are only shown if the user is logged in.  For example:

~~~html
<e:if ~userid != 0>
    <e:nav_menu>
</e:if>
~~~


### `<e:if condition> ... <e:else> ... </e:if>`

**Description:** Allows for conditional HTML to be displayed / hidden as necessary.  The condition can be any PHP code you would like, and it will be checked as to whether or not the condition is true to false.


### `<e:section name="array_name"> ... </e:section>`

**Description:** Will loop through the associative array assigned to the template via the `template::assign()` function, and will copy the contents between the tags for every array within the array.  For example:

~~~html
<e:section name="posts">
    <h3>~posts.title~</h3>
    <p>Published On: ~posts.date_posted~</p><br />

    <p>~post.contents~</p>
~~~

~~~php
<?php

namespace apex;

use apex\template;

$posts = array(
    array(
        'title' => 'Some Title', 
        'date_posted' => 'Jan 19, 2019', 
        'contents' => 'The post contents'
    ), array(
        'title' => 'Another Post Title', 
        'posted_on' => 'Jan 23, 2019', 
        'contents' => 'Other post contents'
    )
);

// Assign template variable
template::assign('posts', $posts);
~~~


### `<e:form>`

**Description:** Replaced with a `<form>` tag, the action being the current template displayed, and method being "POST" by default.  No attributes are needed if only pointing the form to the same template being displayed.

**Attributes**

Attribute | Required | Description
------------- |------------- |------------- 
action | No | Only needed if you need the form to point to a different template than the template being displayed, and is relative to the software (eg. *admin/users/create*), and no full URL is needed.
method | No | Defaults to POST, but change to GET is necessary.
enctype | No | Defaults to "application/x-www-form".
class | No | Defaults to "form-inline"
id | no | Defaults to "frm_main".
file_upload | No | If set to 1, changed the `enctype` attribute to "multipart/form-data" to allow for file uploads.


### `<e:box> ... <e/box>`

**Description:** Standard box / panel as used in Bootstrap themes.  Used to separate page sections.


### `<e:box_header> ... </e:box_header>`

**Description:** A header for the `<e:box>` tag.  Requires a "title" attribute, and any contents between the tags is placed just below the header with a horizontal line underneath.  Good for giving a quick description of the page section.

**Attributes**

Attribute | Required | Description
------------- |------------- |------------- 
title | Yes | The title of the header.


### `<e:data_table> ... </e:data_table>`

**Description:** A standard data table with header columns, and striped backround colored rows.  Generally never needed, as data tables are usually displayed via the `e<function>` tag.

**Attributes**

Attribute | Required | Description
------------- |------------- |------------- 
class | No | The CSS class name of the data table.  Defaults to "table table-bordered table-striped table-hover".
id | No | The ID# of the table element.



### `<e:tab_control> ... </e:tab_control>`

**Description:** Allows you to manually create tab controls within the TPL code.  For details on manually creating tab controls, please refer to the [Tab Control component](components/tabcontrol.md) page.


### 
