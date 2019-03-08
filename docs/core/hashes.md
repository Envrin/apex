
# Hashes / Data Sources

Apex contains a hashes library that allows you to easily manage and parse the hashes that are defined within the /etc/PACKAGE/package.php package 
configuration files.  All functions are static, allowing them to be easily accessed anywhere within the software.


### `string hahes::create_options($hash_alias, $value = '', $form_field = 'select', $form_name = '')`

**Description:** Goes through all key-value pairs within the hash, and generates the necessary HTML for the specified form field which defaults to "selectt", but can also be "radio" or "checkbox".

**Example**

~~~php
namespace apex;

use apex\core\hashes;

$options = hashes::create_options('users:status', 'pending', 'checkbox'', 'status');
~~~


### `string hashes::get_hash_var(string $hash_alias, string $var_alias)`

**Description:* Returns the value of a single hash variable.  Retrieves the hash of `$hash_alias`, then returns the value of the `$var_alias` key.  If the value does not exist, returns false.

**Example**

~~~php
namespace apex;

use apex\core\hashes;

$value = io::get_hash_var('users:status', 'active');
~~~


### `string hashes::parse_data_source(string $data_source, string $value = '', string $form_field = 'select', string $form_name = '')`

**Description:** Parses the given data source, and returns the generated HTML for the `$form_field` specified, which again defaults to "select".  For more details on the 
`$data_source` variable and how it's formatted, please refer to the [Hash component](../components/hash.md) page.



