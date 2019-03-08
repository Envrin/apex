<?php

namespace apex;

use apex\registry;
use apex\log;
use apex\debug;
/**
* Handles all database communication between the software 
* and the mySQL database.  Please refer to the developer 
* documentation for more details on methods within this class.
*/
class DB
{

    // Properties
    public static $connections = array();
    public static $conn;
    public static $tables = array();
    public static $columns = array();
    protected static $prepared = array();
    protected static $raw_sql;

/**
* Initiates a connection to the mySQL database, and places 
* the resulting connection at DB::$conn.
* 
*     @param string $type Must be either 'read' or 'write', and defines which database username to connect with.
*     @param bool $skip_checks Only used internally to avoid going into a continuous loop.
*/
public static function dbconnect(string $type = 'read') 
{

    // Get database vars
    $vars = registry::get_db_server($type);

    // Connect
    if (!$tmp_conn = mysqli_connect($vars['dbhost'], $vars['dbuser'], $vars['dbpass'], $vars['dbname'], $vars['dbport'])) { 
        throw new DBException('no_connect');
    }

    // Add to connections
    self::$conn = $tmp_conn;
    self::$connections[$type] = self::$conn;

    // Set timezone to UTC
    mysqli_query(self::$conn, "SET TIME_ZONE = '+0:00'");

    // Debug
    debug::add(4, fmsg("Connected to database, type: {1}", $type), __FILE__, __LINE__);

}

/**
* Checks to see whether we're connected to the mySQL database, 
* and if so, if we're connected with the correct 
* mySQL user depending on if it's a read / write connection.
*/
public static function check_connection(string $type = 'read') 
{

    // Return if connected
    if (isset(self::$connections[$type])) { 
        self::$conn = self::$connections[$type];
        return true;

    // Conect
    } else {
        self::dbconnect($type);
    }

}

/** 
* Executes the 'SHOW TABLES' command, and returns a one-dimensional 
* array of all tables within the database.
*
*     @retirm array One-dimensional array of all table names within the database.
*/
public static function show_tables() 
{

    // Connect
    self::check_connection('read');

    // Check IiF TABLES ALREADY RETRIEVED
    IF (COUNT(self::$tables) > 0) { RETURN self::$tables; }

    // Get tables
    $result = self::query("SHOW TABLES");
    while ($row = self::fetch_array($result)) { 
        self::$tables[] = $row[0];
    }

    // Return
    return self::$tables;

}

/**
* Returns an array of all columns within the 
* given table name.
*
*     @param string $table_name The name of the table to retrieve column names from.
*     @param bool $include_types Whether or not to include the types of columns as the value of array
*     @return array One-dimensional array of column names.
*/
public static function show_columns(string $table_name, bool $include_types = false) 
{

    // Connect
    self::check_connection('read');

    // cHECK IF COLUMNS ALREADY GOTTEN
if (isset(self::$columns[$table_name]) && is_array(self::$columns[$table_name]) && count(self::$columns[$table_name]) > 0) { 
        if ($include_types == true) {
            return self::$columns[$table_name]; 
        } else { 
            return array_keys(self::$columns[$table_name]);
        }
    }

    // Get column names
    self::$columns[$table_name] = array();
    $result = self::query("DESCRIBE $table_name");
    while ($row = self::fetch_array($result)) { 
        self::$columns[$table_name][$row[0]] = $row[1];
    }

    // Return
    if ($include_types === true) { 
        return self::$columns[$table_name];
    } else { 
        return array_keys(self::$columns[$table_name]);
    }


}

/**
* Inserts a new record into the database.
* 
*     @param string $table The table name to insert a row into.
*     @param array Array of key-value paris of column names, and their values to insert.
*/
public static function insert(...$args) 
{

    // Connect
    self::check_connection('write');

    // Check if table exists
    $table_name = array_shift($args);
    if (!self::check_table($table_name)) {
        throw new DBException('no_table', '', 'insert', $table_name); 
    }

    // Set variables
    $values = array();
    $placeholders = array();
    $columns = self::show_columns($table_name, true);

    // Generate SQL
    $sql = "INSERT INTO $table_name (" . implode(', ', array_keys($args[0])) . ") VALUES (";
    foreach ($args[0] as $column => $value) {

        // Check if column exists
        if (!isset($columns[$column])) { 
            throw new DBException('no_column', '', 'insert', $table_name, $column);
        }

        // Add variables to sql
        $placeholders[] = self::get_placeholder($columns[$column]);
        $values[] = $value;
    }
    $sql .= implode(", ", $placeholders) . ')';

    // Execute SQL
    self::query($sql, ...$values);

}

/**
* Updates the database via the 'UPDATE' command.
* 
*     @param string $table_name The table name to update.
*     @param array $data Array of key-value pairs containing the columns to update, and their values.
*     @param string $where_sql The WHERE clause of the update system with placeholders for sanitization.
*     @param array $vars The values of the placeholders within the previous parameter.
*/ 
public static function update(...$args) 
{

// Connect
    self::check_connection('write');

    // Set variables
    $table_name = array_shift($args);
    $updates = array_shift($args);

    // Check if table exists
    if (!self::check_table($table_name)) { 
        throw new DBException('no_table', '', 'update', $table_name);
    }

    // Set variables
    $values = array();
    $placeholders = array();
    $columns = self::show_columns($table_name, true);

    // Generate SQL
    $sql = "UPDATE $table_name SET ";
    foreach ($updates as $column => $value) { 

        // Ensure column exists in table
        if (!isset($columns[$column])) { 
            throw new DBException('no_column', '', 'update', $table_name, $column);
        }

        // Set SQL variables
        $placeholders[] = "$column = " . self::get_placeholder($columns[$column]);
        $values[] = $value;
    }

    // Finish SQL
    $sql .= implode(", ", $placeholders);
    if (isset($args[0]) && isset($args[1])) {
        $sql .= " WHERE " . array_shift($args);
    }

    // Execute  SQL
    self::query($sql, ...$values, ...$args);

}

/**
* Delete one or more rows from a table via the DELETE sataement.
* 
*     @param string $table_name The table name to delete rows from.
*     @param string $where_sql The WHERE clause of the delete statement with placeholders.
*     @param array $vars The values of the placeholders in the previous parameter.
*/ 
public static function delete(...$args) 
{

// Connect
    self::check_connection('write');

    $table_name = array_shift($args);

    // Check if table exists
    if (!self::check_table($table_name)) { 
        throw new DBException('no_table', '', 'delete', $table_name);
    }

    // Format SQL
    $sql = "DELETE FROM $table_name";
    if (isset($args[0]) && $args[0] != '') { 
        $sql .= ' WHERE ' . array_shift($args);
    }

    // Execute SQL
    self::query($sql, ...$args);

}

/**
* Gets a single row from the database, and if the SQL statement 
* matches more than one row, only returns the first row.
* 
*     @param string $sql The SELECT SQL statement to execute with placeholders.
*     @param array $vars The values of the placeholders in sequential order.
*     @return array Array of key-value pairs of the one row retrieved.  False if no row found.
*/
public static function get_row(...$args) 
{

// Connect
    self::check_connection('read');

    // Get first row
    $result = self::query(...$args);
    if (!$row = self::fetch_assoc($result)) { return false; }

    // Return
    return $row;

}

/**
* A short-hand version of the above 'get_row()' method, and used 
* if you're retrieving a specific row by the 'id' column.
* 
*     @param string $table_name The table name to retrive the row from.
*     @param string $id_number The value of the 'id' column to retrieve.
*     @return array Array containing key-value pairs of the row retrieved.
*/
public static function get_idrow($table_name, $id_number) 
{

// Connect
    self::check_connection('read');

    //Check table
    if (!self::check_table($table_name)) { 
        throw new DBException('not_table', '', 'select', $table_name);
    }

    // Get first row
    if (!$row = DB::get_row("SELECT * FROM $table_name WHERE id = %s ORDER BY id LIMIT 0,1", $id_number)) { 
        return false;
    }

    // Return
    return $row;

}

/**
* Retrieves a single column from a table, and returns a 
* one-dimensional array of the values.
* 
*     @param string $sql The SELECT SQL statement to execute with placeholders.
*     @param array $vars The values of the placeholders in sequential order.
*     @return array One-dimensional array containing the values of the column.
*/
public static function get_column(...$args) 
{

// Connect
    self::check_connection('read');

    // Get column
    $cvalues = array();
    $result = self::query(...$args);
    while ($row = self::fetch_array($result)) { 
        $cvalues[] = $row[0];
    }

// Return
    return $cvalues;

}

/**
* Retrieves two columns from a database (ie. 'id', and some other column), 
* and returns an array of key-value pairs of the results.
*     @param string $sql The SELECT SQL statement to execute with placeholders.
*     @param array $vars The values of the placeholders.
*     @return array Array of key-value pairs of the results.
*/
public static function get_hash(...$args) 
{

// Connect
    self::check_connection('read');

    // Get hash
    $vars = array();
    $result = self::query(...$args);
    while ($row = self::fetch_array($result)) { 
        $vars[$row[0]] = $row[1];
    }

// Return
    return $vars;

}

/**
* Gets a single column from a single row, and returns 
* the resulting scalar variable.
* 
*     @param string $sql The SELECT SQL statement to execute with placeholders.
*     @param array $vars The values of the placeholders in sequential order.
*     @return string The value of the column from the first row.  Returns false if no rows matched.
*/
public static function get_field(...$args) 
{

// Connect
    self::check_connection('read');

    // Execute SQL query
    $result = self::query(...$args);

    // Return result
    if (!$row = self::fetch_array($result)) { return false; }
    return $row[0];

}

/**
* Executes any SQL statement against the database, and returns 
* the result.
* 
*     @param string $sql The SQL statement to execute with placeholders.
*     @param array $vars The values of the placeholders in sequential order.
*     @return mixed The result of the query.
*/
public static function query(...$args) 
{

    // Check connection
    if (preg_match("/^SELECT/i", $args[0])) { $connect_type = 'read'; }
    elseif (preg_match("/^SHOW/", $args[0])) { $connect_type = 'read'; }
    elseif (preg_match("/^DESCRIBE/", $args[0])) { $connect_type = 'read'; }
    else { $connect_type = 'write'; }

    // Check connection
    self::check_connection($connect_type);

    //Format SQL
    list($hash, $bind_params, $values) = self::format_sql($args);

    // Bind params
    if (count($values) > 0) { 
        mysqli_stmt_bind_param(self::$prepared[$hash], $bind_params, ...$values);
    }

    // Execute SQL
    if (!mysqli_stmt_execute(self::$prepared[$hash])) { 
        throw new DBException('query', self::$raw_sql);
    }

    // Get result
    $result = mysqli_stmt_get_result(self::$prepared[$hash]);

    // Debug
    debug::add(3, fmsg("Executed SQL: {1}", self::$raw_sql), __FILE__, __LINE__);
    debug::add_sql(self::$raw_sql);

    // Return
    return $result;

}

/**
* The standard mysqli_fetch_array() function, except with error checking.
*/
public static function fetch_array($result) 
{

    // Get row
    if (!$row = mysqli_fetch_array($result)) {
        return false;
    }

    // Return
    return $row;

}

/**
* The standard mysqli_fetch_assoc() function except with error checking.
*/
public static function fetch_assoc($result) 
{

    // Get row
    if (!$row = mysqli_fetch_assoc($result)) {
        return false;
    }

    // Return
    return $row;

}

/**
* Returns the number of rows affected by the previous SQL statement.
*/
public static function num_rows($result) 
{

    // Get num rows
    if (!$num = mysqli_num_rows($result)) { 
        throw new DBException('num_rows');
    }
    if ($num == '') { $num = 0; }

    // Return
    return $num;

}

/**
* Returns the ID# of the previous INSERT statement.
*/
public static function insert_id() 
{

    // Get insert ID
    return mysqli_insert_id(self::$conn);

}

/**
* Formats the SQL by sanitizing the values passed as additional parameters, and 
* replacing the placeholders within the SQL statement with them.
*/
protected static function format_sql($args) 
{

    // Set variables
    $x=1; 
    $values = array();
    $bind_params = '';
    $raw_sql = $args[0];

    // Go through args
    preg_match_all("/\%(\w+)/", $args[0], $args_match, PREG_SET_ORDER);
    foreach ($args_match as $match) {
        $value = $args[$x] ?? '';

        // Check data type
        $is_valid = true;
        if ($match[1] == 'i' && $value != '0' && !filter_var($value, FILTER_VALIDATE_INT)) { $is_valid = false; }
        //elseif ($match[1] == 'd' && !filter_var($value, FILTER_VALIDATE_FLOAT)) { $is_valid = false; }
        elseif ($match[1] == 'b' && $value != 0 && $value != 1) { $is_valid = false; }
        elseif ($match[1] == 'e' && !filter_var($value, FILTER_VALIDATE_EMAIL)) { $is_valid = false; }
        elseif ($match[1] == 'url' && !filter_var($value, FILTER_VALIDATE_URL)) { $is_valid = false; }
        elseif ($match[1] == 'ds') { 
            if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)$/", $value, $dmatch)) { 
                if (!check_date($dmatch[2], $dmatch[3], $dmatch[1])) { $is_valid = false; }
            } else { $is_valid = false; }
        } elseif ($match[1] == 'ts' && !preg_match("/^\d\d:\d\d:\d\d$/", $value)) { $is_valid = false; }
        elseif ($match[1] == 'dt') { 
            if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d) \d\d:\d\d:\d\d$/", $value, $dmatch)) { 
                if (!check_date($dmatch[2], $dmatch[3], $dmatch[1])) { $is_valid = false; }
            } else { $is_valid = false; }
        } 
        

        // Process invalid argument, if needed
        if ($is_valid === false) {
            throw new DBException('invalid_variable', $args[0], '', '', '', $match[1], $value); 
        }

        // Add bind_param
        if ($match[1] == 'i' || $match[1] == 'b') { $bind_params .= 'i'; }
        elseif ($match[1] == 'd') { $bind_params .= 'd'; }
        elseif ($match[1] == 'blobl') { $bind_params .= 'b'; }
        else { $bind_params .= 's'; }

        // Format value
        if ($match[1] == 'ls') { $value = '%' . $value . '%'; }
        $values[] = $value;

        // Replace placeholder in SQL
        $args[0] = preg_replace("/$match[0]/", '?', $args[0], 1);
        $raw_sql = preg_replace("/$match[0]/", "'" . mysqli_real_escape_string(self::$conn, $value) . "'", $raw_sql, 1);

    $x++; }

    // Check for prepared statement
    $hash = 's' . crc32($args[0]);
    if (!isset(self::$prepared[$hash])) { 
        if (!self::$prepared[$hash] = mysqli_prepare(self::$conn, $args[0])) { 
            throw new DBException('query', $raw_sql);
        }
    }
    self::$raw_sql = $raw_sql;

    // Return
    return array($hash, $bind_params, $values);

}
/**
* Checks to see whether or not a table exists within the database.
* 
*     @param string $table_name The table name to check.
*     @return bool WHether or not the table exists in the database.
*/
public static function check_table($table_name)
{

    // Get table names
    $tables = self::show_tables();
    $ok = in_array($table_name, $tables) ? true : false;

    // Return
    return $ok;

}

/**
* Begins a new transaction within the database, meaning no further SQL 
* queries will be executed against the database until a COMMIT is executed.
*/
public static function begin_transaction() 
{

    // Begin transaction
    if (!mysqli_begin_transaction(self::$conn)) { 
        throw new DBException('begin_transaction');
    }

    //EwReturn
    return true;

} 

/**
* Commits a transaction, meaning any SQL queries that were executed after the 
* transaction will now be written to the database.
*/
public static function commit() 
{

    // Commit transaction
    if (!mysqli_commit(self::$conn)) { 
        throw new DBException('commit');
    }

    //EwReturn
    return true;

} 

/**
* Performs a rollback on the previously started transaction, 
* meaning none of the SQL statements executed since the transaction began 
* will be applied to the database.
*/
public static function rollback() 
{

    // Rollback transaction
    if (!mysqli_rollback(self::$conn)) { 
        throw new DBException('rollback');
    }

    //EwReturnr
    return true;
 
}

/**
* Get placeholder based on column type
*     @param string $col_type The type of column
*/
private static function get_placeholder(string $col_type)
{

    // Get placeholder
    if (strtolower($col_type) == 'tinyint(1)') { $type = '%b'; }
    elseif (preg_match("/int\(/i", $col_type)) { $type = '%i'; }
    elseif (preg_match("/decimal/i", $col_type)) { $type = '%d'; }
    elseif (strtolower($col_type) == 'blob') { $type = '%blob'; }
    else { $type = '%s'; }

    // Return
    return $type;

}

}

