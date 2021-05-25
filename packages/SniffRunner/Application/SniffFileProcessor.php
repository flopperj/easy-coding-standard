<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\SniffRunner\Application;

use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PhpCsFixer\Differ\DifferInterface;
use Symplify\EasyCodingStandard\Application\AppliedCheckersCollector;
use Symplify\EasyCodingStandard\Configuration\Configuration;
use Symplify\EasyCodingStandard\Contract\Application\FileProcessorInterface;
use Symplify\EasyCodingStandard\Error\ErrorAndDiffCollector;
use Symplify\EasyCodingStandard\FileSystem\TargetFileInfoResolver;
use Symplify\EasyCodingStandard\SniffRunner\File\FileFactory;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\File;
use ECSPrefix20210525\Symplify\SmartFileSystem\SmartFileInfo;
use ECSPrefix20210525\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @see \Symplify\EasyCodingStandard\Tests\Error\ErrorCollector\SniffFileProcessorTest
 */
final class SniffFileProcessor implements \Symplify\EasyCodingStandard\Contract\Application\FileProcessorInterface
{
    /**
     * @var Sniff[]
     */
    private $sniffs = [];
    /**
     * @var Sniff[][]
     */
    private $tokenListeners = [];
    /**
     * @var Fixer
     */
    private $fixer;
    /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;
    /**
     * @var DifferInterface
     */
    private $differ;
    /**
     * @var AppliedCheckersCollector
     */
    private $appliedCheckersCollector;
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var TargetFileInfoResolver
     */
    private $targetFileInfoResolver;
    /**
     * @param Sniff[] $sniffs
     */
    public function __construct(\PHP_CodeSniffer\Fixer $fixer, \Symplify\EasyCodingStandard\SniffRunner\File\FileFactory $fileFactory, \Symplify\EasyCodingStandard\Configuration\Configuration $configuration, \Symplify\EasyCodingStandard\Error\ErrorAndDiffCollector $errorAndDiffCollector, \PhpCsFixer\Differ\DifferInterface $differ, \Symplify\EasyCodingStandard\Application\AppliedCheckersCollector $appliedCheckersCollector, \ECSPrefix20210525\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Symplify\EasyCodingStandard\FileSystem\TargetFileInfoResolver $targetFileInfoResolver, array $sniffs = [])
    {
        $this->fixer = $fixer;
        $this->fileFactory = $fileFactory;
        $this->configuration = $configuration;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->differ = $differ;
        $this->appliedCheckersCollector = $appliedCheckersCollector;
        $this->addCompatibilityLayer();
        foreach ($sniffs as $sniff) {
            $this->addSniff($sniff);
        }
        $this->smartFileSystem = $smartFileSystem;
        $this->targetFileInfoResolver = $targetFileInfoResolver;
    }
    /**
     * @return void
     */
    public function addSniff(\PHP_CodeSniffer\Sniffs\Sniff $sniff)
    {
        $this->sniffs[] = $sniff;
        $tokens = $sniff->register();
        foreach ($tokens as $token) {
            $this->tokenListeners[$token][] = $sniff;
        }
    }
    /**
     * @return Sniff[]
     */
    public function getCheckers() : array
    {
        return $this->sniffs;
    }
    public function processFile(\ECSPrefix20210525\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        $file = $this->fileFactory->createFromFileInfo($smartFileInfo);
        $this->fixFile($file, $this->fixer, $smartFileInfo, $this->tokenListeners);
        // add diff
        if ($smartFileInfo->getContents() !== $this->fixer->getContents()) {
            $diff = $this->differ->diff($smartFileInfo->getContents(), $this->fixer->getContents());
            $targetFileInfo = $this->targetFileInfoResolver->resolveTargetFileInfo($smartFileInfo);
            $this->errorAndDiffCollector->addDiffForFileInfo($targetFileInfo, $diff, $this->appliedCheckersCollector->getAppliedCheckersPerFileInfo($smartFileInfo));
        }
        // 4. save file content (faster without changes check)
        if ($this->configuration->isFixer()) {
            $this->smartFileSystem->dumpFile($file->getFilename(), $this->fixer->getContents());
        }
        return $this->fixer->getContents();
    }
    /**
     * @return void
     */
    private function addCompatibilityLayer()
    {
        if (!\defined('PHP_CODESNIFFER_VERBOSITY')) {
            // initalize token with INT type, otherwise php-cs-fixer and php-parser breaks
            if (!\defined('T_MATCH')) {
                \define('T_MATCH', 5000);
            }
            \define('PHP_CODESNIFFER_VERBOSITY', 0);
            new \PHP_CodeSniffer\Util\Tokens();
        }
    }
    /**
     * Mimics @see \PHP_CodeSniffer\Files\File::process()
     *
     * @see \PHP_CodeSniffer\Fixer::fixFile()
     *
     * @param Sniff[][] $tokenListeners
     * @return void
     */
    private function fixFile(\Symplify\EasyCodingStandard\SniffRunner\ValueObject\File $file, \PHP_CodeSniffer\Fixer $fixer, \ECSPrefix20210525\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $tokenListeners)
    {
        $previousContent = $smartFileInfo->getContents();
        $this->fixer->loops = 0;
        do {
            // Only needed once file content has changed.
            $content = $previousContent;
            $file->setContent($content);
            $file->processWithTokenListenersAndFileInfo($tokenListeners, $smartFileInfo);
            // fixed content
            $previousContent = $fixer->getContents();
            ++$this->fixer->loops;
        } while ($previousContent !== $content);
    }
}
