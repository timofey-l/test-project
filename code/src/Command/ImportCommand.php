<?php

namespace App\Command;

use App\Import\CSVImportProcessor;
use App\Import\Exceptions\FileNotFound;
use App\Import\Exceptions\FileReadError;
use App\Import\Rules\RuleDiscontinued;
use App\Import\Rules\RuleNumber;
use App\Import\Rules\RuleRange;
use App\Import\Rules\RuleValidate;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCommand extends Command
{
    /**
     * @var int Start time for measurement
     */
    private $startTime = 0;

    protected static $fieldsMapping = [
        'Product Code' => 'strProductCode',
        'Product Name' => 'strProductName',
        'Product Description' => 'strProductDesc',
        'Stock' => 'intQuantity',
        'Cost in GBP' => 'dcmPrice',
        'Discontinued' => 'dtmDiscontinued',
    ];

    protected static $defaultName = 'import:process';

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->startTime = microtime(true);
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Processes import file')
            ->setHelp('This command imports data from csv file')
            ->addArgument('filename', InputArgument::REQUIRED, 'path to CSV file')
            ->addOption('dry', null, InputOption::VALUE_OPTIONAL, 'run script without making any changes in DB', false);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $dryRun = is_null($input->getOption('dry')) ? true : false;

        $fieldsMapping = self::$fieldsMapping;
        $importProcessor = new CSVImportProcessor($filename, $fieldsMapping, [
            new RuleValidate(),
            new RuleNumber([
                'Stock' => 'Stock should be integer!',
                'Cost in GBP' => 'Wrong price value!',
            ]),
            new RuleRange([
                'Stock' => [
                    'max' => 10
                ],
                'Cost in GBP' => [
                    'max' => 5
                ],
            ], 'Price should be >= 5 and count should be >= 10'),
            new RuleRange([
                'Cost in GBP' => [
                    'min' => 1001
                ],
            ], 'Price should be < 1000'),
            new RuleDiscontinued('Discontinued'),
        ]);

        $io = new SymfonyStyle($input, $output);

        try {
            $importResult = $importProcessor->process();
            if (!$dryRun) {
                $this->dbProcessItems($importResult);
            }
            $this->renderResult($importResult, $io);
        } catch (FileNotFound $e) {
            $io->error('File not found: ' . $e->filename);
        } catch (FileReadError $e) {
            $io->error("Can't read the file: " . $e->filename);
        }

    }

    /**
     * Inserts or updates the database records
     *
     * @param \App\Import\ImportResult $importResult
     */
    private function dbProcessItems(\App\Import\ImportResult $importResult)
    {
        // insert/update successfully parsed items
        foreach ($importResult->successfulItems as $item) {
            $existingProduct = $this->getProductByCode($item['Product Code']);

            if ($existingProduct === false) {
                $this->insertProduct($item);
                $importResult->dbInsterts++;
            } else {
                $this->updateProduct($item);
                $importResult->dbUpdates++;
            }
        }

        // insert/update discontinued items
        foreach ($importResult->discountinuedItems as $item) {
            $existingProduct = $this->getProductByCode($item['Product Code']);

            if ($existingProduct === false) {
                $this->insertProduct($item, true);
                $importResult->dbInsterts++;
            } else {
                $this->updateProduct($item, true);
                $importResult->dbUpdates++;
            }
        }
    }

    /**
     * Returns array of product row or false if the row wasn't found
     * @param string $code
     * @return bool
     */
    protected function getProductByCode(string $code)
    {
        $stmt = $this->connection->prepare("
        SELECT *
        FROM tblProductData
        WHERE strProductCode = :code
        ");

        $stmt->bindParam(':code', $code);

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Renders the results to console
     *
     * @param \App\Import\ImportResult $importResult
     * @param SymfonyStyle $io
     */
    private function renderResult(\App\Import\ImportResult $importResult, SymfonyStyle $io)
    {
        $io->section('Stats');

        $io->table([], [
            ['Total processed values:', $importResult->processed()],
            ['Successfully parsed:', $importResult->successful()],
            ['Discontinued:', $importResult->discontinued()],
            ['Failed to parse:', $importResult->failed()],
            new TableSeparator(),
            ['New rows in DB:', $importResult->dbInsterts],
            ['Updated rows in DB', $importResult->dbUpdates],
            new TableSeparator(),
            ['Memory usage:', memory_get_usage(true) / 1024 / 1024 . ' Mb'],
            ['Time:', number_format(microtime(true) - $this->startTime, 1) . ' sec'],
        ]);

        $io->section('Failed items');

        foreach ($importResult->failedItems as $item) {

            $itemRow = [];
            foreach ($item as $key => $value) {
                if (strpos($key, '_') !== 0) {
                    $itemRow [] = "$key:'$value'";
                }
            }
            $itemStr = implode(', ', $itemRow);
            $io->text("Data: " . $itemStr);
            $io->text("Reason: " . $item['_error']);
            $io->newLine();

        }
    }

    /**
     * Gets array of product data with DB fields in keys
     * @param $item
     * @return array
     */
    protected function getMappedProduct($item) {
        $result = [];

        foreach (self::$fieldsMapping as $key => $value) {
            if (isset($item[$key])) {
                $result[$value] = $item[$key];
            }
        }

        return $result;
    }

    /**
     * Inserts new product into DB
     * @param $item
     * @param bool $discontinued
     */
    private function insertProduct($item, $discontinued = false)
    {
        $product = $this->getMappedProduct($item);

        $discontinuedStr = $discontinued ? 'CURRENT_TIMESTAMP' : 'NULL';

        $stmt = $this->connection->prepare("
        INSERT INTO tblProductData (strProductName, strProductCode, strProductDesc, intQuantity, dcmPrice, dtmDiscontinued)
        VALUES (:name, :code, :desc, :quantity, :price, $discontinuedStr);
        ");
        $stmt->bindParam(':name', $product['strProductName']);
        $stmt->bindParam(':code', $product['strProductCode']);
        $stmt->bindParam(':desc', $product['strProductDesc']);
        $stmt->bindParam(':quantity', $product['intQuantity']);
        $stmt->bindParam(':price', $product['dcmPrice']);

        $stmt->execute();
    }

    /**
     * Updates the product in DB
     * @param $item
     * @param bool $discontinued
     */
    private function updateProduct($item, $discontinued = false)
    {
        $product = $this->getMappedProduct($item);

        $discontinuedStr = $discontinued ? 'dtmDiscontinued = CURRENT_TIMESTAMP' : 'dtmDiscontinued = NULL';

        $stmt = $this->connection->prepare("
        UPDATE tblProductData
        SET 
          strProductName = :name, 
          strProductDesc = :desc, 
          intQuantity = :quantity, 
          dcmPrice = :price, 
          $discontinuedStr
        WHERE strProductCode = :code 
        ");
        $stmt->bindParam(':name', $product['strProductName']);
        $stmt->bindParam(':code', $product['strProductCode']);
        $stmt->bindParam(':desc', $product['strProductDesc']);
        $stmt->bindParam(':quantity', $product['intQuantity']);
        $stmt->bindParam(':price', $product['dcmPrice']);

        $stmt->execute();
    }

}
