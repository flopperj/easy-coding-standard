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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\SwitchAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\ControlCaseStructuresAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
/**
 * Fixer for rules defined in PSR2 ¶5.2.
 */
final class SwitchCaseSemicolonToColonFixer extends \PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('A case should be followed by a colon and not a semicolon.', [new \PhpCsFixer\FixerDefinition\CodeSample('<?php
    switch ($a) {
        case 1;
            break;
        default;
            break;
    }
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after NoEmptyStatementFixer.
     */
    public function getPriority() : int
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        return $tokens->isTokenKindFound(\T_SWITCH);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        /** @var SwitchAnalysis $analysis */
        foreach (\PhpCsFixer\Tokenizer\Analyzer\ControlCaseStructuresAnalyzer::findControlStructures($tokens, [\T_SWITCH]) as $analysis) {
            $default = $analysis->getDefaultAnalysis();
            if (null !== $default) {
                $this->fixTokenIfNeeded($tokens, $default->getColonIndex());
            }
            foreach ($analysis->getCases() as $caseAnalysis) {
                $this->fixTokenIfNeeded($tokens, $caseAnalysis->getColonIndex());
            }
        }
    }
    private function fixTokenIfNeeded(\PhpCsFixer\Tokenizer\Tokens $tokens, int $index) : void
    {
        if ($tokens[$index]->equals(';')) {
            $tokens[$index] = new \PhpCsFixer\Tokenizer\Token(':');
        }
    }
}
