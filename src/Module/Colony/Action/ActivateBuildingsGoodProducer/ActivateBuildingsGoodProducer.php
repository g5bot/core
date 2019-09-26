<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuildingsGoodProducer;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ActivateBuildingsGoodProducer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ACTIVATE_GOODRELATED_PROD';

    private $colonyLoader;

    private $buildingAction;

    private $commodityRepository;

    private $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingActionInterface $buildingAction,
        CommodityRepositoryInterface $commodityRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingAction = $buildingAction;
        $this->commodityRepository = $commodityRepository;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $colonyId = (int) $colony->getId();

        $goodId = (int) request::postIntFatal('good');

        $fields = $this->planetFieldRepository->getCommodityProducingByColonyAndCommodity($colonyId, $goodId);

        foreach ($fields as $field) {
            $this->buildingAction->activate($colony, $field, $game);
        }

        $list = $this->planetFieldRepository->getByColonyWithBuilding($colonyId);

        usort($list, 'compareBuildings');

        $game->setTemplateVar('BUILDING_LIST', $list);
        $game->setTemplateVar('USEABLE_GOOD_LIST', $this->commodityRepository->getByBuildingsOnColony($colonyId));

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => ColonyEnum::MENU_BUILDINGS]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
