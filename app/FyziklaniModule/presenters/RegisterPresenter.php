<?php

namespace FyziklaniModule;

use FKSDB\Components\Forms\Controls\Autocomplete\ReactPersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\FireLogger;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class RegisterPresenter extends BasePresenter {
    /**
     * @var SchoolProvider
     */
    private $schoolProvider;

    public function injectSchoolProvider(SchoolProvider $schoolProvider) {
        $this->schoolProvider = $schoolProvider;
    }

    /**
     * @var ReactPersonProvider
     */
    private $reactPersonProvider;

    public function injectReactPersonProvider(ReactPersonProvider $reactPersonProvider) {
        $this->reactPersonProvider = $reactPersonProvider;
    }

    /**
     * @var \ServicePerson
     */
    private $servicePerson;

    public function injectServicePerson(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleSchoolProvider(\ReactResponse $response, array $requestData) {
        $data = $this->schoolProvider->getFilteredItems($requestData['payload']);
        $response->setData($data);
        $response->setAct('school-provider');
        $this->sendResponse($response);
    }

    /**
     * @param \ReactResponse $response
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    private function handlePersonProvider(\ReactResponse $response, array $requestData) {

        $email = $requestData['email'];
        // $type = $requestData['form'];
        //'sara.byskova@email.cz'
        $data = $this->reactPersonProvider->getPersonByEmail($email
            , ['person.personId', 'personHistory.schoolId', 'personHistory.studyYear', 'personInfo.idNumber', 'person.familyName', 'person.otherName'], 2017);
        $data['accessKey'] = $requestData['accessKey'];
        $data['fields'] = [
            'person' => [
                'name' => 'Base info',
                'fields' => [
                    'person_id' => [
                        'required' => true,
                    ],
                    'family_name' => [
                        'required' => true,
                        'filled' => true,
                        'secure' => false,
                        'value' => 'Michal',
                        'readonly' => true,
                    ],
                    'other_name' => [
                        'required' => true,
                        'filled' => true,
                        'secure' => false,
                        'value' => 'Michal',
                        'readonly' => true,
                    ],
                ],
            ],
            'person_history' => [
                'name' => 'Person History',
                'fields' => [

                    'school_id' => [
                        'required' => true,
                        'filled' => true,
                        'secure' => true,
                        'value' => [],
                        'readonly' => true,
                    ],
                    'study_year' => [
                        'required' => true,
                        'filled' => true,
                        'secure' => false,
                        'value' => '9',
                        'readonly' => true,
                    ],
                ],
            ]

        ];
        $response->setData($data);
        $response->setAct('person-provider');
        $this->sendResponse($response);
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleTeamNameUnique(\ReactResponse $response, array $requestData) {
        $name = $requestData['name'];
        $count = $this->serviceFyziklaniTeam->getTable()->where('name=?', $name)->where('event_id', $this->getEventId())->count();

        $data = ['result' => true];
        if ($count) {
            $data['result'] = false;
            $response->addMessage(new \ReactMessage(_('Meno je už použité'), 'danger'));
        }
        $response->setData($data);
        $response->setAct('team-name-unique');
        $this->sendResponse($response);

    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleLangDownload(\ReactResponse $response) {
        $keys = ['Other name', 'Team name', 'Family name',
            'E-mail', 'School',
            'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť',
            'Opraviť hodnotu',
            'Study year',
            'Doprovodný program o ktorý mám zaujem.',
            'E-mail',
            'hledat',

        ];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = _($key);
        }
        $response->setAct('lang-downloader');
        $response->setData($data);
        $this->sendResponse($response);
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws JsonException
     * @throws BadRequestException
     */
    public function renderDefault() {

        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('data');
            FireLogger::log($this->getHttpRequest());
            $response = new \ReactResponse();
            switch ($this->getHttpRequest()->getPost('act')) {
                case 'school-provider':
                    $this->handleSchoolProvider($response, $data ?: []);
                    break;
                case 'person-provider' :
                    $this->handlePersonProvider($response, $data ?: []);
                    break;
                case 'team-name-unique':
                    $this->handleTeamNameUnique($response, $data ?: []);
                    break;
                case 'lang-downloader':
                    $this->handleLangDownload($response);
                    break;
            }

        } else {
            $this->template->data = [];
            $this->template->accDef = '[{"accId":1,"date":"2017-05-02","name":"Elf","price":{"eur":10,"kc":300}},{"accId":2,"date":"2017-05-03","name":"Elf","price":{"eur":10,"kc":300}},{"accId":3,"date":"2017-05-04","name":"Elf","price":{"eur":10,"kc":300}},{"accId":4,"date":"2017-05-05","name":"Elf","price":{"eur":10,"kc":300}},{"accId":5,"date":"2017-05-03","name":"Duo","price":{"eur":20,"kc":500}},{"accId":6,"date":"2017-05-04","name":"Duo","price":{"eur":20,"kc":500}},{"accId":7,"date":"2017-05-05","name":"Duo","price":{"eur":20,"kc":500}}]';

            $this->template->scheduleDef = Json::encode($this->getEvent()->getParameter('schedule'));
            $pDef = [
                [
                    'personSelector' => [
                        'type' => 'participant',
                        'index' => 0,
                        'accessKey' => 'participant[0]',
                    ],
                    'index' => 0,
                    'type' => 'participant',
                ], [
                    'personSelector' => [
                        'type' => 'participant',
                        'index' => 1,
                        'accessKey' => 'participant[1]',
                    ],
                    'index' => 1,
                    'type' => 'participant',
                ], [
                    'personSelector' => [
                        'type' => 'participant',
                        'index' => 2,
                        'accessKey' => 'participant[2]',
                    ],
                    'index' => 2,
                    'type' => 'participant',
                ], [
                    'personSelector' => [
                        'type' => 'participant',
                        'index' => 3,
                        'accessKey' => 'participant[3]',
                    ],
                    'index' => 3,
                    'type' => 'participant',
                ], [
                    'personSelector' => [
                        'type' => 'participant',
                        'index' => 4,
                        'accessKey' => 'participant[4]',
                    ],
                    'index' => 4,
                    'type' => 'participant',
                ], [
                    'personSelector' => [
                        'type' => 'teacher',
                        'index' => 0,
                        'accessKey' => 'teacher[0]',
                    ],
                    'index' => 0,
                    'type' => 'teacher',
                ],
            ];
            // $this->template->personsDef = '[{"fields":[],"index":0,"type":"participant"},{"fields":[],"index":1,"type":"participant"},{"fields":[],"index":2,"type":"participant"},{"fields":[],"index":3,"type":"participant"},{"fields":[],"index":4,"type":"participant"},{"fields":[],"index":0,"type":"teacher"}]';
            $this->template->personsDef = Json::encode($pDef);
            $this->template->studyYearsDef = Json::encode($this->getStudyYears());
        }

    }

    private function getStudyYears() {

        $hsYears = [];
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, 2017));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, 2017));
        }

        return [
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ];

    }
}
