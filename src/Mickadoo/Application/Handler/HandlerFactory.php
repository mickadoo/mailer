<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Application\Application;
use Mickadoo\Mailer\Exception\MailerException;
use Psr\Log\LoggerInterface;

class HandlerFactory
{
    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->handlers[] = new NamedRecipientMailHandler(
            $application->getMailContentGenerator(),
            $application->getMailer()
        );

        $this->logger = $application['logger'];

        $this->handlers[] = new UuidOnlyMailHandler(
            $application->getMailContentGenerator(),
            $application->getMailer(),
            $application->getUserFinder()
        );

        $this->handlers[] = new EmailChangedEventHandler(
            $application->getUserFinder()
        );
    }

    /**
     * @param $message
     *
     * @return HandlerInterface
     * @throws MailerException
     */
    public function getHandlerForMessage($message) : HandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($message)) {
                return $handler;
            }
        }

        // todo should this be logging only?
        echo 'failed to handle message';
        $this->logger->error("Could not handle: " . print_r($message, true));

        return new BlackHoleHandler();
    }
}
