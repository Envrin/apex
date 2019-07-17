<?php

namespace apex\app\interfaces;


/** 
 * Debugger interface.
 */
interface DebuggerInterface {

/**
 * Add entry to debug session 
 *
 * @param int $level Number beterrn 1 -53 defining the level of entry.
 * @param string $message The message to add
 * @param string $file File number (__FILE__)
 * @param int $line_number The line number of call (__LINE__)
 * @param string $log_level Optional, and will add appropriate log item via logger if not debug.
 * @param int $is_system Defaults to 0, and used by internal error handlers to specify this as coming from PHP interpreter.
 */
public function add(int $level, string $message, string $file = '', int $line_number = 0, $log_level = 'debug', $is_system = 0);


/**
 * Finish session.
 *
 * Finish the session, compileall notes and data gatherered during request, 
 * and put them into redis for later display.  This is executed by the registry 
 * class at the end of each request.
 */
public function finish_session();

}


