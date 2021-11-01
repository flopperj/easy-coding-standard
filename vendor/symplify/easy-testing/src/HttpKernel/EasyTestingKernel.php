<?php

declare (strict_types=1);
namespace ECSPrefix20211101\Symplify\EasyTesting\HttpKernel;

use ECSPrefix20211101\Psr\Container\ContainerInterface;
use ECSPrefix20211101\Symplify\EasyTesting\ValueObject\EasyTestingConfig;
use ECSPrefix20211101\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class EasyTestingKernel extends \ECSPrefix20211101\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs($configFiles) : \ECSPrefix20211101\Psr\Container\ContainerInterface
    {
        $configFiles[] = \ECSPrefix20211101\Symplify\EasyTesting\ValueObject\EasyTestingConfig::FILE_PATH;
        return $this->create([], [], $configFiles);
    }
}
