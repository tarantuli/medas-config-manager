<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ServiceManager\ServiceManager;

$sm = ServiceManager::get();

$sm->addPackage(ConfigManagerPackage::instance());

$sm->resolve(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory(realpath(__DIR__ . '/tests/MockConfig'));
