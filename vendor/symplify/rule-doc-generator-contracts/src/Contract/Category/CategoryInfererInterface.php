<?php

namespace ECSPrefix20210517\Symplify\RuleDocGenerator\Contract\Category;

use ECSPrefix20210517\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface CategoryInfererInterface
{
    /**
     * @return string|null
     */
    public function infer(\ECSPrefix20210517\Symplify\RuleDocGenerator\ValueObject\RuleDefinition $ruleDefinition);
}
