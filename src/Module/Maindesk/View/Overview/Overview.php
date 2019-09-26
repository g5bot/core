<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Module\Communication\Lib\KnTalFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $historyRepository;

    private $allianceBoardTopicRepository;

    private $userProfileVisitorRepository;

    private $knPostRepository;

    private $knTalFactory;

    private $colonyShipQueueRepository;

    private $userRepository;

    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        KnPostRepositoryInterface $knPostRepository,
        KnTalFactoryInterface $knTalFactory,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->historyRepository = $historyRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->knPostRepository = $knPostRepository;
        $this->knTalFactory = $knTalFactory;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $list = [];

        foreach ($this->knPostRepository->getNewerThenMark((int) $user->getKNMark()) as $post) {
            $list[] = $this->knTalFactory->createKnPostTal($post, $user);
        }

        $game->appendNavigationPart(
            'maindesk.php',
            _('Maindesk')
        );
        $game->setPageTitle(_('/ Maindesk'));
        $game->setTemplateFile('html/maindesk.xhtml');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            (int)$user->getActive() === 1
        );
        $game->setTemplateVar('NEW_KN_POSTINGS', $list);
        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            $this->knPostRepository->getAmountSince((int) $user->getKNMark())
        );
        $game->setTemplateVar(
            'RECENT_PROFILE_VISITORS',
            $this->userProfileVisitorRepository->getRecent($userId)
        );
        $game->setTemplateVar(
            'RANDOM_ONLINE_USER',
            $this->userRepository->getOrderedByLastaction(15, $userId, time() - GameEnum::USER_ONLINE_PERIOD)
        );
        $game->setTemplateVar(
            'SHIP_BUILD_PROGRESS',
            $this->colonyShipQueueRepository->getByUser($userId)
        );
        $game->setTemplateVar(
            'RECENT_ALLIANCE_BOARD_TOPICS',
            $this->allianceBoardTopicRepository->getRecentByAlliance((int) $user->getAllianceId())
        );
        $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());
    }
}
