<?php

namespace FKSDB\Payment\Transition\Transitions;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPayment;
use FKSDB\Payment\PriceCalculator\PriceCalculatorFactory;
use FKSDB\Payment\SymbolGenerator\SymbolGeneratorFactory;
use FKSDB\Payment\Transition\PaymentMachine;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\Transition;
use FKSDB\Transitions\TransitionsFactory;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\DateTime;
use Nette\NotImplementedException;


class Fyziklani13Payment extends AbstractTransitionsGenerator {
    /**
     * @var SymbolGeneratorFactory
     */
    private $symbolGeneratorFactory;

    /**
     * @var PriceCalculatorFactory
     */
    private $priceCalculatorFactory;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, TransitionsFactory $transitionFactory, SymbolGeneratorFactory $symbolGeneratorFactory, PriceCalculatorFactory $priceCalculatorFactory) {
        parent::__construct($transitionFactory);
        $this->connection = $connection;
        $this->symbolGeneratorFactory = $symbolGeneratorFactory;
        $this->priceCalculatorFactory = $priceCalculatorFactory;
    }

    public function createTransitions(Machine &$machine) {
        if (!$machine instanceof PaymentMachine) {
            throw new BadRequestException('Očakvaná sa trieda PaymentMachine');
        }

        $this->addTransitionInitToNew($machine);
        $this->addTransitionNewToWaiting($machine);
        $this->addTransitionNewToCanceled($machine);
        $this->addTransitionWaitingToReceived($machine);
        $this->addTransitionWaitingToCancel($machine);
    }

    public function createMachine(ModelEvent $event): Machine {
        $machine = new PaymentMachine($this->connection);
        $machine->setSymbolGenerator($this->symbolGeneratorFactory->createGenerator($event));
        $machine->setPriceCalculator($this->priceCalculatorFactory->createCalculator($event));
        return $machine;
    }

    private function addTransitionInitToNew(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(Machine::STATE_INIT, ModelPayment::STATE_NEW, _('Pokračovať k sumarizácii'));
        $transition->setCondition(
            function () {
                return $this->transitionFactory->getConditionDateFrom(new DateTime('2018-01-01 00:00:00'));
            });
        $machine->addTransition($transition);
    }

    private function addTransitionNewToWaiting(PaymentMachine &$machine) {

        $options = (object)[
            'bcc' => 'fyziklani@fykos.cz',
            'from' => 'fyziklani@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];
        $transition = $this->transitionFactory->createTransition(
            ModelPayment::STATE_NEW,
            ModelPayment::STATE_WAITING,
            _('Potvrdiť platbu a napočítať cenu')
        );

        $transition->setType(Transition::TYPE_SUCCESS);
        $transition->setCondition(function (ModelPayment $eventPayment) {
            return $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit') ||
                $this->transitionFactory->getConditionOwnerAssertion($eventPayment->getPerson());
        });
        $transition->beforeExecuteClosures[] = function (ModelPayment &$modelPayment) use ($machine) {
            $modelPayment->update($machine->getSymbolGenerator()->create($modelPayment));
            $modelPayment->updatePrice($machine->getPriceCalculator());
        };
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/create', $options);

        $machine->addTransition($transition);
    }

    private function addTransitionNewToCanceled(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(ModelPayment::STATE_NEW, ModelPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function () {
            return true;
        });
        $transition->beforeExecuteClosures[] = $this->getClosureDeleteRows();
        $machine->addTransition($transition);
    }

    private function addTransitionWaitingToReceived(PaymentMachine &$machine) {
        $options = (object)[
            'bcc' => 'fyziklani@fykos.cz',
            'from' => 'fyziklani@fykos.cz',
            'subject' => 'prijali sme platbu'
        ];
        $transition = $this->transitionFactory->createTransition(ModelPayment::STATE_WAITING, ModelPayment::STATE_RECEIVED, _('Zaplatil'));
        $transition->beforeExecuteClosures[] = function (ModelPayment $modelPayment) {
            foreach ($modelPayment->getRelatedPersonAccommodation() as $personAccommodation) {
                $personAccommodation->updateState(ModelEventPersonAccommodation::STATUS_PAID);
            }
        };
        $transition->afterExecuteClosures[] = $this->transitionFactory->createMailCallback('fyziklani13/payment/receive', $options);

        $transition->setCondition(function (ModelPayment $eventPayment) {
            return $this->transitionFactory->getConditionDateBetween(new DateTime('2018-01-01 00:00:00'), new DateTime('2019-02-15 00:00:00'))
                && $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $transition->setType(Transition::TYPE_SUCCESS);
        $machine->addTransition($transition);
    }

    private function addTransitionWaitingToCancel(PaymentMachine &$machine) {
        $transition = $this->transitionFactory->createTransition(ModelPayment::STATE_WAITING, ModelPayment::STATE_CANCELED, _('Zrusit platbu'));
        $transition->setType(Transition::TYPE_DANGER);
        $transition->setCondition(function (ModelPayment $eventPayment) {
            $this->transitionFactory->getConditionEventRole($eventPayment->getEvent(), $eventPayment, 'org.edit');
        });
        $transition->beforeExecuteClosures[] = $this->getClosureDeleteRows();

        $machine->addTransition($transition);
    }

    private function getClosureDeleteRows(): \Closure {
        return function (ModelPayment $modelPayment) {
            foreach ($modelPayment->related(\DbNames::TAB_PAYMENT_ACCOMMODATION, 'payment_id') as $row) {
                $row->delete();
            }
        };
    }

    /**
     * @param string $type
     * @param $args
     * @return bool
     */
    private function getCondition(string $type, $args): bool {
        switch ($type) {
            case 'dateTo':
                return $this->transitionFactory->getConditionDateTo($args['dateTo']);
            case 'dataFrom':
                return $this->transitionFactory->getConditionDateFrom($args['dateFrom']);
            case 'dateBetween':
                return $this->transitionFactory->getConditionDateBetween($args['dateFrom'], $args['dateTo']);
            default:
                throw new NotImplementedException();

        }
    }
}
