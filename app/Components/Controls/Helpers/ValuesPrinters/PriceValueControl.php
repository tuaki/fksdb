<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\Payment\IPaymentModel;
use FKSDB\Payment\Price;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class PriceValueControl extends AbstractValueControl {
    /**
     * @param IPaymentModel|Price $model
     * @param string $title
     * @param string $accessKey
     * @param bool $hasPermissions
     */
    public function render($model, string $title, string $accessKey, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        if ($model instanceof Price) {
            $price = $model;
        } else {
            $price = null;
            if ($model->{$accessKey}) {
                $price = $model->getPrice();
            }
        }
        $this->template->price = $price;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PriceValue.latte');
        $this->template->render();
    }

    /**
     * @param IPaymentModel|Price|AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     * @throws \FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException
     */
    public function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        if ($model instanceof Price) {
            $price = $model;
        } else {
            $price = $model->getPrice();
        }
        return Html::el('span')->addText($price->__toString());
    }
    /**
     * @param IPaymentModel|Price|AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     * @throws \FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException
     */
    protected static function getHtmlStatic(AbstractModelSingle $model, string $accessKey): Html {
        if ($model instanceof Price) {
            $price = $model;
        } else {
            $price = $model->getPrice();
        }
        return Html::el('span')->addText($price->__toString());
    }
}
