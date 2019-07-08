
# Views - PHP Methods

There are a few PHP methods that you will need when developing views to assign merge variables, add callout
messages, and so on.  These are available view the *apex\services\template* service, meaning they are
statically available, and are explained below.


### `template::assign(string $var_name, mixed $value)`

**Description:** Assigned a merge variable to the template system, allowing you to personalize the template
with any necessary information.  The `$value` can be either a string, array, or associative array in cases of
`<e:section>` tags.  Within the TPL code, you can then place merge fields such as `~full_name~`, and it will
be replaced with the value of the template variable with the `$var_name` of "full_name".

When you assign an array as the value, you use merge fields withi the TPL code such as
`~array_name.var_name~`.  For example, the `registry::config()` array is always present as a template
variable, and you can use merge fields such as `~config.site_name~`.


### `template::add_callout(string $message, string $type = 'success')`

**Description:** Adds a user message to the next template that is displayed.  These are the standard success /
error / warning messages within themes.  The `$type` variable defaults to "success", but can also be "error",
"info" or "warning".

**Example**

~~~php
namespace apex;

use apex\template;

template::add_message(tr("Successfully added new blog post, %s", $title));

template::add_message("You did not specify a blog title", 'error');
~~~


### `app::set_uri(string $uri)`

**Description:** Changes the template that will be displayed to the web browser, and is only needed if you
ever want to force a different template to be displayed other than what's in the URI.  For example, if you
would like to display the login.tpl template, you would use:

`app::set_uri('login');`


### `template::parse()`

Generally never needed unless for some reaswon you need to halt execution of all other code, and immediately
display a template.  Simply set it as the registry response contents with:

`app::set_res_body(template::parse());`


### `app::echo_template(string $uri)`

**Description:** Should be avoided if possible due to unit tests, but will immediately display the specified
template to the web browser, and end execution.  This is used for things such as when 2FA is required for an
action, and ensures all execution stops and instead the appropriate 2fa.tpl template is displayed.


