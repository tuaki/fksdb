<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class PermissionDenied extends Control {
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * NoRecordsControl constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct();
        $this->translator = $translator;
    }

    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . '/PermissionDenied.latte');
        $this->template->render();
    }
}

