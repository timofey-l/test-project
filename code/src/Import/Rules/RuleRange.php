<?php

namespace App\Import\Rules;

use App\Import\Exceptions\ImportRuleException;
use App\Import\Exceptions\ValidationRuleException;

class RuleRange implements ImportRule
{
    /**
     * @var array
     */
    private $fields;
    /**
     * @var string
     */
    private $validateErrorMessage;


    /**
     * RuleValidate constructor.
     * @param array $fields
     * @param string $validateErrorMessage
     */
    public function __construct($fields, string $validateErrorMessage)
    {

        $this->fields = $fields;
        $this->validateErrorMessage = $validateErrorMessage;
    }

    /**
     * Applies the rule to an item
     * @param $item
     * @return void
     * @throws ImportRuleException
     */
    public function apply(&$item)
    {
        $valid = true;

        foreach ($this->fields as $field => $fieldData) {
            if (!isset($item[$field]) || !is_numeric($item[$field])) {
                throw new ImportRuleException($this);
            }

            $valid = $valid && $this->process($fieldData, $item[$field]);
        }

        if ($valid) {
            throw new ValidationRuleException($this, $this->validateErrorMessage);
        }
    }

    /**
     * Processes the value and returns the value
     * @param $data
     * @param $value
     * @return boolean
     */
    private function process($data, $value)
    {
        $valid = true;

        if (isset($data['min'])) {
            $valid = $valid && ($value > $data['min']);
        }

        if (isset($data['max'])) {
            $valid = $valid && ($value < $data['max']);
        }

        return $valid;
    }
}
