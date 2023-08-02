<?php

namespace SequentSoft\ThreadFlow\Session;

use InvalidArgumentException;
use Closure;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Config\ConfigInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionStoreInterface;

class ArraySessionStore implements SessionStoreInterface
{
    public function __construct(
        protected string $channelName,
        protected ArraySessionStoreStorage $storage,
    ) {
    }

    public function load(
        MessageContextInterface $context
    ): SessionInterface {
        $key = $this->makeKeyString($this->channelName, $context);

        /** @var SessionInterface|null $session */
        $session = $this->storage->load($key);

        if (! is_null($session)) {
            $session = new Session(
                $session->getData(),
                $session->getPageState(),
                $session->getBackgroundPageStates(),
                $session->getBreadcrumbs(),
            );
        } else {
            $session = new Session();
        }
        $session->setSaveCallback(fn(SessionInterface $session) => $this->save(
            $context,
            $session,
        ));

        return $session;
    }

    protected function makeKeyString(string $channelName, MessageContextInterface $context): string
    {
        return $channelName . ':' . $context->getRoom()->getId();
    }

    public function save(MessageContextInterface $context, SessionInterface $session): void
    {
        $key = $this->makeKeyString($this->channelName, $context);

        $this->storage->store($key, $session);
    }
}
