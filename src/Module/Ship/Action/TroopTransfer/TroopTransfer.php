<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TroopTransfer;

use request;

use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TroopTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TROOP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TroopTransferUtilityInterface $transferUtility;
    
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private CrewRepositoryInterface $crewRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        TroopTransferUtilityInterface $transferUtility,
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->transferUtility = $transferUtility;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            $game->addInformation(_("Die Truppenquartiere sind zerstört"));
            return;
        }
        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        
        $isColony = request::has('isColony');
        $isUnload = request::has('isUnload');
        
        if ($isColony)
        {
            $target = $this->colonyRepository->find((int)request::postIntFatal('target'));
        } else {
            $target = $this->shipRepository->find((int)request::postIntFatal('target'));
        }
        
        
        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target, $isColony, !$isColony)) {
            return;
        }
        if (!$isColony && $target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        $requestedTransferCount = request::postInt('tcount');
        
        $transferAmount = $ship->getBeamFactor();
        
        if (ceil($requestedTransferCount / $transferAmount) > $ship->getEps()) {
            $requestedTransferCount = $ship->getEps() * $transferAmount;
        }

        $shipCrew = $ship->getCrewCount();
        
        if ($isColony)
        {
            if ($isUnload)
            {
                $amount = min($requestedTransferCount, $this->transferUtility->getBeamableTroopCount($ship));
                
                $array = $ship->getCrewlist()->getValues();
                
                for ($i = 0; $i < $amount; $i++) {
                    $this->shipCrewRepository->delete($array[$i]);
                    $shipCrew--;
                }
            }
            else {
                $amount = min($requestedTransferCount, min($ship->getUser()->getFreeCrewCount(),
                $this->transferUtility->getFreeQuarters($ship)));
                
                for ($i = 0; $i < $amount; $i++) {
                    $crew = $this->crewRepository->getFreeByUser($userId);

                    $sc = $this->shipCrewRepository->prototype();
                    $sc->setCrew($crew);
                    $sc->setShip($ship);
                    $sc->setUser($ship->getUser());
                    $sc->setSlot(CrewEnum::CREW_TYPE_CREWMAN);
    
                    $this->shipCrewRepository->save($sc);
                    $shipCrew++;
                }
            }
            
        }
        else
        {
            if ($isUnload)
            {
                $amount = min($requestedTransferCount, min($this->transferUtility->getBeamableTroopCount($ship),
                            $this->transferUtility->getFreeQuarters($target)));

                $array = $ship->getCrewlist()->getValues();

                for ($i = 0; $i < $amount; $i++) {
                    $sc = $array[$i];
                    $sc->setShip($target);
                    $this->shipCrewRepository->save($sc);
                    $shipCrew--;
                }
            }
            else {
                $amount = min($requestedTransferCount, min($target->getCrewCount(),
                            $this->transferUtility->getFreeQuarters($ship)));

                $array = $target->getCrewlist()->getValues();

                for ($i = 0; $i < $amount; $i++) {
                    $sc = $array[$i];
                    $sc->setShip($ship);
                    $this->shipCrewRepository->save($sc);
                    $shipCrew++;
                }
            }
            
        }
        
        $this->shipRepository->save($ship);

        if ($shipCrew > $ship->getBuildplan()->getCrew())
        {
            $this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Crewman %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        if ($shipCrew <= $ship->getBuildplan()->getCrew())
        {
            $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }

    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
