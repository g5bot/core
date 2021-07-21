<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperShield extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            current($this->modules)->getModule(),
            'getBaseShield',
            $this->rump->getBaseShield()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setMaxShield($this->getValue());
    }
}
