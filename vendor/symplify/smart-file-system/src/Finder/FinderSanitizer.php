<?php

namespace Symplify\SmartFileSystem\Finder;

use ECSPrefix20210509\Nette\Utils\Finder as NetteFinder;
use SplFileInfo;
use ECSPrefix20210509\Symfony\Component\Finder\Finder as SymfonyFinder;
use ECSPrefix20210509\Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\SmartFileSystem\Tests\Finder\FinderSanitizer\FinderSanitizerTest
 */
final class FinderSanitizer
{
    /**
     * @param mixed[] $files
     * @return mixed[]
     */
    public function sanitize($files)
    {
        $smartFileInfos = [];
        foreach ($files as $file) {
            $fileInfo = \is_string($file) ? new \SplFileInfo($file) : $file;
            if (!$this->isFileInfoValid($fileInfo)) {
                continue;
            }
            /** @var string $realPath */
            $realPath = $fileInfo->getRealPath();
            $smartFileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($realPath);
        }
        return $smartFileInfos;
    }
    /**
     * @return bool
     */
    private function isFileInfoValid(\SplFileInfo $fileInfo)
    {
        return (bool) $fileInfo->getRealPath();
    }
}
