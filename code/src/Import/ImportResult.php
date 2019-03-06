<?php

namespace App\Import;

class ImportResult
{
    /**
     * @var array All failed items
     */
    public $failedItems = [];

    /**
     * @var array All successfully parsed items
     */
    public $successfulItems = [];

    /**
     * @var array All discontinued items
     */
    public $discountinuedItems = [];

    /**
     * @var int Number of insterts into db
     */
    public $dbInsterts = 0;

    /**
     * @var int Number of updated rows in db
     */
    public $dbUpdates = 0;

    /**
     * Returns total amount of processed items
     * @return int
     */
    public function processed()
    {
        return count($this->failedItems)
            + count($this->successfulItems)
            + count($this->discountinuedItems);
    }

    /**
     * Returns number of successful parsed items
     * @return int
     */
    public function successful()
    {
        return count($this->successfulItems);
    }

    /**
     * Returns number of discontinued items
     * @return int
     */
    public function discontinued()
    {
        return count($this->discountinuedItems);
    }

    /**
     * Returns number of failed items
     * @return int
     */
    public function failed()
    {
        return count($this->failedItems);
    }
}
