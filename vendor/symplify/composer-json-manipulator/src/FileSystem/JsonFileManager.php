<?php

declare (strict_types=1);
namespace ECSPrefix20220517\Symplify\ComposerJsonManipulator\FileSystem;

use ECSPrefix20220517\Nette\Utils\Json;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\Json\JsonCleaner;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\Json\JsonInliner;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use ECSPrefix20220517\Symplify\PackageBuilder\Configuration\StaticEolConfiguration;
use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo;
use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @see \Symplify\MonorepoBuilder\Tests\FileSystem\JsonFileManager\JsonFileManagerTest
 */
final class JsonFileManager
{
    /**
     * @var array<string, mixed[]>
     */
    private $cachedJSONFiles = [];
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Symplify\ComposerJsonManipulator\Json\JsonCleaner
     */
    private $jsonCleaner;
    /**
     * @var \Symplify\ComposerJsonManipulator\Json\JsonInliner
     */
    private $jsonInliner;
    public function __construct(\ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \ECSPrefix20220517\Symplify\ComposerJsonManipulator\Json\JsonCleaner $jsonCleaner, \ECSPrefix20220517\Symplify\ComposerJsonManipulator\Json\JsonInliner $jsonInliner)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->jsonCleaner = $jsonCleaner;
        $this->jsonInliner = $jsonInliner;
    }
    /**
     * @return mixed[]
     */
    public function loadFromFileInfo(\ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $realPath = $smartFileInfo->getRealPath();
        if (!isset($this->cachedJSONFiles[$realPath])) {
            $this->cachedJSONFiles[$realPath] = \ECSPrefix20220517\Nette\Utils\Json::decode($smartFileInfo->getContents(), \ECSPrefix20220517\Nette\Utils\Json::FORCE_ARRAY);
        }
        return $this->cachedJSONFiles[$realPath];
    }
    /**
     * @return array<string, mixed>
     */
    public function loadFromFilePath(string $filePath) : array
    {
        $fileContent = $this->smartFileSystem->readFile($filePath);
        return \ECSPrefix20220517\Nette\Utils\Json::decode($fileContent, \ECSPrefix20220517\Nette\Utils\Json::FORCE_ARRAY);
    }
    /**
     * @param mixed[] $json
     */
    public function printJsonToFileInfo(array $json, \ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        $jsonString = $this->encodeJsonToFileContent($json);
        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $jsonString);
        $realPath = $smartFileInfo->getRealPath();
        unset($this->cachedJSONFiles[$realPath]);
        return $jsonString;
    }
    public function printComposerJsonToFilePath(\ECSPrefix20220517\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson $composerJson, string $filePath) : string
    {
        $jsonString = $this->encodeJsonToFileContent($composerJson->getJsonArray());
        $this->smartFileSystem->dumpFile($filePath, $jsonString);
        return $jsonString;
    }
    /**
     * @param mixed[] $json
     */
    public function encodeJsonToFileContent(array $json) : string
    {
        // Empty arrays may lead to bad encoding since we can't be sure whether they need to be arrays or objects.
        $json = $this->jsonCleaner->removeEmptyKeysFromJsonArray($json);
        $jsonContent = \ECSPrefix20220517\Nette\Utils\Json::encode($json, \ECSPrefix20220517\Nette\Utils\Json::PRETTY) . \ECSPrefix20220517\Symplify\PackageBuilder\Configuration\StaticEolConfiguration::getEolChar();
        return $this->jsonInliner->inlineSections($jsonContent);
    }
}
