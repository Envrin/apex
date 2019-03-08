
# HTML Form Handling and Validation

Apex contains a forms library allowing for very easy form validation and processing.  Below explains all the various functions 
available to you.  If you haven't already, please also refer to the [HTML Form component](../components/form.md) page.  All functions are static methods 
allowing for easy access to them anywhere within the software.


### `forms::validate_fields([string $error_type = 'template'], [array $required = array()], [array $datatypes = array()], [array $minlength = array()], [array $maxlength = array()], [array $labels = array()])`

**Description:** Useful method allowing for the easy server-side validation of HTML form submissions.  Is also used by 
the smart form component within Apex.  Allows you to define various validation rules via arrays, then will either output any errors to 
the template engine, or the *trigger_error()* PHP function.

**Parameters:**

Variable | Type | Description
------------- |------------- |-------------
`$error_type~ | string | Must be either "template" or "error", and defaults to "template".  Defines whether to send any error messages to the template engine to displayed as validation errors on the proceeding template, or to trigger an error and siplay the error template.
`$required` | array | One-dimensional array of form field names that are required, and can not be blank / non-existent.
`$datatypes` | array | Array of key-value pars, the keys being the form field names, and the values being their required data type.  Supported values are: "alphanum", "integer", "decimal", "email", and "url"
`$minlength` | array | Array of key-value pairs, the keys being the names of form fields, and value being the minimum length in characters they must meet.
`$maxlength` | array | Array of key-value pairs, the keys being the name of form fields, and the value being the maximum number of characters the value allows.
`$labels` | array ~ Allows you to optionally specify a specific name for the form field in case of any errors with its value.  Defaults to the `ucwords()` version of the form field name if non-existent (eg. "full_name" turns into "Full Name" within the error message).


### `bool forms::validate_form(string $form_alias, [string $error_type = 'template'], [array $data = array()])`

**Description:**  useful, and will server-side validate a form submission of any Apex form component, using 
the various attributes defined within the array returned by the `get_form_fields()` method of the form component.  

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$form_alias~ | string | The alias of the form component, formatted in the standard PACKAGE:ALIAS format.
`$error_type` | string | Must be either "template" or "error", and defaulst to "template", which means any errors will be passed to the template engine to be displayed on the procedding template.  Otherwise, if "error" the error template will be displayed for the first error found.
`$data` | array | Array containing any attributes within the &lt;e:function&gt; tag that calls the form.

**Return Value:** Returns a boolean stating whether or not the template engine has received any errors from the form validation.

**Exmaple**
~~~php
namespace apex;

use apex\core\forms;

if (!forms::validate_form('users:register')) { 
    echo "There are validation errors.";
}
~~~


### `array forms::get_uploaded_file(string $var)`

**Description:** Tales om pme stromg. the name of the file input field, checks if the file has been uploaded and returns it.  If no file has been uploaded returns false.

**Return Value:** Returns an array containing the filename, MIME type and contents of the uploaded file.

**Example**

~~~php
namespace apex;

use apex\core\forms;

if (!list($filename, $mime_type, $contents) = forms::get_uploaded_file('some_file')) { 
    echo "No file was uploaded";
}
~~~


### `array forms::get_chk(string $var)`

**Description:** Clean way to retrieve the values of a checkbox form field without any worry about warning or error messages.  Takes in one string, the name of the checkbox form field, and returns an array of all checked values.  Checks to ensure the form field exists within the form, and whether or not it's an array.


### `string forms::get_date_interval(string $var)`

**Description:** Returns the value of the `<e:date_interval>` HTML tag.  If not properly filled in, returns false.




