<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ObjectInstantiator\ObjectInstantiatorPackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class, isDev: true);

    $config->addPackages([
        ConfigManagerPackage::instance(),
        ObjectInstantiatorPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__ . '/tests')
    ->addDirectory(realpath(__DIR__ . '/tests/MockConfig'));
