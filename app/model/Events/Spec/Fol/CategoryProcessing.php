<?php

namespace Events\Spec\Fol;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Field;
use Events\Model\Holder\Holder;
use Events\Processings\AbstractProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\Components\Forms\Factories\Events\IOptionsProvider;
use Nette\ArrayHash;
use Nette\Forms\Form;
use ServiceSchool;
use YearCalculator;

/**
 * Class CategoryProcessing
 * @package Events\Spec\Fol
 */
class CategoryProcessing extends AbstractProcessing implements IOptionsProvider {

    const HIGH_SCHOOL_A = 'A';
    const HIGH_SCHOOL_B = 'B';
    const HIGH_SCHOOL_C = 'C';
    const ABROAD = 'F';
    const OPEN = 'O';

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServiceSchool
     */
    private $serviceSchool;
    private $categoryNames;

    private $rulesVersion;

    /**
     *
     * @param int $rulesVersion version 1 is up to year 2017, version 2 from 2018
     * @param YearCalculator $yearCalculator
     * @param ServiceSchool $serviceSchool
     */
    function __construct($rulesVersion, YearCalculator $yearCalculator, ServiceSchool $serviceSchool) {
        $this->yearCalculator = $yearCalculator;
        $this->serviceSchool = $serviceSchool;

        if (!in_array($rulesVersion, [1, 2])) {
            throw new \Nette\InvalidArgumentException(_("Neplatná hodnota \$rulesVersion."));
        }
        $this->rulesVersion = $rulesVersion;

        if ($this->rulesVersion == 1) {
            $this->categoryNames = [
                self::HIGH_SCHOOL_A => _('Středoškoláci A'),
                self::HIGH_SCHOOL_B => _('Středoškoláci B'),
                self::HIGH_SCHOOL_C => _('Středoškoláci C'),
                self::ABROAD => _('Zahraniční SŠ'),
                self::OPEN => _('Open'),
            ];
        }
        else if ($this->rulesVersion == 2) {
            $this->categoryNames = [
                self::HIGH_SCHOOL_A => _('Středoškoláci A'),
                self::HIGH_SCHOOL_B => _('Středoškoláci B'),
                self::HIGH_SCHOOL_C => _('Středoškoláci C'),
                self::OPEN => _('Open'),
            ];
        }
    }

    /**
     * @param $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return mixed|void
     */
    protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        if (!isset($values['team'])) {
            return;
        }

        $event = $holder->getEvent();
        $contest = $event->getEventType()->contest;
        $year = $event->year;
        $acYear = $this->yearCalculator->getAcademicYear($contest, $year);

        $participants = [];
        foreach ($holder as $name => $baseHolder) {
            if ($name == 'team') {
                continue;
            }
            $formControls = [
                'school_id' => $this->getControl("$name.person_id.person_history.school_id"),
                'study_year' => $this->getControl("$name.person_id.person_history.study_year"),
            ];
            $formControls['school_id'] = reset($formControls['school_id']);
            $formControls['study_year'] = reset($formControls['study_year']);

            $formValues = [
                'school_id' => ($formControls['school_id'] ? $formControls['school_id']->getValue() : null),
                'study_year' => ($formControls['study_year'] ? $formControls['study_year']->getValue() : null),
            ];

            if (!$formValues['school_id']) {
                if ($this->isBaseReallyEmpty($name)) {
                    continue;
                }
                $person = $baseHolder->getModel()->getMainModel()->person;
                $history = $person->related('person_history')->where('ac_year', $acYear)->fetch();
                $participantData = [
                    'school_id' => $history->school_id,
                    'study_year' => $history->study_year,
                ];
            } else {
                $participantData = $formValues;
            }
            $participants[] = $participantData;
        }

        $result = $values['team']['category'] = $this->getCategory($participants);

        $original = $holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT ? $holder->getPrimaryHolder()->getModel()->category : null;
        if ($original != $result) {
            $logger->log(sprintf(_('Tým zařazen do kategorie %s.'), $this->categoryNames[$result]), ILogger::INFO);
        }
    }

    /**
     *   Open (staří odkudkoliv - pokazí to i jeden člen týmu)
     *   Zahraniční
     *   ČR - A - (3,4]
     *   ČR - B - (2,3] - max. 2 ze 4. ročníku
     *   ČR - C - [0,2] - nikdo ze 4. ročníku, max. 2 z 3 ročníku
     * @param $competitors
     * @return string
     */
    private function getCategory($competitors) {
        // init stats
        $olds = 0;
        $year = [0, 0, 0, 0, 0]; //0 - ZŠ, 1..4 - SŠ
        $abroad = 0;
        // calculate stats
        foreach ($competitors as $competitor) {
            if (!$competitor['school_id']) { // for future
                $olds += 1;
            } else {
                $country = $this->serviceSchool->getTable()->select('address.region.country_iso')->where(['school_id' => $competitor['school_id']])->fetch();
                if (!in_array($country->country_iso, ['CZ', 'SK'])) {
                    $abroad += 1;
                }
            }

            if ($competitor['study_year'] === null) {
                $olds += 1;
            } else if ($competitor['study_year'] >= 1 && $competitor['study_year'] <= 4) {
                $year[(int) $competitor['study_year']] += 1;
            } else {
                $year[0] += 1; // ZŠ
            }
        }
        // evaluate stats
        if ($olds > 0) {
            return self::OPEN;
        } elseif ($this->rulesVersion == 1 && $abroad > 0) {
            return self::ABROAD;
        } else { //Czech/Slovak highschoolers (or lower)
            $sum = 0;
            $cnt = 0;
            for ($y = 0; $y <= 4; ++$y) {
                $sum += $year[$y] * $y;
                $cnt += $year[$y];
            }
            $avg = $sum / $cnt;
            if ($avg <= 2 && $year[4] == 0 && $year[3] <= 2) {
                return self::HIGH_SCHOOL_C;
            } elseif ($avg <= 3 && $year[4] <= 2) {
                return self::HIGH_SCHOOL_B;
            } else {
                return self::HIGH_SCHOOL_A;
            }
        }
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getOptions(Field $field) {
        return $this->categoryNames;
    }

}
