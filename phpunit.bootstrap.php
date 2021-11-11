<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ServiceManager\ServiceManager;

$sm = ServiceManager::get();

$sm->addPackage(ConfigManager::class);

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);

$config->readEnv(__DIR__);
$config->addDirectory(realpath(__DIR__.'/tests/MockConfig'));
