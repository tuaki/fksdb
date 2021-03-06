<?php

use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_REGION;
    protected $modelClassName = 'FKSDB\ORM\ModelRegion';

    /**
     * @return Selection
     */
    public function getCountries(): Selection {
        return $this->getTable()->where('country_iso = nuts');
    }

}

