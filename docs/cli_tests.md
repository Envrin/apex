
# CLI -- Unit Tests

There are a couple CLI commands available allowing you to easily execute unit tests against a single package, single class, or the new 
system.  All unit tests go through the popular phpUnit, and you may read more about unit testing in Apex via the [Unit Tests via phpUnit](testing.md) page of this manual.

### `mode [DEVEL|PROD] [DEBUG_LEVEL]`

**Description:** Changes the server mode between either development or products, and allows you to change the debug level between 0 -5.

**Example:** `php apex.php mode devel 4`


### `debug NUM`

**Description:** Specifies whether or not to save debugging information for the next requests, which can then be viewed through the Dev Kit->Debugger menu of 
the administration panel.  The value passed must be one of the following:

* 0 - Debugging off
* 1 - Debugging on, but only for next request
* 2 - Debugging on for all future requests until turned off

**Example:** `php apex.php debug 1`


### `test PACKAGE [CLASS_NAME]`

**Description:**  Executes the unit tests created for the specified package.  Optionally, you may specify a `CLASS_NAME`, and it will only execute that single class of unit tests.  If 
not specified, all test classes within the package will be executed.

**Example:** `php apex.php test casino betting`


### `testall`

**Description:** Tests all unit test classes from all packages installed on the system.  

**Example:** `php apex.php testall`


