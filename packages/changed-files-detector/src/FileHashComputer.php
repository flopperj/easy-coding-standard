<?php

namespace Symplify\EasyCodingStandard\ChangedFilesDetector;

use ECSPrefix20210507\Symfony\Component\Config\FileLocator;
use ECSPrefix20210507\Symfony\Component\Config\Loader\LoaderInterface;
use ECSPrefix20210507\Symfony\Component\Config\Loader\LoaderResolver;
use ECSPrefix20210507\Symfony\Component\DependencyInjection\ContainerBuilder;
use ECSPrefix20210507\Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symplify\EasyCodingStandard\Exception\Configuration\FileNotFoundException;
use Symplify\PackageBuilder\DependencyInjection\FileLoader\ParameterMergingPhpFileLoader;
use Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
/**
 * @see \Symplify\EasyCodingStandard\ChangedFilesDetector\Tests\FileHashComputer\FileHashComputerTest
 */
final class FileHashComputer
{
    /**
     * @param string $filePath
     * @return string
     */
    public function computeConfig($filePath)
    {
        $containerBuilder = new ContainerBuilder();
        $loader = $this->createLoader($filePath, $containerBuilder);
        $loader->load($filePath);
        $parameterBag = $containerBuilder->getParameterBag();
        return $this->arrayToHash($containerBuilder->getServiceIds()) . $this->arrayToHash($parameterBag->all());
    }
    /**
     * @param string $filePath
     * @return string
     */
    public function compute($filePath)
    {
        $fileHash = \md5_file($filePath);
        if (!$fileHash) {
            throw new FileNotFoundException(\sprintf('File "%s" was not found', $fileHash));
        }
        return $fileHash;
    }
    /**
     * @param mixed[] $array
     * @return string
     */
    private function arrayToHash(array $array)
    {
        $serializedArray = \serialize($array);
        return \md5($serializedArray);
    }
    /**
     * @param string $filePath
     * @param \ECSPrefix20210507\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     * @return \ECSPrefix20210507\Symfony\Component\Config\Loader\LoaderInterface
     */
    private function createLoader($filePath, $containerBuilder)
    {
        $fileLocator = new FileLocator([\dirname($filePath)]);
        $loaders = [new GlobFileLoader($containerBuilder, $fileLocator), new ParameterMergingPhpFileLoader($containerBuilder, $fileLocator)];
        $loaderResolver = new LoaderResolver($loaders);
        $loader = $loaderResolver->resolve($filePath);
        if (!$loader) {
            throw new ShouldNotHappenException();
        }
        return $loader;
    }
}
