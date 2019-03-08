
# AJAX Library

A full AJAX library is available alowing you to easily modify the DOM elements within the web browser without writing any Javascript.  This library is 
also used by the Web Socket server to instantly modify DOM elements in real-time.  If you haven't already, please refer to the [AJAX component](../components/ajax.md) page.

**NOTE:** Quite a few new functions will be added to this later in the near future once Apex development is completed.


### `alert($message)`

**Description:** Opens a dialog box within the web browser with the defined message.


### `clear_table(string $divid)`

**Description:** Clears all table rows within the specified divid table.


### `remove_checked_rows(string $div)`

**Description:** Removes all rows of the table with ID# `$divid` that are checked.


### `add_data_rows(string $divid, string $table_alias, array $rows, array $data = array())`

**Description:** Adds one or more rows to the data table with the ID of `$divid`.  The rows have to be for the table with alias `$table_alias`, and the `$rows` is an array of associative arrays containing the rows to dd.


### `set_pagination(string $divid, array $table_details)`

**Description:** Updates the pagination links of a database table with the ID of `$divid`.  The `$table_details` array is retrieved from the `tables::get_table_details()` function of the library within the core package.



###  
