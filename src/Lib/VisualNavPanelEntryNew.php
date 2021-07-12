<?php

declare(strict_types=1);

use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;

class VisualNavPanelEntryNew
{

    private $field;

    private bool $isSystem;

    private $isTachyonSystemActive;

    private $tachyonFresh;

    function __construct($field, bool $isSystem, bool $isTachyonSystemActive = false, bool $tachyonFresh = false)
    {
        $this->field = $field;
        $this->isSystem = $isSystem;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
    }

    function getPosX()
    {
        return $this->isSystem ? $this->field->getSx() : $this->field->getCx();
    }

    function getPosY()
    {
        return $this->isSystem ? $this->field->getSy() : $this->field->getCy();
    }

    function getMapfieldType()
    {
        return $this->field->getFieldType()->getType();
    }

    function getShipCount()
    {
        return $this->field->getShips()->count();
    }

    function hasCloakedShips()
    {
        return count(array_filter(
            $this->field->getShips()->toArray(),
            function (ShipInterface $ship): bool {
                return $ship->getCloakState();
            }
        ));
    }

    function hasShips()
    {
        return $this->getShipCount() > 0;
    }

    function getSubspaceCode()
    {
        $directionArray = [1 => [], 2 => [], 3 => [], 4 => []];

        foreach ($this->field->getSignatures() as $sig) {
            $shipId = $sig->getShip()->getId();

            foreach (ShipEnum::DIRECTION_ARRAY as $direction) {
                if (($sig->getFromDirection() == $direction || $sig->getToDirection() == $direction)
                    && !in_array($shipId, $directionArray[$direction])
                ) {
                    $directionArray[$direction][] = $shipId;
                }
            }
        }

        $code = sprintf(
            '%d%d%d%d',
            $this->getCode(count($directionArray[1])),
            $this->getCode(count($directionArray[2])),
            $this->getCode(count($directionArray[3])),
            $this->getCode(count($directionArray[4])),
        );
        return $code == '0000' ? null : $code;
    }

    private function getCode(int $shipCount): int
    {
        if ($shipCount == 0) {
            return 0;
        }
        if ($shipCount == 1) {
            return 1;
        }
        if ($shipCount < 6) {
            return 2;
        }
        if ($shipCount < 11) {
            return 3;
        }
        if ($shipCount < 21) {
            return 4;
        }

        return 5;
    }

    function getDisplayCount()
    {
        if ($this->hasShips()) {
            return $this->getShipCount();
        }
        if ($this->hasCloakedShips()) {
            if ($this->tachyonFresh) {
                return "?";
            }
            if (
                $this->isTachyonSystemActive
                && abs($this->getPosX() - $this->currentShipPosX) < 3
                && abs($this->getPosY() - $this->currentShipPosY) < 3
            ) {
                return "?";
            }
        }
        return "";
    }

    function getCacheValue()
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getDisplayCount() . "_" . $this->isClickAble();
    }

    public $currentShipPosX = null;
    public $currentShipPosY = null;

    function isCurrentShipPosition()
    {
        if ($this->getPosX() == $this->currentShipPosX && $this->getPosY() == $this->currentShipPosY) {
            return true;
        }
        return false;
    }

    function getBorder()
    {
        return $this->data['color'];
    }

    private $cssClass = 'lss';

    function setCSSClass($class)
    {
        $this->cssClass = $class;
    }

    function getCSSClass()
    {
        if (!$this->getRow() && $this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return $this->cssClass;
    }

    function isClickAble()
    {
        if (!$this->isCurrentShipPosition() && ($this->getPosX() == $this->currentShipPosX || $this->getPosY() == $this->currentShipPosY)) {
            return true;
        }
        return false;
    }

    function getRow()
    {
        return $this->row;
    }
}
