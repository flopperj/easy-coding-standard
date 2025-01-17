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
namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Utils;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class PhpUnitMethodCasingFixer extends \PhpCsFixer\Fixer\AbstractPhpUnitFixer implements \PhpCsFixer\Fixer\ConfigurableFixerInterface
{
    /**
     * @internal
     */
    public const CAMEL_CASE = 'camel_case';
    /**
     * @internal
     */
    public const SNAKE_CASE = 'snake_case';
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Enforce camel (or snake) case for PHPUnit test methods, following configuration.', [new \PhpCsFixer\FixerDefinition\CodeSample('<?php
class MyTest extends \\PhpUnit\\FrameWork\\TestCase
{
    public function test_my_code() {}
}
'), new \PhpCsFixer\FixerDefinition\CodeSample('<?php
class MyTest extends \\PhpUnit\\FrameWork\\TestCase
{
    public function testMyCode() {}
}
', ['case' => self::SNAKE_CASE])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after PhpUnitTestAnnotationFixer.
     */
    public function getPriority() : int
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition() : \PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface
    {
        return new \PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('case', 'Apply camel or snake case to test methods'))->setAllowedValues([self::CAMEL_CASE, self::SNAKE_CASE])->setDefault(self::CAMEL_CASE)->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyPhpUnitClassFix(\PhpCsFixer\Tokenizer\Tokens $tokens, int $startIndex, int $endIndex) : void
    {
        for ($index = $endIndex - 1; $index > $startIndex; --$index) {
            if (!$this->isTestMethod($tokens, $index)) {
                continue;
            }
            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            $functionName = $tokens[$functionNameIndex]->getContent();
            $newFunctionName = $this->updateMethodCasing($functionName);
            if ($newFunctionName !== $functionName) {
                $tokens[$functionNameIndex] = new \PhpCsFixer\Tokenizer\Token([\T_STRING, $newFunctionName]);
            }
            $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
            if ($this->isPHPDoc($tokens, $docBlockIndex)) {
                $this->updateDocBlock($tokens, $docBlockIndex);
            }
        }
    }
    private function updateMethodCasing(string $functionName) : string
    {
        $parts = \explode('::', $functionName);
        $functionNamePart = \array_pop($parts);
        if (self::CAMEL_CASE === $this->configuration['case']) {
            $newFunctionNamePart = $functionNamePart;
            $newFunctionNamePart = \ucwords($newFunctionNamePart, '_');
            $newFunctionNamePart = \str_replace('_', '', $newFunctionNamePart);
            $newFunctionNamePart = \lcfirst($newFunctionNamePart);
        } else {
            $newFunctionNamePart = \PhpCsFixer\Utils::camelCaseToUnderscore($functionNamePart);
        }
        $parts[] = $newFunctionNamePart;
        return \implode('::', $parts);
    }
    private function isTestMethod(\PhpCsFixer\Tokenizer\Tokens $tokens, int $index) : bool
    {
        // Check if we are dealing with a (non-abstract, non-lambda) function
        if (!$this->isMethod($tokens, $index)) {
            return \false;
        }
        // if the function name starts with test it's a test
        $functionNameIndex = $tokens->getNextMeaningfulToken($index);
        $functionName = $tokens[$functionNameIndex]->getContent();
        if (\strncmp($functionName, 'test', \strlen('test')) === 0) {
            return \true;
        }
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
        return $this->isPHPDoc($tokens, $docBlockIndex) && \strpos($tokens[$docBlockIndex]->getContent(), '@test') !== \false;
    }
    private function isMethod(\PhpCsFixer\Tokenizer\Tokens $tokens, int $index) : bool
    {
        $tokensAnalyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        return $tokens[$index]->isGivenKind(\T_FUNCTION) && !$tokensAnalyzer->isLambda($index);
    }
    private function updateDocBlock(\PhpCsFixer\Tokenizer\Tokens $tokens, int $docBlockIndex) : void
    {
        $doc = new \PhpCsFixer\DocBlock\DocBlock($tokens[$docBlockIndex]->getContent());
        $lines = $doc->getLines();
        $docBlockNeedsUpdate = \false;
        for ($inc = 0; $inc < \count($lines); ++$inc) {
            $lineContent = $lines[$inc]->getContent();
            if (\strpos($lineContent, '@depends') === \false) {
                continue;
            }
            $newLineContent = \PhpCsFixer\Preg::replaceCallback('/(@depends\\s+)(.+)(\\b)/', function (array $matches) : string {
                return \sprintf('%s%s%s', $matches[1], $this->updateMethodCasing($matches[2]), $matches[3]);
            }, $lineContent);
            if ($newLineContent !== $lineContent) {
                $lines[$inc] = new \PhpCsFixer\DocBlock\Line($newLineContent);
                $docBlockNeedsUpdate = \true;
            }
        }
        if ($docBlockNeedsUpdate) {
            $lines = \implode('', $lines);
            $tokens[$docBlockIndex] = new \PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $lines]);
        }
    }
}
