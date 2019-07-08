<?php
declare(strict_types = 1);

namespace apex\services;

use apex\app;
use apex\app\exceptions\ServiceException;


/**
 * Debugger service / dispatcher.  Object passed as the singleton must 
 * implement apex\app\interfaces\DebuggerInterface 
 */
class debug
{

    private static $service = 'debug';
    private static $app = null;
    private static $instance = null;


    /**
     * Set the app instance.
     */
    public static function set_app($obj)
    {
        self::$app = $obj;
    }

    /**
     * Sets / returns the instance for this service / dispatcher.
     */
    public static function singleton($object = null)
{

        // Define instance, if needed
        if ($object !== null && !self::$instance) { 
            self::$instance = $object;
        }

        // Return
        return self::$instance;
    }

    /**
     * Calls a method of the instance.
     */
    public static function __callstatic($method, $params) 
    {

        // Ensure we have an instance defined
        if (!self::$instance) { 
            if (!self::$app) { self::$app = app::get_instance(); }
            self::$app->assign_service(self::$service);
        }

        // Ensure method exists
        if (!method_exists(self::$instance, $method)) { 
            throw new ServiceException('no_method', self::$service, $method);
        }

        // Call method, and return 
        return self::$instance->$method(...$params);
    }

}

