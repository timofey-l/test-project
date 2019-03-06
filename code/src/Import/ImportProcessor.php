<?php

namespace App\Import;

use App\Import\Rules\ImportRule;

interface ImportProcessor {

    /**
     * Returns an array of rules to process
     * @return ImportRule[]
     */
    public function rules();

    /**
     * Processes the import
     * @return ImportResult
     */
    public function process();

    /**
     * Processes item of import
     * @param $item
     * @param ImportResult $result
     * @return void
     */
    public function processItem($item, ImportResult $result);

}
