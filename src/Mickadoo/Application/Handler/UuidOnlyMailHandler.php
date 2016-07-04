<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Mailer\MailerInterface;
use Mickadoo\Mailer\Service\MailContentGenerator;
use Mickadoo\Mailer\Service\UserFinder;

class UuidOnlyMailHandler extends AbstractMailHandler
{
    /**
     * @var UserFinder
     */
    protected $finder;

    /**
     * @param MailContentGenerator $contentGenerator
     * @param MailerInterface      $mailer
     * @param UserFinder           $finder
     */
    public function __construct(
        MailContentGenerator $contentGenerator,
        MailerInterface $mailer,
        UserFinder $finder
    ) {
        $this->finder = $finder;
        parent::__construct($contentGenerator, $mailer);
    }

    /**
     * @param array $message
     */
    public function handle(array $message)
    {
        $user = $this->finder->findByUuid($message['user']['_id']);

        if (!$user) {
            echo 'no user found with that id';
            return;
        }

        $recipient = $user['email'];
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
        return isset($message['user']['_id']);
    }
}
