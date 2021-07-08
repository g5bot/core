<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tick\Process\ProcessTickInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

/**
 * @var ProcessTickInterface[] $handlerList
 */
$handlerList = $container->get('process_tick_handler');

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

foreach ($handlerList as $process) {
    $process->work();
}

$db->flush();
$db->commit();
