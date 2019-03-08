
# Trigger Component

&nbsp; | &nbsp;
------------- |-------------
**Description:** | Allows execution of additional PHP code when various actions occur within the software.  For example, you can create a trigger thaqt executes after a user is created, if you needed to perform additional actions.
**Create Command:** | `php apex.php create PACKAGE:trigger TRIGGER_EVENT:ALIAS OWNER`
**File Location:** | /src/PACKAGE/trigger/TRIGGER_EVENT/ALIAS.php
**Namespace:** | `apex\PACKAGE\trigger\TRIGGER_EVENT\ALIAS`


The `TRIGGER_EVENT` when creating the trigger is dependent on the package developer that you are integrating with.  For example, 
if creating a "casino" trigger that executes after every user is created, you would use:

`php apex.php create trigger users:created:casino casino

Refer to the documentation of the package you're integrating with to see which TRIGGER_EVENTs are available.


## Methods

Below explains all methods within this class.


### `process(array $data)`

**Description:** Simply executes the necessary PHP code upon the trigger event occurring.  The `$data` array passed is 
any additional information available as decided by the developer of the trigger event, and please refer to the package documentation 
for details on what this array includes.  For example, the trigger after creating a user will contains a `'userid`' variable in this array defining the ID# of the user that was created.




