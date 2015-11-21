<?php

namespace Mickadoo\Mailer;

use Mickadoo\Mailer\Exception\MailerException;
use Psr\Log\LoggerInterface;

class SwiftMailer implements MailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $swiftMailer;

    /**
     * @var array
     */
    protected $mailConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Swift_Mailer $swiftMailer
     * @param array $mailConfig
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $swiftMailer, array $mailConfig, LoggerInterface $logger)
    {
        $this->swiftMailer = $swiftMailer;
        $this->mailConfig = $mailConfig;
        $this->logger = $logger;
    }

    /**
     * @param string $recipient
     * @param string $subject
     * @param string $body
     * @throws MailerException
     */
    public function send($recipient, $subject, $body)
    {
        // todo remove
        $recipient = 'michaeldevery@gmail.com';
        $message = $this->createMessage($body, $subject);
        $message->setTo([$recipient]);

        if (!$this->swiftMailer->send($message, $failures)) {
            $this->logger->error(sprintf("Message sending to %s with subject '%s' failed", $recipient, $subject));
            throw new MailerException('The mail sent to fail for a mysterious reason');
        }

        $this->logger->info(sprintf("Sent message to %s with subject '%s'", $recipient, $subject));
    }

    /**
     * @param $body
     * @param $subject
     * @return \Swift_Mime_MimePart|\Swift_Message
     */
    private function createMessage($body, $subject)
    {
        /** @var string $from */
        $from = $this->mailConfig['from'];
        $fromName = $this->mailConfig['from_name'];

        return \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($from => $fromName))
            ->setBody($body, 'text/html');
    }
}
