<?php

declare (strict_types=1);
namespace ECSPrefix20210525;

use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use ECSPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\ECSPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->import(__DIR__ . '/config.php');
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->set(\PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer::class);
    $services->load('Symplify\\CodingStandard\\Fixer\\', __DIR__ . '/../src/Fixer')->exclude([__DIR__ . '/../src/Fixer/Annotation']);
    $services->set(\PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer::class);
};
