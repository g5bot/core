<?php

declare(strict_types=1);

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

$config = $container->get(ConfigInterface::class);
$gameController = $container->get(GameControllerInterface::class);

$whoops = new Run();

if ($gameController->getUser()->getId() == 126 || $config->get('debug.debug_mode') === true) {
//    error_reporting(E_ALL);
    error_reporting(E_ALL & ~E_NOTICE);

    if (Whoops\Util\Misc::isCommandLine()) {
        $handler = new PlainTextHandler();
    } else {
        $handler = new PrettyPageHandler();
        $handler->setPageTitle('Error - Star Trek Universe');
    }

    $whoops->prependHandler($handler);

} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    if (Whoops\Util\Misc::isCommandLine()) {
        $handler = new PlainTextHandler();
    } else {
        $handler = function (Throwable $e, $inspector, $run) {
            require_once __DIR__ . '/../html/error.html';
        };
    }
    $whoops->prependHandler($handler);
}

$logger = new Monolog\Logger('stu');
$logger->pushHandler(
    new Monolog\Handler\StreamHandler($config->get('debug.logfile_path'))
);

$whoops->prependHandler(function (Throwable $e, $inspector, $run) use ($logger) {
    $logger->error(
        $e->getMessage(),
        [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ]
    );

});
$whoops->register();
