<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Application\Application;
use Mickadoo\Mailer\Exception\MailerException;

class HandlerFactory
{
    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->handlers[] = new NamedRecipientMailHandler(
            $application->getMailContentGenerator(),
            $application->getMailer()
        );

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
        throw new MailerException("No handlers registered for that");
    }
}
