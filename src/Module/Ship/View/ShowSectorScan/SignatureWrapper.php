<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use Stu\Module\Tal\TalHelper;

class SignatureWrapper
{

    private $signature = null;

    function __construct($signature)
    {
        $this->signature = $signature;
    }

    function getRump()
    {
        if ($this->signature->isCloaked()) {
            if ($this->signature->getTime() > (time() - 21600)) {
                return $this->signature->getRump();
            } else {
                return null;
            }
        }
        if ($this->signature->getTime() > (time() - 43200)) {
            return $this->signature->getRump();
        } else {
            return null;
        }
    }

    function getShipName()
    {
        if ($this->signature->isCloaked()) {
            return null;
        }
        if ($this->signature->getTime() > (time() - 21600)) {
            return $this->signature->getShipName();
        } else {
            return null;
        }
    }

    function getAge()
    {
        return TalHelper::formatSeconds(strval(time() - $this->signature->getTime()));
    }
}
