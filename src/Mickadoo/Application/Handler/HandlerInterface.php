<?php

namespace Mickadoo\Application\Handler;

interface HandlerInterface
{
    /**
     * @param array $message
     */
    public function handle(array $message);

    /**
     * @param array $message
     *
     * @return bool
     */
    public function canHandle(array $message) : bool;
}
