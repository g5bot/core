<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class PrivateMessageListItem implements PrivateMessageListItemInterface
{
    private $privateMessageRepository;

    private $contactRepository;

    private $ignoreListRepository;

    private $message;

    private $currentUserId;

    private $sender;

    private $senderignore;

    private $sendercontact;

    public function __construct(
        PrivateMessageRepositoryInterface $privateMessageRepository,
        ContactRepositoryInterface $contactRepository,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageInterface $message,
        int $currentUserId
    ) {
        $this->privateMessageRepository = $privateMessageRepository;
        $this->contactRepository = $contactRepository;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->message = $message;
        $this->currentUserId = $currentUserId;
    }

    public function getSender(): UserInterface
    {
        if ($this->sender === null) {
            $this->sender = $this->message->getSender();
        }
        return $this->sender;
    }

    public function getDate(): int
    {
        return $this->message->getDate();
    }

    public function isMarkableAsNew(): bool
    {
        if ($this->message->getNew() === false) {
            return false;
        }
        $this->message->setNew(false);

        $this->privateMessageRepository->save($this->message);

        return true;
    }

    public function getText(): string
    {
        return $this->message->getText();
    }

    public function getNew(): bool
    {
        return $this->message->getNew();
    }

    public function getId(): int
    {
        return $this->message->getId();
    }

    public function displayUserLinks(): bool
    {
        return $this->getSender() && $this->getSender()->getId() !== GameEnum::USER_NOONE;
    }

    public function getReplied(): bool
    {
        return $this->message->getReplied();
    }

    public function senderIsIgnored(): bool
    {
        if ($this->senderignore === null) {
            $this->senderignore = $this->ignoreListRepository->exists(
                $this->currentUserId,
                $this->message->getSenderId()
            );
        }
        return $this->senderignore;
    }

    public function senderIsContact(): ?ContactInterface
    {
        if ($this->sendercontact === null) {
            $this->sendercontact = $this->contactRepository->getByUserAndOpponent(
                $this->currentUserId,
                $this->message->getSenderId()
            );
        }
        return $this->sendercontact;
    }
}
