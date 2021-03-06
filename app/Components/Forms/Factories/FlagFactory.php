<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonFlag;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\Utils\Arrays;


/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class FlagFactory {

    /**
     * @param HiddenField|null $hiddenField
     * @param array $metadata
     * @return BaseControl
     */
    public function createFlag(HiddenField $hiddenField = null, $metadata = []): BaseControl {
        $control = $this->createSpamMff();

        if (Arrays::get($metadata, 'required', false)) {
            $conditioned = $control;
            if ($hiddenField) {
                $conditioned = $control->addConditionOn($hiddenField, Form::FILLED);
            }
            $conditioned->addRule(Form::FILLED, _('Pole %label je povinné.'));
        }
        if ($caption = Arrays::get($metadata, 'caption', null)) { // intentionally =
            $control->caption = $caption;
        }
        if ($description = Arrays::get($metadata, 'description', null)) { // intentionally =
            $control->setOption('description', $description);
        }
        return $control;
    }

    /**
     * @return PersonFlag
     */
    public function createSpamMff(): PersonFlag {
        $control = new PersonFlag(_('Přeji si dostávat informace o dění na MFF a akcích, které pořádáme'));
        return $control;
    }

}

