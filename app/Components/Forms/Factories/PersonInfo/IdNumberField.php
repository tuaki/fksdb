<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyInput;
use Nette\Forms\Form;

class IdNumberField extends WriteonlyInput implements \IReactField {

    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Číslo OP'));
        $this->setOption('description', _('U cizinců číslo pasu.'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }

}
