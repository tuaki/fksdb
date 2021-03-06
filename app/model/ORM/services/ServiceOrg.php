<?php

use FKSDB\ORM\ModelOrg;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceOrg extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ORG;
    protected $modelClassName = 'FKSDB\ORM\ModelOrg';

    /**
     * Syntactic sugar.
     *
     * @param mixed $signature
     * @param mixed $contest_id
     * @return ModelOrg|null
     */
    public function findByTeXSignature($signature, $contest_id) {
        if (!$signature) {
            return null;
        }
        $result = $this->getTable()->where('tex_signature', $signature)
            ->where('contest_id', $contest_id)->fetch();
        return $result ? ModelOrg::createFromTableRow($result) : null;
    }

}

