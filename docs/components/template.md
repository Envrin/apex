
# Template Component

&nbsp; | &nbsp;
------------- |------------- 
**Description:** | The templates that are displayed within the administration panel, public web site, and member's area.
**Create Command:** | `php apex.php create template URI PACKAGE 
**File Location:** | /views/tpl/URI.tpl<br />/views/php/URI.php
**Namespace:** | `apex`

#### Example

`php apex.php create template /admin/settings/blog myblog`

The above will create a template accessible via web browser at http://localhost/admin/settings/blog, with two files located at:

- /views/tpl/admin/settings/blog.tpl
- /views/php/admin/settings/blog.php

The template will also be owned by the `myblog` package, meaning it will be included upon publishing the package to a repository.


### .tpl File

This starts as simply a blank file, and is the TPL / HTML code that is outputted to the web browser.  There are various 
special tags available to help with development, and for full details, please visit the [Template Engine](../templates.md) page of this manual.

For a quick example:

~~~

<h1>Create New Post</h1>

<e:form>

<e:box>
    <e:box_header title="Post Details">
        <p>Enter the desired details below, and a new post will be added to the system.</p>
    </e:box_header>

    <e:function alias="display_form" form="myblog:post">
</e:box>
~~~


### .php File

The PHP file is optional, but if it exists, will be automatically included upon parsing the template.  This is meant to 
perform any necessary actions for the specific template, and can be anything you wish.  A quick example file:

~~~php
<?php

namespace apex;

use apex\DB;
use apex\registry;
use apex\template;
use apex\myblog\posts;

// Create post, if needed
if (registry::$action == 'create_post') { 

    $post = new posts();
    $posts->create();

    // User message
    template::add_message(tr("Successfully created new post with title, %s", registry::post('title')), 'success');

}

// Assign template variables
template::assign('blog_title', registry::config('myblog:title'));

?>
~~~


