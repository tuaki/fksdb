<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Strings;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readint $contest_id
 * @property-readstring $name
 */
class ModelContest extends AbstractModelSingle {
    const ID_FYKOS = 1;
    const ID_VYFUK = 2;

    /**
     * @return string
     */
    public function getContestSymbol(): string {
        return strtolower(Strings::webalize($this->name));
    }
}
