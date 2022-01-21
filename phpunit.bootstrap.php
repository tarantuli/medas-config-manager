<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ServiceManager\ServiceManager;

$sm = ServiceManager::get();

$sm->addPackage(ConfigManagerPackage::instance());

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);

$config->readEnv(__DIR__);
$config->addDirectory(realpath(__DIR__.'/tests/MockConfig'));
