<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Mailer\MailerInterface;
use Mickadoo\Mailer\Service\MailContentGenerator;
use Mickadoo\Mailer\Service\UserService;

class UuidOnlyMailHandler extends AbstractMailHandler
{
    /**
     * @var UserService
     */
    protected $finder;

    /**
     * @param MailContentGenerator $contentGenerator
     * @param MailerInterface      $mailer
     * @param UserService          $finder
     */
    public function __construct(
        MailContentGenerator $contentGenerator,
        MailerInterface $mailer,
        UserService $finder
    ) {
        $this->finder = $finder;
        parent::__construct($contentGenerator, $mailer);
    }

    /**
     * @param array $message
     */
    public function handle(array $message)
    {
        $user = $this->finder->findByUuid($this->getUuid($message));

        if (!$user) {
            echo 'no user found with id ' . $this->getUuid($message);
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
        return null !== $this->getUuid($message);
    }

    /**
     * @param $message
     *
     * @return null|string
     */
    private function getUuid($message)
    {
        if (isset($message['user']['_id'])) {
            return $message['user']['_id'];
        }

        if (isset($message['data']['user']['uuid'])) {
            return $message['data']['user']['uuid'];
        }

        return null;
    }
}
