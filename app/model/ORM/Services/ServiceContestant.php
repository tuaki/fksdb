<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestant;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceContestant extends AbstractServiceSingle {

    protected $viewName = DbNames::VIEW_CONTESTANT;

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelContestant::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_CONTESTANT_BASE;
    }

    /**
     * @note Read-only (loads data from view).
     *
     * @param int $contest_id
     * @param int $year
     * @return Selection
     */
    public function getCurrentContestants($contest_id, $year) {
        $contestants = $this->getConnection()->table($this->viewName)
            ->select('*');


        $contestants->where([
            'v_contestant.contest_id' => $contest_id,
            'v_contestant.year' => $year,
        ]);

        return $contestants;
    }

}
