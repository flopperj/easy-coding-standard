<?php

declare (strict_types=1);
namespace ECSPrefix20220517\Symplify\EasyTesting\ValueObject;

use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo;
final class InputFileInfoAndExpected
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $inputFileInfo;
    /**
     * @var mixed
     */
    private $expected;
    /**
     * @param mixed $expected
     */
    public function __construct(\ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo $inputFileInfo, $expected)
    {
        $this->inputFileInfo = $inputFileInfo;
        $this->expected = $expected;
    }
    public function getInputFileContent() : string
    {
        return $this->inputFileInfo->getContents();
    }
    public function getInputFileInfo() : \ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->inputFileInfo;
    }
    public function getInputFileRealPath() : string
    {
        return $this->inputFileInfo->getRealPath();
    }
    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }
}
