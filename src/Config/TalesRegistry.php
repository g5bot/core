<?php

declare(strict_types=1);

namespace Stu\Config;

use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;

TalesRegistry::registerPrefix(
    'clmodeDescription',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::getContactListModeDescription((int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'addPlusCharacter',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::addPlusCharacter((int)' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'isPositive',
    function ($src, $nothrow): string {
        return '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' > 0';
    }
);
TalesRegistry::registerPrefix(
    'isNegative',
    function ($src, $nothrow): string {
        return '(int) ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ' < 0';
    }
);

TalesRegistry::registerPrefix(
    'bbcode',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsHtml()';
    }
);
TalesRegistry::registerPrefix(
    'bbcode2txt',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::getBBCodeParser()->parse(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')->getAsText()';
    }
);
TalesRegistry::registerPrefix(
    'jsquote',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::jsquote(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'datetime',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y H:i\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'date',
    function ($src, $nothrow): string {
        return 'date(\'d.m.Y\', ' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'nl2br',
    function ($src, $nothrow): string {
        return 'nl2br(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
TalesRegistry::registerPrefix(
    'formatSeconds',
    function ($src, $nothrow): string {
        return '\Stu\Module\Tal\TalHelper::formatSeconds(' . TalesInternal::compileToPHPExpression($src, $nothrow) . ')';
    }
);
