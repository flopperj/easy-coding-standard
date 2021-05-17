<?php

namespace Symplify\EasyCodingStandard\FileSystem;

use Symplify\EasyCodingStandard\ChangedFilesDetector\ChangedFilesDetector;
use ECSPrefix20210517\Symplify\SmartFileSystem\SmartFileInfo;
final class FileFilter
{
    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;
    public function __construct(\Symplify\EasyCodingStandard\ChangedFilesDetector\ChangedFilesDetector $changedFilesDetector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return mixed[]
     */
    public function filterOnlyChangedFiles(array $fileInfos)
    {
        return \array_filter($fileInfos, function (\ECSPrefix20210517\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool {
            return $this->changedFilesDetector->hasFileInfoChanged($smartFileInfo);
        });
    }
}
