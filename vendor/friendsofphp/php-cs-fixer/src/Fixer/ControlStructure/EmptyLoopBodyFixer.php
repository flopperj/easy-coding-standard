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
namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
final class EmptyLoopBodyFixer extends \PhpCsFixer\AbstractFixer implements \PhpCsFixer\Fixer\ConfigurableFixerInterface
{
    private const STYLE_BRACES = 'braces';
    private const STYLE_SEMICOLON = 'semicolon';
    private const TOKEN_LOOP_KINDS = [\T_FOR, \T_FOREACH, \T_WHILE];
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Empty loop-body must be in configured style.', [new \PhpCsFixer\FixerDefinition\CodeSample("<?php while(foo()){}\n"), new \PhpCsFixer\FixerDefinition\CodeSample("<?php while(foo());\n", ['style' => 'braces'])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer, NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer.
     * Must run after NoEmptyStatementFixer.
     */
    public function getPriority() : int
    {
        return 39;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        return $tokens->isAnyTokenKindsFound(self::TOKEN_LOOP_KINDS);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        if (self::STYLE_BRACES === $this->configuration['style']) {
            $analyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
            $fixLoop = static function (int $index, int $endIndex) use($tokens, $analyzer) : void {
                if ($tokens[$index]->isGivenKind(\T_WHILE) && $analyzer->isWhilePartOfDoWhile($index)) {
                    return;
                }
                $semiColonIndex = $tokens->getNextMeaningfulToken($endIndex);
                if (!$tokens[$semiColonIndex]->equals(';')) {
                    return;
                }
                $tokens[$semiColonIndex] = new \PhpCsFixer\Tokenizer\Token('{');
                $tokens->insertAt($semiColonIndex + 1, new \PhpCsFixer\Tokenizer\Token('}'));
            };
        } else {
            $fixLoop = static function (int $index, int $endIndex) use($tokens) : void {
                $braceOpenIndex = $tokens->getNextMeaningfulToken($endIndex);
                if (!$tokens[$braceOpenIndex]->equals('{')) {
                    return;
                }
                $braceCloseIndex = $tokens->getNextMeaningfulToken($braceOpenIndex);
                if (!$tokens[$braceCloseIndex]->equals('}')) {
                    return;
                }
                $tokens[$braceOpenIndex] = new \PhpCsFixer\Tokenizer\Token(';');
                $tokens->clearTokenAndMergeSurroundingWhitespace($braceCloseIndex);
            };
        }
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if ($tokens[$index]->isGivenKind(self::TOKEN_LOOP_KINDS)) {
                $endIndex = $tokens->getNextTokenOfKind($index, ['(']);
                // proceed to open '('
                $endIndex = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $endIndex);
                // proceed to close ')'
                $fixLoop($index, $endIndex);
                // fix loop if needs fixing
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition() : \PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface
    {
        return new \PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('style', 'Style of empty loop-bodies.'))->setAllowedTypes(['string'])->setAllowedValues([self::STYLE_BRACES, self::STYLE_SEMICOLON])->setDefault(self::STYLE_SEMICOLON)->getOption()]);
    }
}
