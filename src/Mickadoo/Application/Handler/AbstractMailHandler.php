<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Mailer\MailerInterface;
use Mickadoo\Mailer\Service\MailContentGenerator;

abstract class AbstractMailHandler implements HandlerInterface
{
    /**
     * @var MailContentGenerator
     */
    protected $contentGenerator;

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @param MailContentGenerator $contentGenerator
     * @param MailerInterface      $mailer
     */
    public function __construct(
        MailContentGenerator $contentGenerator,
        MailerInterface $mailer
    ) {
        $this->contentGenerator = $contentGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @param string $recipient
     * @param string $type
     * @param string $locale
     * @param array  $data
     */
    protected function sendMail(
        string $recipient,
        string $type,
        string $locale,
        array $data
    ) {
        $body = $this->contentGenerator->getBody($type, $data, $locale);
        $subject = $this->contentGenerator->getSubject($type, $data, $locale);
        $this->mailer->send($recipient, $subject, $body);
    }
}
