
# Components Overview

Apex supports various standardized components allowing for quick, efficient and quality development, plus ensures standardization across packages and developers.  Below lists all the components that are supported.

Name | Description
------------- |-------------
[Library](components/lib.md) | Blank PHP library file for a new class, and is most commonly used within Apex.
[Template](components/template.md) | One of the core components, and are the templates that display output to the browser, and perform necessary actions.
[AJAX Function](components/ajax.md) | Easily execute code via AJAX with no Javascript required.  Full library available allowing for easily manipulation of the DOM elements.
[Auto-Suggest / Complete](components/autosuggest.md) | Standard auto-suggest/complete boxes that allow users to enter a few characters, and a list of possible options is displayed.  Useful for things such as searching user accounts, and can be easily placed in any template within a couple minutes with no Javascript.
[Controller](components/controller.md) | Allows for the easy handling of different process flows which overall work in the same way, but handle the data slightly differently.  Example of this are e-mail notifications (different notification types pull different database records to personalize the e-mail message) and transaction types (deposit, withdraw, commission, fee, product purchase, etc.).
[Crontab Job](components/cron.md) | Easily add in crontab jobs that execute at specified time intervals.  No need to add the crontab job to the server itself, as the in-house cron daemon will execute it when needed.
[Data Tables](components/table.md) | Quality, stylish data tables with full AJAX functionality including pagination, search, sort, and row deletion.  Flexible, customizable, and can be developed to display any data and placed in templates within a couple short minutes.
[HTML Form](components/form.md) | Quality HTML forms with full Javascript validation, easily customizable with conditional fields, can be placed in any template with one HTML tag blank, with values from the database or pre-filled with POST variables (ie. in case of user submission errors).
[HTML Function](components/htmlfunc.md) | Allows you to place one HTML tag in any templates, and have it replaced with anything you wish.  Useful when you want the same element / functionality, or variation thereof, placed within multiple locations throughout the system.
[Modal](components/modal.md) | Standard modal / popup dialog allowing for a more user-friendly experience.  Can contain any output you want, with built-in functionality for form processing within the modal.
[Tab Control ](components/tabcontrol.md) | Supports both, static and dynamic tab controls.  These dynamic tab controls are easily expandable by other packages, plus allow for the easy placement in multiple templates system-wide while providing the same functionality.
[Tab Page](components/tabpage.md) | Singular pages within an existing tab control, allowing your package to expand on existing tab controls (eg. add a tab when managing a user's profile).
[Unit Test](components/test.md) | Unit tests via phpUnit, allowing you to provide 100% code coverage with unit tests.
[Trigger](components/trigger.md) | Allows you to have PHP automatically executed at pre-deternubed events as prescribed by other developers.  For example, when a new user is created, you can define a trigger that executes additional PHP code.


