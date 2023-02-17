<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ServiceManager\ServiceConfig;
use Medas\ServiceManager\ServiceManager;

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();
    $config->addPackages([
        ConfigManagerPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory(realpath(__DIR__ . '/tests/MockConfig'));
