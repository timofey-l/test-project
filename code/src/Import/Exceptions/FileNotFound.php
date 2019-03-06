<?php

namespace App\Import\Exceptions;

class FileNotFound extends \Exception
{
    public $filename = '';

    /**
     * FileNotFound constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }


}
