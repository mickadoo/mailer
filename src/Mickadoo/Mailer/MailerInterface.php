<?php

namespace Mickadoo\Mailer;

interface MailerInterface
{
    /**
     * @param string $recipient
     * @param string $subject
     * @param string $body
     */
    public function send($recipient, $subject, $body);
}
