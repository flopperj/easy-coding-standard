<?php

namespace ECSPrefix20210509\Jean85\Exception;

class ProvidedPackageException extends \Exception implements \ECSPrefix20210509\Jean85\Exception\VersionMissingExceptionInterface
{
    /**
     * @param string $packageName
     * @return \Jean85\Exception\VersionMissingExceptionInterface
     */
    public static function create($packageName)
    {
        $packageName = (string) $packageName;
        return new self('Cannot retrieve a version for package ' . $packageName . ' since it is provided, probably a metapackage');
    }
}
