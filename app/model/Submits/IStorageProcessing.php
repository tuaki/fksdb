<?php

namespace Submits;

use FKSDB\ORM\ModelSubmit;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IStorageProcessing {

    /**
     * @param string $filename
     * @return mixed
     */
    public function setInputFile(string $filename);

    /**
     * @param string $filename
     * @return mixed
     */
    public function setOutputFile(string $filename);

    /**
     * @param ModelSubmit $submit
     * @return mixed
     */
    public function process(ModelSubmit $submit);
}


