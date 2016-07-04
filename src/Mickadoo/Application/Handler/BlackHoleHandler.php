<?php

namespace Mickadoo\Application\Handler;

class BlackHoleHandler implements HandlerInterface
{
    /**
     * @param array $message
     */
    public function handle(array $message)
    {
        // do nothing
    }

    /**
     * @param array $message
     *
     * @return bool
     */
    public function canHandle(array $message) : bool
    {
        // never called
        return false;
    }
}
