<?php

namespace Persons;

use Authentication\AccountManager;
use FKSDB\Components\Factories\ExtendedPersonWizardFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\WizardComponent;
use FormUtils;
use Mail\MailTemplateFactory;
use Mail\SendFailedException;
use ModelException;
use ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DateTime;
use ServiceLogin;
use ServicePerson;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractPersonHandler {

    /**
     * @var ServicePerson
     */
    protected $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    protected $servicePersonInfo;

    /**
     * @var ServiceLogin
     */
    protected $serviceLogin;

    /**
     * @var MailTemplateFactory
     */
    protected $mailTemplateFactory;

    /**
     * @var AccountManager
     */
    protected $accountManager;

    /**
     * @var ModelPerson
     */
    protected $person;

    function __construct(ServicePerson $servicePerson, ServicePersonInfo $servicePersonInfo, ServiceLogin $serviceLogin, MailTemplateFactory $mailTemplateFactory, AccountManager $accountManager) {
        $this->servicePerson = $servicePerson;
        $this->servicePersonInfo = $servicePersonInfo;
        $this->serviceLogin = $serviceLogin;
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->accountManager = $accountManager;
    }

    public final function loadPerson(WizardComponent $wizard) {
        $personData = $wizard->getData(ExtendedPersonWizardFactory::STEP_PERSON);
        $this->person = $this->getPersonFromPersonStep($personData);
        return $this->person;
    }

    public final function store(WizardComponent $wizard, Presenter $presenter) {
        $this->loadPerson($wizard);
        /*
         * Process data
         */
        $connection = $this->servicePerson->getConnection();
        try {
            if (!$connection->beginTransaction()) {
                throw new ModelException();
            }

            /*
             * Person
             */
            $this->servicePerson->save($this->person);

            $data = $wizard->getData(ExtendedPersonWizardFactory::STEP_DATA);

            /*
             * Login
             */
            $emailContainer = $data[ExtendedPersonWizardFactory::CONT_PERSON]; // email data are in person's container
            $email = $emailContainer['email'];
            $createLogin = $emailContainer[PersonFactory::CONT_LOGIN][PersonFactory::EL_CREATE_LOGIN];

            if ($email && !$this->person->getLogin() && $createLogin) {
                $lang = $emailContainer[PersonFactory::CONT_LOGIN][PersonFactory::EL_CREATE_LOGIN_LANG];
                $template = $this->mailTemplateFactory->createLoginInvitation($presenter, $lang);
                try {
                    $this->accountManager->createLoginWithInvitation($template, $this->person, $email);
                    $presenter->flashMessage(_('Zvací e-mail odeslán.'), $presenter::FLASH_INFO);
                } catch (SendFailedException $e) {
                    $presenter->flashMessage(_('Zvací e-mail se nepodařilo odeslat.'), $presenter::FLASH_ERROR);
                }
            }

            /*
             * Personal info
             */
            $personInfoData = $data[ExtendedPersonWizardFactory::CONT_PERSON_INFO];
            $personInfoData['email'] = $email;
            $personInfoData = FormUtils::emptyStrToNull($personInfoData);
            $personInfo = $this->person->getInfo();
            if (!$personInfo) {
                $personInfoData['agreed'] = $personInfoData['agreed'] ? new DateTime() : null;
                $personInfo = $this->servicePersonInfo->createNew($personInfoData);
                $personInfo->person_id = $this->person->person_id;
            } else {
                unset($personInfoData['agreed']); // do not overwrite in existing person_info
                $this->servicePersonInfo->updateModel($personInfo, $personInfoData);
            }
            $this->servicePersonInfo->save($personInfo);

            /*
             * Extension data
             */
            $this->storeExtendedData($data, $presenter);

            /*
             * Finalize
             */
            if (!$connection->commit()) {
                throw new ModelException();
            }
            $wizard->disposeData();
        } catch (ModelException $e) {
            $connection->rollBack();
            throw new PersonHandlerException(null, null, $e);
        }
    }

    abstract protected function storeExtendedData($data, Presenter $presenter);

    public function getPerson() {
        return $this->person;
    }

    /**
     * 
     * @param mixed $data
     * @return ModelPerson
     */
    private function getPersonFromPersonStep($data) {
        if ($data[ExtendedPersonWizardFactory::EL_PERSON_ID]) {
            $person = $this->servicePerson->findByPrimary($data[ExtendedPersonWizardFactory::EL_PERSON_ID]);
        } else {
            $dataPerson = $data[ExtendedPersonWizardFactory::CONT_PERSON];
            $dataPerson = FormUtils::emptyStrToNull($dataPerson);

            $person = $this->servicePerson->createNew($dataPerson);
        }
        return $person;
    }

}

class PersonHandlerException extends ModelException {
    
}