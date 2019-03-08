<?php
declare(strict_types = 1);

namespace apex;


/**
* Handles all database related errors including 
* connection, SQL query, and formatting errors.
*/
class DBException extends ApexException
{

    // Set error codes
    private $error_codes = array(
        'no_connect' => "Unable to connect to the mySQL database using the supplied information.  Please ensure the mySQL server is running, and the right connection information is within settings.  If needed, you can update connection information by running 'php apex.php update_master' at the terminal", 
        'no_table' => "Unable to perform {action} on table name {table}, as table does not exist within the database.", 
        'no_column' => "Unable to perform {action} on column {column} within the table {table}, as column does not exist within the table.", 
        'num_rows' => "Unable to determine number of affected rows", 
        'insert_id' => "Unable to determine ID# of last insert", 
        'begin_transaction' => "Unable to begin transaction", 
        'commit' => "Unable to commit transaction", 
        'rollback' => "Unable to rollback transaction", 
        'query' => "Unable to execute SQL statement: {sql}", 
        'invalid_variable' => "Invalid variable passed, is not a {type} as expected: {value}"
    );

    // Placeholder types
    private $placeholder_types = array(
        's' => 'string', 
        'i' => 'integer', 
        'd' => 'decimal', 
        'b' => 'boolean', 
        'e' => 'email address', 
        'url' => 'URL', 
        'ds' => 'date', 
        'ts' => 'time', 
        'dt' => 'datetime'
    );
public function __construct(string $message, $sql_query = '', $action = '', $table_name = '', $column_name = '', $var_type = 's', $value = '')
{

    // Set vars
    $vars = array(
        'action' => strtoupper($action), 
        'table' => $table_name, 
        'column' => $column_name, 
        'sql' => $sql_query, 
        'type' => $this->placeholder_types[$var_type], 
        'value' => $value
    );

    // Set variables
    $this->is_generic = 1;
    $this->log_level = 'error';

    // Get message
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

    // Set SQL query
    $this->sql_query = $sql_query == '' ? $this->message : $sql_query;

    // Finish message
    $this->message = "DB Error: " . $this->message . '  <br /><br />(' . mysqli_error(DB::$conn) . ')';

}

}


