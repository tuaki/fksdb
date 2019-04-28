<?php


namespace FKSDB\ValidationTest;

use FKSDB\Messages\Message;
use Nette\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class ValidationLog
 * @package FKSDB\ValidationTest
 */
class ValidationLog extends Message {
    /**
     * @var Html
     */
    public $detail;
    /**
     * @var string
     */
    public $testName;

    /**
     * ValidationLog constructor.
     * @param string $testName
     * @param string $message
     * @param string $level
     * @param Html|null $detail
     */
    public function __construct(string $testName, string $message, string $level, Html $detail = null) {
        parent::__construct($message, $level);
        $this->detail = $detail;
        $this->testName = $testName;
    }

    /**
     * @return string
     */
    public function mapLevelToIcon() {
        switch ($this->getLevel()) {
            case ValidationLog::LVL_DANGER:
                return 'fa fa-close';
            case ValidationLog::LVL_WARNING:
                return 'fa fa-warning';
            case ValidationLog::LVL_INFO:
                return 'fa fa-info';
            case ValidationLog::LVL_SUCCESS:
                return 'fa fa-check';
            default:
                throw new NotImplementedException(\sprintf('%s is not supported', $this->getLevel()));
        }
    }

    /**
     * @return Html
     */
    public function createHtmlIcon(): Html {
        $icon = Html::el('span');
        $icon->addAttributes(['class' => $this->mapLevelToIcon()]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $this->getLevel(),
            'title' => $this->getMessage(),
        ])->addHtml($icon);
    }
}
