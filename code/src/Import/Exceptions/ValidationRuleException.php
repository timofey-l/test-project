<?php

namespace App\Import\Exceptions;

use App\Import\Rules\ImportRule;

class ValidationRuleException extends ImportRuleException
{
    /**
     * @var string
     */
    private $msg;

    /**
     * ValidationRuleException constructor.
     * @param ImportRule $rule
     * @param string $msg
     */
    public function __construct(ImportRule $rule, string $msg)
    {
        parent::__construct($rule);
        $this->msg = $msg;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }
}
