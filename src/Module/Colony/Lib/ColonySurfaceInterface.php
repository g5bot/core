<?php

namespace Stu\Module\Colony\Lib;

interface ColonySurfaceInterface
{
    public function getSurface(): array;

    public function getSurfaceTileCssClass(): string;

    public function getEpsBoxTitleString(): string;

    public function getPositiveEffectPrimaryDescription(): string;

    public function getPositiveEffectSecondaryDescription(): string;

    public function getNegativeEffectDescription(): string;

    public function getStorageSumPercent(): float;

    public function updateSurface(): array;

    public function getProductionSumClass(): string;

    public function hasShipyard(): bool;

    public function hasModuleFab(): bool;

    public function hasAirfield(): bool;

    public function getDayNightState(): string;
}