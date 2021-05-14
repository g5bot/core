<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonyResetter implements ColonyResetterInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private FleetRepositoryInterface $fleetRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        FleetRepositoryInterface $fleetRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->fleetRepository = $fleetRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function reset(
        ColonyInterface $colony
    ): void {
        $this->colonyLibFactory->createColonySurface($colony)->updateSurface();

        $this->resetBlockers($colony);
        $this->resetDefenders($colony);

        $colony->setEps(0)
            ->setMaxEps(0)
            ->setMaxStorage(0)
            ->setWorkers(0)
            ->setWorkless(0)
            ->setMaxBev(0)
            ->setImmigrationstate(true)
            ->setPopulationlimit(0)
            ->setUser($this->userRepository->find(GameEnum::USER_NOONE))
            ->setName('');

        $this->colonyRepository->save($colony);

        $this->colonyStorageRepository->truncateByColony($colony);

        foreach ($this->colonyTerraformingRepository->getByColony([$colony]) as $fieldTerraforming) {
            $this->colonyTerraformingRepository->delete($fieldTerraforming);
        }

        $this->colonyShipQueueRepository->truncateByColony($colony);
        $this->planetFieldRepository->truncateByColony($colony);
    }

    private function resetBlockers(ColonyInterface $colony): void
    {
        foreach ($colony->getBlockers() as $blockerFleet) {
            $this->sendMessage($colony, $blockerFleet, false);
            $blockerFleet->setBlockedColony(null);
            $this->fleetRepository->save($blockerFleet);
        }
        $colony->getBlockers()->clear();
    }

    private function resetDefenders(ColonyInterface $colony): void
    {
        foreach ($colony->getDefenders() as $defenderFleet) {
            $this->sendMessage($colony, $defenderFleet, true);
            $defenderFleet->setDefendedColony(null);
            $this->fleetRepository->save($defenderFleet);
        }
        $colony->getDefenders()->clear();
    }

    private function sendMessage(ColonyInterface $colony, FleetInterface $fleet, bool $isDefending): void
    {
        $txt = sprintf(
            'Der Spieler %s hat die Kolonie %s in Sektor %d|%d (%s System) verlassen. Deine Flotte %s hat die %s beendet.',
            $colony->getUser()->getName(),
            $colony->getName(),
            $colony->getSx(),
            $colony->getSy(),
            $colony->getSystem()->getName(),
            $fleet->getName(),
            $isDefending ? 'Verteidigung' : 'Blockade'
        );

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $fleet->getUserId(),
            $txt,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }
}
