<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\ArrayHash;
use ORM\IModel;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedHandler {

    const RESOLUTION_OVERWRITE = 'overwrite';
    const RESOLUTION_KEEP = 'keep';
    const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution();

    public function setResolution($resolution);

    public function update(IModel $model, ArrayHash $values);

    public function createFromValues(ArrayHash $values);

    public function isSecondaryKey($field);

    /**
     * @param string $field
     * @param mixed $key
     * @return IModel
     */
    public function findBySecondaryKey($field, $key);
}

class ModelDataConflictException extends RuntimeException {

    /** @var ArrayHash */
    private $conflicts;

    /** @var ReferencedId */
    private $referencedId;

    public function __construct($conflicts, $code = null, $previous = null) {
        parent::__construct(null, $code, $previous);
        $this->conflicts = $conflicts;
    }

    public function getConflicts() {
        return $this->conflicts;
    }

    public function getReferencedId() {
        return $this->referencedId;
    }

    public function setReferencedId(ReferencedId $referencedId) {
        $this->referencedId = $referencedId;
    }

}
