<?php

declare (strict_types=1);
namespace ECSPrefix20211113;

use ECSPrefix20211113\Symfony\Component\Console\Style\SymfonyStyle;
use ECSPrefix20211113\Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ECSPrefix20211113\Symplify\ComposerJsonManipulator\ValueObject\Option;
use ECSPrefix20211113\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use ECSPrefix20211113\Symplify\PackageBuilder\Parameter\ParameterProvider;
use ECSPrefix20211113\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use ECSPrefix20211113\Symplify\SmartFileSystem\SmartFileSystem;
use function ECSPrefix20211113\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\ECSPrefix20211113\Symplify\ComposerJsonManipulator\ValueObject\Option::INLINE_SECTIONS, ['keywords']);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ECSPrefix20211113\Symplify\ComposerJsonManipulator\\', __DIR__ . '/../src');
    $services->set(\ECSPrefix20211113\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\ECSPrefix20211113\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\ECSPrefix20211113\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->args([\ECSPrefix20211113\Symfony\Component\DependencyInjection\Loader\Configurator\service(\ECSPrefix20211113\Symfony\Component\DependencyInjection\ContainerInterface::class)]);
    $services->set(\ECSPrefix20211113\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\ECSPrefix20211113\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\ECSPrefix20211113\Symfony\Component\DependencyInjection\Loader\Configurator\service(\ECSPrefix20211113\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
};
