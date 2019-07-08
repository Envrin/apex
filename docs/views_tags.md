
# Views - Special HTML Tags

Views support various special HTML tags that help provide efficient and streamlined development.  All special
HTMl tags are prefixed with `<a:`, such as for example, `<e:textbox ... >`.  Below explains all special HTML
tags that are supported.

1. <a href="#form">`<a:form>`</a>
2. <a href="#box">`<a:box>`</a>
3. <a href="#if">`<a:if>`</a>
4. <a href="#section">`<a:section>`</a>
5. <a href="#tab_control">`<a:tab_control>`</a>
6. <a href="#data_table">`<a:data_table>`</a>
7. <a href="#theme_tags">Theme Tags</a>


<a name="form">
### `<a:form>`

**Description:** Replaced with a `<form>` tag, the action being the current view displayed, and method being
"POST" by default.  No attributes are needed if only pointing the form to the same view being displayed.

**Attributes**

Attribute | Required | Description ------------- |------------- |------------- action | No | Only needed if
you need the form to point to a different view than the one being displayed, and is the URI relative to the
software. method | No | Defaults to POST, but change to GET if necessary. enctype | No | Defaults to
"application/x-www-form". class | No | Defaults to "form-inline" id | no | Defaults to "frm_main". file_upload
| No | If set to 1, changes the `enctype` attribute to "multipart/form-data" to allow for file uploads.


<a name="box">
### `<a:box> ... </a:box>` / `<a:box_header> ... </a:box_header>`

**Description:** Standard box / panel as used in Bootstrap themes.  Used to separate page sections.  Used with
the `<a:box_header title="..."> ... </a:box_header>` tags as the title / header element of the box.

**Example:**

~~~
<a:box>
    <a:box_header title="Store Details">
        <p>From below you can view and manage all details on the selected store.</p>
    </a:box_header>

    ... contents of box ...
</a:box>
~~~

<a name="if">
### `<a:if condition> ... <a:else> ... </e:if>`

**Description:** Allows for conditional HTML to be displayed / hidden as necessary.  The condition can be any
PHP code you would like, and it will be checked as to whether or not the condition is true or false.

**Example**

~~~
<a:if ~userid~ != 0>
    <a:nav_menu~
<a:else>
    No menu to display here.
</a:if>
~~~

<a name="section">
### `<a:section name="array_name"> ... </a:section>`


**Description:** Will loop through the associative array assigned to the template via the `template::assign()`
function, and will copy the contents between the tags for every array within the array.  For example:

**Example HTML**

~~~html
<a:section name="posts">
    <h3>~posts.title~</h3>
    <p>Published On: ~posts.date_posted~</p><br />

    <p>~post.contents~</p>
</a:section>
~~~

**Example PHP**

~~~php
<?php

namespace apex;

use apex\app;
use apex\services\template;

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


<a name="tab_control">
### `<a:tab_control> ... </a:tab_control>`

**Description:** Allows you to manually create tab controls within the TPL code.

**Example*

~~~
<a:tab_control>

    <a:tab_page name="General">
        <h3>General">
        ... contents of tab page ...
        </a:tab_page>

    <a:tab_page name="orders">
        <h3>Orders</h3>
            ... contents of the tab page ...
    </a:tab_page>

    <a:tab_page name="Referalls">
        <h3>Referalls</h3>
        .. contents of tab page ...
    </a:tab_page>

</a:tab_control>
~~~


<a name="data_table">
### `ae:data_table> ... </a:data_table>`

**Description:** A standard data table with header columns, and striped backround colored rows.  Generally
never needed, as data tables are usually displayed via the `a<function>` tag.

**Attributes**

Attribute | Required | Description ------------- |------------- |------------- class | No | The CSS class name
of the data table.  Defaults to "table table-bordered table-striped table-hover". id | No | The ID# of the
table element.


<a name="theme_tags">
### Theme Tags

The below table lists various HTML tags that are only needed during theme development, and not for individual
views.

Tag | Description ------------- |------------- `<a:callouts>` | Replaced with the callouts (ie. success /
error messages) that are displayed on the top of the page.  You only need this tag when implementing themes,
not individual views. `<e:page_title>` | The title of the current page being displayed.  Apex will first check
the database to see if a page title has been specifically defined for the page, and if not, will check the TPL
code if any `<h1> ... </h1>` tags exist and use that, and otherwise will just default to the site name
configuration variable. `<a:nav_menu>` | The navigation menu of the area being displayed (administration
panel, member area, public web site), and uses the HTML tags located within the
`/views/themes/ALIAS/components/nav_menu.tpl` file of the theme being used.  Please refer to these
*nav_menu.tpl* files to see proper formatting.


