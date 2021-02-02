<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

class DatabaseTopListDiscover extends DatabaseTopList
{

    private $points = null;

    function __construct($entry)
    {
        parent::__construct($entry['user_id']);
        $this->points = $entry['points'];
    }

    function getPoints()
    {
        return $this->points;
    }
}
