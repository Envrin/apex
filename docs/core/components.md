
# Component Handling

There is a components library that allows for the easy checking and loading of components.  This is useful when for example, 
you need to load a controller, hash, or other component.


### `array components::check(string $type, string $alias)`

**Description:* Checks whether or not a component exists and is installed on the system.  Takes in one string formatted in the 
standard Apex format, which is `ALIAS:[PARENT:]PACKAGE`.  If the component does not exist, it will return false.  Otherwise, if 
the components it will return an array containing the package, parent, and alias of the component.

**Example**

~~~php
namespace apex;

use apex\core\components;

if (!list($package, $parent, $alias) = components::check('hash', 'users:status')) { 
    trigger_error(tr("The component does not exist of type %s and alias %s", $type, $alias), E_USER_ERROR);
}
~~~


### `object components::load(string $type, string $alias, string $package = '', $parent = '', $data = array())`

**Description:** Loads a component, and returns the initiated object class that was created.

**Example**

~~~php
namespace apex;

use apex\core\components;

if (!$controller = components::load('controller', 'system', 'core', 'notifications')) { 
    trigger_error(tr("Unable to load component of type controller with alias %s, package %s, and parent %s", $alias, $package, $parent), E_USER_ERROR);
}
~~~


### `string components::get_file(string $type, string $alias, string $package, string $parent = '')`

**Description:** Returns the location of the PHP file for the components.  If the component does not need a PHP file, returns false.

**Example**

~~~php
namespace apex;

use apex\core\components;

$php_file = components::get_file('controller', 'membership_fee', 'transaction', 'transaction');
~~~


### `string components::get_tpl_file(string $type, string $alias, string $package, string $parent = '')`

**Description:** Same as the above function, except returns the location of the TPL components for the component, and if one does not exist, returns false.

**Example**

~~~php
namespace apex;

use apex\core\components;

$tpl_file = components::get_tpl_file('controller', 'membership_fee', 'transaction', 'transaction');
~~~





