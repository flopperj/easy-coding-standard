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
namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * Fixer for rules defined in PSR2 ¶4.3, ¶4.5.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class VisibilityRequiredFixer extends \PhpCsFixer\AbstractFixer implements \PhpCsFixer\Fixer\ConfigurableFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.', [new \PhpCsFixer\FixerDefinition\CodeSample('<?php
class Sample
{
    var $a;
    static protected $var_foo2;

    function A()
    {
    }
}
'), new \PhpCsFixer\FixerDefinition\CodeSample('<?php
class Sample
{
    const SAMPLE = 1;
}
', ['elements' => ['const']])]);
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        return $tokens->isAnyTokenKindsFound(\PhpCsFixer\Tokenizer\Token::getClassyTokenKinds());
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition() : \PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface
    {
        return new \PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('elements', 'The structural elements to fix (PHP >= 7.1 required for `const`).'))->setAllowedTypes(['array'])->setAllowedValues([new \PhpCsFixer\FixerConfiguration\AllowedValueSubset(['property', 'method', 'const'])])->setDefault(['property', 'method', 'const'])->getOption()]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        $tokensAnalyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $propertyTypeDeclarationKinds = [\T_STRING, \T_NS_SEPARATOR, \PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, \PhpCsFixer\Tokenizer\CT::T_ARRAY_TYPEHINT, \PhpCsFixer\Tokenizer\CT::T_TYPE_ALTERNATION, \PhpCsFixer\Tokenizer\CT::T_TYPE_INTERSECTION];
        if (\defined('T_READONLY')) {
            // @TODO: drop condition when PHP 8.1+ is required
            $propertyReadOnlyType = T_READONLY;
            $propertyTypeDeclarationKinds[] = T_READONLY;
        } else {
            $propertyReadOnlyType = -999;
        }
        $expectedKindsGeneric = [\T_ABSTRACT, \T_FINAL, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_STATIC, \T_VAR];
        $expectedKindsPropertyKinds = \array_merge($expectedKindsGeneric, $propertyTypeDeclarationKinds);
        foreach (\array_reverse($tokensAnalyzer->getClassyElements(), \true) as $index => $element) {
            if (!\in_array($element['type'], $this->configuration['elements'], \true)) {
                continue;
            }
            $abstractFinalIndex = null;
            $visibilityIndex = null;
            $staticIndex = null;
            $typeIndex = null;
            $readOnlyIndex = null;
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $expectedKinds = 'property' === $element['type'] ? $expectedKindsPropertyKinds : $expectedKindsGeneric;
            while ($tokens[$prevIndex]->isGivenKind($expectedKinds)) {
                if ($tokens[$prevIndex]->isGivenKind([\T_ABSTRACT, \T_FINAL])) {
                    $abstractFinalIndex = $prevIndex;
                } elseif ($tokens[$prevIndex]->isGivenKind(\T_STATIC)) {
                    $staticIndex = $prevIndex;
                } elseif ($tokens[$prevIndex]->isGivenKind($propertyReadOnlyType)) {
                    $readOnlyIndex = $prevIndex;
                } elseif ($tokens[$prevIndex]->isGivenKind($propertyTypeDeclarationKinds)) {
                    $typeIndex = $prevIndex;
                } else {
                    $visibilityIndex = $prevIndex;
                }
                $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            }
            if (null !== $typeIndex) {
                $index = $typeIndex;
            }
            if ($tokens[$prevIndex]->equals(',')) {
                continue;
            }
            $swapIndex = $staticIndex ?? $readOnlyIndex;
            // "static" property cannot be "readonly", so there can always be at most one swap
            if (null !== $swapIndex) {
                if ($this->isKeywordPlacedProperly($tokens, $swapIndex, $index)) {
                    $index = $swapIndex;
                } else {
                    $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $swapIndex, $index);
                }
            }
            if (null === $visibilityIndex) {
                $tokens->insertAt($index, [new \PhpCsFixer\Tokenizer\Token([\T_PUBLIC, 'public']), new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
            } else {
                if ($tokens[$visibilityIndex]->isGivenKind(\T_VAR)) {
                    $tokens[$visibilityIndex] = new \PhpCsFixer\Tokenizer\Token([\T_PUBLIC, 'public']);
                }
                if ($this->isKeywordPlacedProperly($tokens, $visibilityIndex, $index)) {
                    $index = $visibilityIndex;
                } else {
                    $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $visibilityIndex, $index);
                }
            }
            if (null === $abstractFinalIndex) {
                continue;
            }
            if ($this->isKeywordPlacedProperly($tokens, $abstractFinalIndex, $index)) {
                continue;
            }
            $this->moveTokenAndEnsureSingleSpaceFollows($tokens, $abstractFinalIndex, $index);
        }
    }
    private function isKeywordPlacedProperly(\PhpCsFixer\Tokenizer\Tokens $tokens, int $keywordIndex, int $comparedIndex) : bool
    {
        return $keywordIndex + 2 === $comparedIndex && ' ' === $tokens[$keywordIndex + 1]->getContent();
    }
    private function moveTokenAndEnsureSingleSpaceFollows(\PhpCsFixer\Tokenizer\Tokens $tokens, int $fromIndex, int $toIndex) : void
    {
        $tokens->insertAt($toIndex, [$tokens[$fromIndex], new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
        $tokens->clearAt($fromIndex);
        if ($tokens[$fromIndex + 1]->isWhitespace()) {
            $tokens->clearAt($fromIndex + 1);
        }
    }
}
