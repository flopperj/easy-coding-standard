<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Documentation\DocumentationLocator;
use PhpCsFixer\Documentation\FixerDocumentGenerator;
use PhpCsFixer\Documentation\ListDocumentGenerator;
use PhpCsFixer\Documentation\RuleSetDocumentationGenerator;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSets;
use ECSPrefix20220517\Symfony\Component\Console\Command\Command;
use ECSPrefix20220517\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface;
use ECSPrefix20220517\Symfony\Component\Filesystem\Filesystem;
use ECSPrefix20220517\Symfony\Component\Finder\Finder;
use ECSPrefix20220517\Symfony\Component\Finder\SplFileInfo;
/**
 * @internal
 */
final class DocumentationCommand extends \ECSPrefix20220517\Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'documentation';
    protected function configure() : void
    {
        $this->setAliases(['doc'])->setDescription('Dumps the documentation of the project into its "/doc" directory.');
    }
    protected function execute(\ECSPrefix20220517\Symfony\Component\Console\Input\InputInterface $input, \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $filesystem = new \ECSPrefix20220517\Symfony\Component\Filesystem\Filesystem();
        $locator = new \PhpCsFixer\Documentation\DocumentationLocator();
        $fixerFactory = new \PhpCsFixer\FixerFactory();
        $fixerFactory->registerBuiltInFixers();
        $fixers = $fixerFactory->getFixers();
        $setDefinitions = \PhpCsFixer\RuleSet\RuleSets::getSetDefinitions();
        $fixerDocumentGenerator = new \PhpCsFixer\Documentation\FixerDocumentGenerator($locator);
        $ruleSetDocumentationGenerator = new \PhpCsFixer\Documentation\RuleSetDocumentationGenerator($locator);
        $listDocumentGenerator = new \PhpCsFixer\Documentation\ListDocumentGenerator($locator);
        // Array of existing fixer docs.
        // We first override existing files, and then we will delete files that are no longer needed.
        // We cannot remove all files first, as generation of docs is re-using existing docs to extract code-samples for
        // VersionSpecificCodeSample under incompatible PHP version.
        $docForFixerRelativePaths = [];
        foreach ($fixers as $fixer) {
            $docForFixerRelativePaths[] = $locator->getFixerDocumentationFileRelativePath($fixer);
            $filesystem->dumpFile($locator->getFixerDocumentationFilePath($fixer), $fixerDocumentGenerator->generateFixerDocumentation($fixer));
        }
        /** @var SplFileInfo $file */
        foreach ((new \ECSPrefix20220517\Symfony\Component\Finder\Finder())->files()->in($locator->getFixersDocumentationDirectoryPath())->notPath($docForFixerRelativePaths) as $file) {
            $filesystem->remove($file->getPathname());
        }
        // Fixer doc. index
        $filesystem->dumpFile($locator->getFixersDocumentationIndexFilePath(), $fixerDocumentGenerator->generateFixersDocumentationIndex($fixers));
        // RuleSet docs.
        /** @var SplFileInfo $file */
        foreach ((new \ECSPrefix20220517\Symfony\Component\Finder\Finder())->files()->in($locator->getRuleSetsDocumentationDirectoryPath()) as $file) {
            $filesystem->remove($file->getPathname());
        }
        $paths = [];
        foreach ($setDefinitions as $name => $definition) {
            $path = $locator->getRuleSetsDocumentationFilePath($name);
            $paths[$name] = $path;
            $filesystem->dumpFile($path, $ruleSetDocumentationGenerator->generateRuleSetsDocumentation($definition, $fixers));
        }
        // RuleSet doc. index
        $filesystem->dumpFile($locator->getRuleSetsDocumentationIndexFilePath(), $ruleSetDocumentationGenerator->generateRuleSetsDocumentationIndex($paths));
        // List file / Appendix
        $filesystem->dumpFile($locator->getListingFilePath(), $listDocumentGenerator->generateListingDocumentation($fixers));
        $output->writeln('Docs updated.');
        return 0;
    }
}
