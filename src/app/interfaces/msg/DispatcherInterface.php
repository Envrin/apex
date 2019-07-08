<?php
declare(strict_types=1);

namespace apex\app\interfaces\msg;

use apex\app\interfaces\msg\EventMessageinterface;


/**
 * Defines a dispatcher for events.
 */
interface DispatcherInterface
{
    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(EventMessageInterface $msg);
}
