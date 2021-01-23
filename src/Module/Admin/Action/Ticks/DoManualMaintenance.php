<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maintenance\DatabaseBackup;
use Stu\Module\Tick\Maintenance\Maintenance;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class DoManualMaintenance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MAINTENANCE';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        global $container;

        $maintenance = new Maintenance(
            $container->get(GameConfigRepositoryInterface::class),
            array_filter(
                $container->get('maintenance_handler'),
                function ($key): bool {
                    return $key != DatabaseBackup::class;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
        $maintenance->handle();

        $game->addInformation("Der Wartungs-Tick wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
