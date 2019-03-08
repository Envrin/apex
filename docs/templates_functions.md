
# Template Functions

There are a few PHP functions that you will need to utilize the template system.  These are all static functions, meaning they are available throughout the entire software without having to initiate a class object.  For details 
on the location of templates, and how to create / delete them, please refer to the [Template component](components/template.md) page.


### `template::assign(string $var_name, mixed $value)`

**Description:** Assigned a merge variable to the template system, allowing you to personalize the template with any necessary information.  The 
`$value` can be either a string, array, or associative array in cases of `<e:section>` tags.  Within the TPL code, you can then place merge fields 
such as `~full_name~`, and it will be replaced with the value of the template variable with the `$var_name` of "full_name".

When you assign an array as the value, you use merge fields withi the TPL code 
such as `~array_name.var_name~`.  For example, the `registry::config()` array is always present as a template variable, and you can use merge fields 
such as `~config.site_name~`.


### `template::add_message(string $message, string $type = 'success')`

**Description:** Adds a user message to the next template that is displayed.  These are the standard success / error / warning messages within themes.  The `$type` variable defaults to "success", but can also be "error", "info" or "warning".

**Example**

~~~php
namespace apex;

use apex\template;

template::add_message(tr("Successfully added new blog post, %s", $title));

template::add_message("You did not specify a blog title", 'error');
~~~


### `registry::set_route(string $route)`

**Description:** Changes the template that will be displayed to the web browser, and is only needed if you ever 
want to force a different template to be displayed other than what's in the URI.  For example, if you would like to 
display the login.tpl template, you would use:

`registry::set_route('login');`


### `template::parse()`

Generally never needed unless for some reaswon you need to halt execution of all other code, and immediately display a template.  Simply set it 
as the registry response contents with:

`registry::set_response(template::parse());`


