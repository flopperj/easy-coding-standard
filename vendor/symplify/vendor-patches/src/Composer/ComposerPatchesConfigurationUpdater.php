<?php

declare (strict_types=1);
namespace ECSPrefix20220517\Symplify\VendorPatches\Composer;

use ECSPrefix20220517\Symplify\Astral\Exception\ShouldNotHappenException;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use ECSPrefix20220517\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use ECSPrefix20220517\Symplify\PackageBuilder\Yaml\ParametersMerger;
use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Symplify\VendorPatches\Tests\Composer\ComposerPatchesConfigurationUpdater\ComposerPatchesConfigurationUpdaterTest
 */
final class ComposerPatchesConfigurationUpdater
{
    /**
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;
    /**
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    public function __construct(\ECSPrefix20220517\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \ECSPrefix20220517\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager $jsonFileManager, \ECSPrefix20220517\Symplify\PackageBuilder\Yaml\ParametersMerger $parametersMerger)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->jsonFileManager = $jsonFileManager;
        $this->parametersMerger = $parametersMerger;
    }
    /**
     * @param mixed[] $composerExtraPatches
     */
    public function updateComposerJson(string $composerJsonFilePath, array $composerExtraPatches) : \ECSPrefix20220517\Symplify\ComposerJsonManipulator\ValueObject\ComposerJson
    {
        $extra = ['patches' => $composerExtraPatches];
        $composerJson = $this->composerJsonFactory->createFromFilePath($composerJsonFilePath);
        // merge "extra" section - deep merge is needed, so original patches are included
        $newExtra = $this->parametersMerger->merge($composerJson->getExtra(), $extra);
        $composerJson->setExtra($newExtra);
        return $composerJson;
    }
    /**
     * @param mixed[] $composerExtraPatches
     */
    public function updateComposerJsonAndPrint(string $composerJsonFilePath, array $composerExtraPatches) : void
    {
        $composerJson = $this->updateComposerJson($composerJsonFilePath, $composerExtraPatches);
        $fileInfo = $composerJson->getFileInfo();
        if (!$fileInfo instanceof \ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo) {
            throw new \ECSPrefix20220517\Symplify\Astral\Exception\ShouldNotHappenException();
        }
        $this->jsonFileManager->printComposerJsonToFilePath($composerJson, $fileInfo->getRealPath());
    }
}
