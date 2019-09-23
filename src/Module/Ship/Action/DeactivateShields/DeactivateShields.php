<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateShields;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DeactivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_SHIELDS';

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

        $ship->setShieldState(false);

        $this->shipRepository->save($ship);

        $game->addInformation("Schilde deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
