<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

class LinkedinIdField extends TextInput {
    public function __construct() {
        parent::__construct(_('Linkedin Id'));
    }
}