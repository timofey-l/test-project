<?php

namespace App\Import\Exceptions;

use App\Import\Rules\ImportRule;

class ImportRuleException extends \Exception
{
    /**
     * @var ImportRule
     */
    private $rule;


    /**
     * ImportRuleException constructor.
     */
    public function __construct(ImportRule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return ImportRule
     */
    public function getRule(): ImportRule
    {
        return $this->rule;
    }
}
