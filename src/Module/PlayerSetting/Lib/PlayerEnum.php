<?php

namespace Stu\Module\PlayerSetting\Lib;

final class PlayerEnum
{

    public const USER_NEW = 0;
    public const USER_ACTIVE = 1;
    public const USER_LOCKED = 4;

    //DELMARK
    public const DELETION_REQUESTED = 1;
    public const DELETION_CONFIRMED = 2;
    public const DELETION_FORBIDDEN = 3;
}
