<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateCloak;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class ActivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_CLOAK';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->getShieldState()) {
            $ship->setShieldState(false);
            $game->addInformation("Schilde deaktiviert");
        }
        if ($ship->getDockedTo()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        $ship->setCloakState(true);

        $this->shipRepository->save($ship);

        $game->addInformation("Tarnung aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
