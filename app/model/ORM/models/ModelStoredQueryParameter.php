<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\InvalidStateException;
use PDO;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string type
 * @property integer default_integer
 * @property string default_string
 */
class ModelStoredQueryParameter extends AbstractModelSingle {

    const TYPE_INT = 'integer';
    const TYPE_STR = 'string';
    const TYPE_BOOL = 'bool';

    /**
     * @return int|string
     * @throws InvalidStateException
     */
    public function getDefaultValue() {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                return $this->default_integer;
            case self::TYPE_STR:
                return $this->default_string;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    /**
     * @param $value
     * @throws InvalidStateException
     */
    public function setDefaultValue($value) {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                $this->default_integer = (int)$value;
                break;
            case self::TYPE_STR:
                $this->default_string = $value;
                break;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    /**
     * @return int
     * @throws InvalidStateException
     */
    public function getPDOType() {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                return PDO::PARAM_INT;
            case self::TYPE_STR:
                return PDO::PARAM_STR;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

}
