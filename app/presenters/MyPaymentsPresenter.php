<?php

use FKSDB\Components\Grids\Payment\MyPaymentGrid;

/**
 * Class MyPaymentsPresenter
 */
class MyPaymentsPresenter extends AuthenticatedPresenter {
    /**
     * @var \ServicePayment
     */
    private $servicePayment;

    /**
     * @param ServicePayment $servicePayment
     */
    /**
     * @param ServicePayment $servicePayment
     */
    public function injectServicePayment(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    public function titleDefault() {
        $this->setTitle(_('My payments'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @return MyPaymentGrid
     */
    /**
     * @return MyPaymentGrid
     */
    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->servicePayment);
    }

}
