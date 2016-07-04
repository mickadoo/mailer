<?php

namespace Mickadoo\Application\Handler;

use Mickadoo\Mailer\Service\UserService;

class EmailChangedEventHandler implements HandlerInterface
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param array $message
     *
     * @throws \Exception
     */
    public function handle(array $message)
    {
        $data = $message['data'];

        $uuid = $data['user']['_id'];
        $email = $data['user']['email'];

        $this->userService->updateOrCreate($uuid, $email);
    }

    /**
     * @param array $message
     *
     * @return bool
     */
    public function canHandle(array $message) : bool
    {
        $event = $message['event'] ?? null;

        return $event === 'email.changed';
    }
}
