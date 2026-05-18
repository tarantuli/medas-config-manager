<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig(ObjectInstantiator::class);

    $config->addPackages([
        ConfigManagerPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__ . '/tests')
    ->addDirectory(realpath(__DIR__ . '/tests/MockConfig'));
