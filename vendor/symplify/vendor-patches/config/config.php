<?php

declare (strict_types=1);
namespace ECSPrefix20220517;

use ECSPrefix20220517\SebastianBergmann\Diff\Differ;
use ECSPrefix20220517\SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use ECSPrefix20220517\Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ECSPrefix20220517\Symplify\PackageBuilder\Composer\VendorDirProvider;
use ECSPrefix20220517\Symplify\PackageBuilder\Yaml\ParametersMerger;
use ECSPrefix20220517\Symplify\SmartFileSystem\Json\JsonFileSystem;
use ECSPrefix20220517\Symplify\VendorPatches\Console\VendorPatchesApplication;
use function ECSPrefix20220517\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ECSPrefix20220517\Symplify\VendorPatches\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);
    $services->set(\ECSPrefix20220517\SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder::class)->args(['$addLineNumbers' => \true]);
    $services->set(\ECSPrefix20220517\SebastianBergmann\Diff\Differ::class)->args(['$outputBuilder' => \ECSPrefix20220517\Symfony\Component\DependencyInjection\Loader\Configurator\service(\ECSPrefix20220517\SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder::class)]);
    $services->set(\ECSPrefix20220517\Symplify\PackageBuilder\Composer\VendorDirProvider::class);
    $services->set(\ECSPrefix20220517\Symplify\SmartFileSystem\Json\JsonFileSystem::class);
    // for autowired commands
    $services->alias(\ECSPrefix20220517\Symfony\Component\Console\Application::class, \ECSPrefix20220517\Symplify\VendorPatches\Console\VendorPatchesApplication::class);
    $services->set(\ECSPrefix20220517\Symplify\PackageBuilder\Yaml\ParametersMerger::class);
};
