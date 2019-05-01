<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;

/**
 * Class NameRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class NameRow extends AbstractEventRowFactory {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Name');
    }

    /**
     * @return null|string
     */
    public function getDescription() {
        return _('U soustředka místo.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        $control = parent::createField();
        $control->addRule(Form::FILLED, _('%label je povinný.'))
            ->addRule(Form::MAX_LENGTH, null, 255)
            ->setOption('description', $this->getDescription());
        return $control;
    }
}
