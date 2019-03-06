<?php

namespace App\Import;

use App\Import\Exceptions\DiscontinuedRuleException;
use App\Import\Exceptions\FileNotFound;
use App\Import\Exceptions\FileReadError;
use App\Import\Rules\ImportRule;

class CSVImportProcessor implements ImportProcessor
{

    /**
     * @var string delimeter for values
     */
    public $delimiter = ',';

    /**
     * @var bool true if file contains headers line
     */
    public $headerLine = true;

    /**
     * @var ImportRule[];
     */
    public $rules = [];

    /**
     * @var array|bool map fields in csv file to entity;
     */
    public $headers = [];

    /** @var string $filename */
    private $filename;

    /**
     * CSVImportProcessor
     *
     * @param $filename string filename to import
     * @param bool|array $headers mapping of fields
     * @param ImportRule[] $rules rules to apply for each item
     */
    public function __construct(string $filename, $headers = false, $rules = [])
    {
        $this->filename = $filename;

        if (is_array($headers)) {
            $this->headers = $headers;
        }

        $this->rules = $rules;
    }


    /**
     * @inheritdoc
     *
     * @throws FileNotFound
     * @throws FileReadError
     */
    public function process()
    {
        // check if file exists and readable
        if (!file_exists($this->filename)) {
            throw new FileNotFound($this->filename);
        }

        // enable auto detect line endings
        ini_set("auto_detect_line_endings", true);

        // open file for read
        $handle = fopen($this->filename, 'r');

        if ($handle === false) {
            throw new FileReadError($this->filename);
        }

        // create results object
        $result = new ImportResult();

        // process the file
        $row = 1;

        // headers array to define the values
        $headers = $this->headers;

        while (($data = fgetcsv($handle, 1024, $this->delimiter)) !== false) {

            // get the headers if possible
            if ($row === 1 && $this->headerLine) {
                $headers = $data;
                $row++;
                continue;
            }

            $item = [];
            foreach ($headers as $i => $header) {
                $item[$header] = $data[$i] ?? null;
            }

            $this->processItem($item, $result);

            $row++;
        }

        // close the file
        fclose($handle);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function processItem($item, ImportResult $result)
    {
        try {
            foreach ($this->rules() as $rule) {
                $rule->apply($item);
            }
            $result->successfulItems[] = $item;
        } catch (Exceptions\ValidationRuleException $e) {
            $item['_error'] = $e->getMsg();
            $result->failedItems[] = $item;
        } catch (DiscontinuedRuleException $e) {
            $result->discountinuedItems[] = $item;
        } catch (Exceptions\ImportRuleException $e) {
            $result->failedItems[] = $item;
        }
    }

    /**
     * Returns an array of rules to process
     * @return ImportRule[]
     */
    public function rules()
    {
        return $this->rules;
    }
}
