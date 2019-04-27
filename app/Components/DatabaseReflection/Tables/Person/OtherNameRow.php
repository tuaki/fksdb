<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;

/**
 * Class OtherNameRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class OtherNameRow extends AbstractRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Other name');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }
}