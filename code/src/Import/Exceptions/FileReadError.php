<?php

namespace App\Import\Exceptions;

class FileReadError extends \Exception
{
    public $filename = '';

    /**
     * FileReadError constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }


}
