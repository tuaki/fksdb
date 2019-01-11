<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

abstract class IPhoneField extends WriteOnlyInput {

    public function __construct($label) {
        parent::__construct($label);
        $this->setAttribute("placeholder", 've tvaru +420123456789');
        $this->addRule(Form::MAX_LENGTH, null, 32);
        $this->addCondition(Form::FILLED)
            ->addRule(Form::PATTERN, _('%label smí obsahovat jen číslice a musí být v mezinárodím tvaru začínajícím +421 nebo +420.'), '/\+42[01]\d{9}/');
    }
}
