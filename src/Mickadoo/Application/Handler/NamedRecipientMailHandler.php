<?php

namespace Mickadoo\Application\Handler;

class NamedRecipientMailHandler extends AbstractMailHandler
{
    /**
     * @param array $message
     */
    public function handle(array $message)
    {
        $recipient = $message['recipient'];
        $type = $message['type'];
        $data = $message['data'] ?? [];
        $locale = 'en_US';

        $this->sendMail($recipient, $type, $locale, $data);
    }

    /**
     * @param array $message
     *
     * @return bool
     */
    public function canHandle(array $message) : bool
    {
        // todo also check for available mails
        return isset($message['recipient']);
    }
}
