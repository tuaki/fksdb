<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\ValuePrinters\DatePrinter;
use FKSDB\Components\Forms\Controls\DateInputs\DateTimeLocalInput;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class BeginRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class BeginRow extends AbstractEventRowFactory {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event begin');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = new DateTimeLocalInput(self::getTitle());
        $control->addRule(Form::FILLED, _('%label je povinný.'));
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
