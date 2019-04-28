<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class EndRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class EndRow extends AbstractRow {
    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event end');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new DateTimeLocalInput(self::getTitle());
        $control->addRule(Form::FILLED, _('%label je povinný.'))
            ->setOption('description', _('U jednodenních akcí shodný se začátkem.'));
        return $control;
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        return (new DatePrinter('d.m.Y'))($model->{$fieldName});
    }
}
