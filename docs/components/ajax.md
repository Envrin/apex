
# AJAX Function Component

&nbsp; | &nbsp;
------------- |-------------
**Description:** | Allows PHP code via AJAX to be easily executed, plus includes an AJAX library allowing for the easy manipulation of DOM elements within the web browser, without having to use any Javascript.
**Create Command:** | `php apex.php create ajax PACKAGE:ALIAS`
**File Location:** | /src/PACKAGE/ajax/ALIAS.php
**Namespace:** | `apex\PACKAGE\ajax`
**HTML Call:** | `<a href="javascript:ajax_send(PACKAGE/ALIAS', 'field1,field2,field3');">Send Ajax</a>`


## Methods

Below explains all methods available within this class.


### `process()`

**Description:** Simply contains any PHP code that needs to be executed for the AJAX call.  Also extends the /lib/ajax.php library, which 
contains variable methods to easily manipulate the DOM elements within the web browser.

**NOTE:** This is still a work in profess as the AJAX library needs to be expandeded on, and this page will be updated later.  For 
now, just view the /lib/ajax.php call to see the methods currently available.
 
