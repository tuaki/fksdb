<?php

namespace FKSDB\Utils;

use Iterator;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CSVParser extends Object implements Iterator {

    const INDEX_NUMERIC = 0;
    const INDEX_FROM_HEADER = 1;
    const BOM = '\xEF\xBB\xBF';

    private $file;
    private $delimiter;
    private $indexType;
    private $rowNumber;
    private $currentRow;
    private $header;

    /**
     * CSVParser constructor.
     * @param $filename
     * @param int $indexType
     * @param string $delimiter
     */
    function __construct($filename, $indexType = self::INDEX_NUMERIC, $delimiter = ';') {
        $this->indexType = $indexType;
        $this->delimiter = $delimiter;
        $this->file = fopen($filename, 'r');
        if (!$this->file) {
            throw new InvalidStateException("The file '" . $filename . "' cannot be read.");
        }
    }

    /**
     * @return mixed
     */
    public function current() {
        return $this->currentRow;
    }

    /**
     * @return mixed
     */
    public function key() {
        return $this->rowNumber;
    }

    public function next() {
        $this->currentRow = fgetcsv($this->file, 0, $this->delimiter);
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $result = [];
            foreach ($this->header as $i => $name) {
                $result[$name] = $this->currentRow[$i];
            }
            $this->currentRow = $result;
        }
        $this->rowNumber++;
    }

    public function rewind() {
        rewind($this->file);
        $this->rowNumber = 0;
        if ($this->indexType == self::INDEX_FROM_HEADER) {
            $this->header = fgetcsv($this->file, 0, $this->delimiter);
            $first = reset($this->header);
            if ($first !== false) {
                $first = preg_replace('/' . self::BOM . '/', '', $first);
                $this->header[0] = $first;
            }
        }
        if ($this->valid()) {
            $this->next();
        }
    }

    /**
     * @return bool
     */
    public function valid() {
        $eof = feof($this->file);
        if ($eof) {
            fclose($this->file);
        }
        return !$eof;
    }

}
