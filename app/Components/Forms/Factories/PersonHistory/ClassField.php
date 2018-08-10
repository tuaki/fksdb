<?php

namespace FKSDB\Components\Forms\Factories\PersonHistory;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class ClassField extends TextInput implements \IReactField {

    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Třída'));
        $this->addRule(Form::MAX_LENGTH, null, 16);
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }

}
