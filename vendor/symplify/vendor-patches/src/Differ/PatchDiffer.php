<?php

declare (strict_types=1);
namespace ECSPrefix20220517\Symplify\VendorPatches\Differ;

use ECSPrefix20220517\Nette\Utils\Strings;
use ECSPrefix20220517\SebastianBergmann\Diff\Differ;
use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo;
use ECSPrefix20220517\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
use ECSPrefix20220517\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
/**
 * @see \Symplify\VendorPatches\Tests\Differ\PatchDifferTest
 */
final class PatchDiffer
{
    /**
     * @see https://regex101.com/r/0O5NO1/4
     * @var string
     */
    private const LOCAL_PATH_REGEX = '#vendor\\/[^\\/]+\\/[^\\/]+\\/(?<local_path>.*?)$#is';
    /**
     * @see https://regex101.com/r/vNa7PO/1
     * @var string
     */
    private const START_ORIGINAL_REGEX = '#^--- Original#';
    /**
     * @see https://regex101.com/r/o8C90E/1
     * @var string
     */
    private const START_NEW_REGEX = '#^\\+\\+\\+ New#m';
    /**
     * @var \SebastianBergmann\Diff\Differ
     */
    private $differ;
    public function __construct(\ECSPrefix20220517\SebastianBergmann\Diff\Differ $differ)
    {
        $this->differ = $differ;
    }
    public function diff(\ECSPrefix20220517\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo $oldAndNewFileInfo) : string
    {
        $oldFileInfo = $oldAndNewFileInfo->getOldFileInfo();
        $newFileInfo = $oldAndNewFileInfo->getNewFileInfo();
        $diff = $this->differ->diff($oldFileInfo->getContents(), $newFileInfo->getContents());
        $patchedFileRelativePath = $this->resolveFileInfoPathRelativeFilePath($newFileInfo);
        $clearedDiff = \ECSPrefix20220517\Nette\Utils\Strings::replace($diff, self::START_ORIGINAL_REGEX, '--- /dev/null');
        return \ECSPrefix20220517\Nette\Utils\Strings::replace($clearedDiff, self::START_NEW_REGEX, '+++ ' . $patchedFileRelativePath);
    }
    private function resolveFileInfoPathRelativeFilePath(\ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo $beforeFileInfo) : string
    {
        $match = \ECSPrefix20220517\Nette\Utils\Strings::match($beforeFileInfo->getRealPath(), self::LOCAL_PATH_REGEX);
        if (!isset($match['local_path'])) {
            throw new \ECSPrefix20220517\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        return '../' . $match['local_path'];
    }
}
