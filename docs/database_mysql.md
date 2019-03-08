
# Database -- mySQL / Back-End

Apex utilizes a traditional RDBMS, mainly mySQL, but other database engines can be quite easily implemented such as PostgreSQL or Oracle.  As of this writing, Apex currently 
only supports the mySQL engine, but others can be added.  The database library uses static functions, providing easy access to all database functions everywhere within the software.

**Example**

~~~php
namespace apex;

use apex\DB;

$value = 'john';
$rows = DB::query("SELECT * FROM table_name WHERE some_column = %s", $value);
foreach ($rows as $row) { 
    // Do something
}
~~~


### SQL Placeholders

Every function within the database library allows for placeholders to properly sanitize SQL statements, and help 
prevent SQL injection.  All placeholders begin with a **%** sign, followed by one or two characters.  For example:

~~~php
$status = 'inactive';
$group_id = 2;

$rows = DB::query("SELECT * FROM users WHERE status = %s AND group_id = %i", $status, $group_id);
foreach ($rows as $row) { 
    // Do something
}
~~~

In the above example, the value of the status (%s) column must be a string, and the value of the group_id (%i) column must be an integer.  The actual values are then passed as additional 
parameters to the function, and are properly checked and sanitized before being sent to the mySQL database engine.  The below table lists all available placeholders:

Placeholder | Description
------------- |------------- 
%s | String
%i | Integer, no decimal points
%d | Decimal
%b | Boolean, only allowed values are 1 / 0
%e | E-mail address
%url | URL
%ds | Date stamp, must be formatted in YYYY-MM-DD
%ts | Timestampe, must be formatted in HH:II:SS
%ls | For the LIKE operand.  Sanitizes the value, and surrounds it with '%' characters.  For example, the value "john" becomes "'%john%'"


### `$array query(string $sql, array $args)`

**Description:** Performs any SQL statement against the database, but is generally used for SELECT statement, and simply returns the result of the `mysqli_query()` function.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$sql` | string | The SQL statement to execute.
`$args` | array | Array of variables to replace the placeholders within the SQL statement with.


**Example**

~~~php
$rows = DB::query("SELECT id,username,full_name FROM users WHERE group_id = %i AND status = %s", $group_id, $status);
foreach ($rows as $row) { 
    // Do something
}
~~~


### `insert(string $table_name, array $values)`

**Description:** Inserts a new row into the specified database table.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$table_name` | string | The table name to insert a record into.
`$values` | array | Array of key-value paris that denote the column names and the values to insert.

** Example**

~~~php
DB::insert('blog_posts', array(
    'title' => registry::post('blog_title'), 
    'contents' => registry::post('blog_contents'))
);
~~~


### `update(string $table_name, array $values, string $where_sql, array $args)`

**Description:** Updates one or more rows within the provided table name of the database.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$table_name` | string | The table name to update rows within.
`$values` | array | Key-value pairs of the column names and values to update them to.
`$where_sql~ | string | Optional, and the WHERE caluse within the SQL statement (eg. "id = %i")
`$args` | array | A one dimensional array of fill the placeholders within the `$hwere_sql` variable.


** Example**

~~~php
DB::update('blog_posts', array(
    'title' => registry::post('blog_title'), 
    'contents' => registry::post('blog_contents')), 
"id = %i", registry::post('blog_id'));
~~~


### `delete(string $table_name, string $where_sql, array $args)`

**Description:** Deletes rows from the specified table.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$table_name` | string | The table name to delete rows from.
`$where_sql` | string | There WHERE caluse within the SQL statement (eg. "type = %s")
`$args` | array | One-dimensional array of values to replace the placeholders within the `$where_sql` with.

**Example**

~~~php
DB::delete('blog_posts', 'type = %s AND blog_id = %i', $type, $blog_id);
~~~


### `array get_row(string $sql, array $args)`

**Description:** Get the first row found using the given SQL query, and returns an associative array of the values.

Variable | Type | Description
------------- |------------- |------------- 
`$sql` | string | Any SELECT SQL statement to retrieve the desired row.
`$args` | array | Array containing the values to replace placeholders within the SQL statement with.

**Example**

~~~php
if (!$row = DB::get_row("SELECT * FROM some_table WHERE title = %s AND status = %s", $title, $status)) { 
    echo "No table exists with these variables.";
}
~~~


### `array get_idrow(string $table_name in $id_number)`

**Description:** Similiar to the `get_row()` function, and only returns one row from the database, but just a quicker way to look up rows based strictly on the "id" column of the database table if you have it.

Variable | Type | Description
------------- |------------- |------------- 
`$table_name` | string | The table name to retrive the row from.
`$id_number` | int | The ID# of the record to retrive, must match the "id" column of the database table.

**Example**

~~~php
$row = DB::get_idrow('users', $userid);
~~~


### `array get_column(string $sql, array $args)`

**Description:** Returns a one-dimensional array of one specific column within a database table.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$sql` | string | The SQL statement to execute.
`$args` | array | Array of variables to replace the placeholders within the SQL statement with.

**Example**

~~~php
$types = DB::get_column("SELECT type FROM some_type WHERE status = %s", $status);
~~~


### `array get_hash(string $sql, array $args)`

**Description:* Returns an associatve array of the two columns defined within the SQL statement.  Useful for creating a quick key-value pair from a database table.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$sql` | string | The SQL statement to execute.
`$args` | array | Array of variables to replace the placeholders within the SQL statement with.

**Example**

~~~php
$groups = DB::get_hash("SELECT id,name FROM users_groups");
~~~


### `string get_field(string $sql, array $args)`

**Description:** Returns the first column from the first row of the resulting SQL statement.  Useful for getting a single field from a single row from the database.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$sql` | string | The SQL statement to execute.
`$args` | array | Array of variables to replace the placeholders within the SQL statement with.

**Example**

~~~php
$name = DB::get_field("SELECT full_name FROM users WHERE id = %i", $userid);
~~~


### `int insert_id()`

**Description:** Simply returns the ID# of the last row inserted into a table with an id column that auto increments.


### `array show_tables()`

**Description:** Returns a one-dimensional array of all tables within the database.

** Example**

~~php
$tables = DB::show_tables();
~~~


### `array show_columns(string $table_name)`

** Description:** Returns an array of all columns within the given table provide.

 


