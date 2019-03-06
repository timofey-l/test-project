<?php

namespace App\Import\Rules;

use App\Import\Exceptions\ImportRuleException;

interface ImportRule {

    /**
     * Applies the rule to an item
     * @param $item
     * @throws ImportRuleException
     * @return void
     */
    public function apply(&$item);

}
