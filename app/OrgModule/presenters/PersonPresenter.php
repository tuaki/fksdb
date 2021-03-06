<?php

namespace OrgModule;

use Authentication\AccountManager;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\ModelPerson;
use FormUtils;
use Mail\MailTemplateFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\NotImplementedException;
use Nette\Utils\Html;
use Persons\Deduplication\Merger;
use ServiceLogin;
use ServiceMPostContact;
use ServicePerson;
use ServicePersonHistory;
use ServicePersonInfo;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @deprecated Do not use this presenter to create/modify persons.
 *             It's better to use ReferencedId and ReferencedContainer
 *             inside the particular form.
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonPresenter extends EntityPresenter {

    const CONT_PERSON = 'person';
    const CONT_ADDRESSES = 'addresses';
    const CONT_PERSON_INFO = 'personInfo';
    const CONT_PERSON_HISTORY = 'personHistory';

    protected $modelResourceId = 'person';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServicePersonInfo
     */
    private $servicePersonInfo;

    /**
     * @var ServicePersonHistory
     */
    private $servicePersonHistory;

    /**
     * @var ServiceLogin
     */
    private $serviceLogin;

    /**
     * @var ServiceMPostContact
     */
    private $serviceMPostContact;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var Merger
     */
    private $personMerger;

    /**
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

    /**
     * @var ModelPerson
     */
    private $trunkPerson;

    /**
     * @var ModelPerson
     */
    private $mergedPerson;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var MailTemplateFactory
     */
    private $mailTemplateFactory;

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ServicePersonInfo $servicePersonInfo
     */
    public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo) {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    /**
     * @param ServicePersonHistory $servicePersonHistory
     */
    public function injectServicePersonHistory(ServicePersonHistory $servicePersonHistory) {
        $this->servicePersonHistory = $servicePersonHistory;
    }

    /**
     * @param ServiceLogin $serviceLogin
     */
    public function injectServiceLogin(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    /**
     * @param ServiceMPostContact $serviceMPostContact
     */
    public function injectServiceMPostContact(ServiceMPostContact $serviceMPostContact) {
        $this->serviceMPostContact = $serviceMPostContact;
    }

    /**
     * @param AddressFactory $addressFactory
     */
    public function injectAddressFactory(AddressFactory $addressFactory) {
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param PersonFactory $personFactory
     */
    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    /**
     * @param UniqueEmailFactory $uniqueEmailFactory
     */
    public function injectUniqueEmailFactory(UniqueEmailFactory $uniqueEmailFactory) {
        $this->uniqueEmailFactory = $uniqueEmailFactory;
    }

    /**
     * @param Merger $personMerger
     */
    public function injectPersonMerger(Merger $personMerger) {
        $this->personMerger = $personMerger;
    }

    /**
     * @param FlashDumpFactory $flashDumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    /**
     * @param AccountManager $accountManager
     */
    public function injectAccountManager(AccountManager $accountManager) {
        $this->accountManager = $accountManager;
    }

    /**
     * @param MailTemplateFactory $mailTemplateFactory
     */
    public function injectMailTemplateFactory(MailTemplateFactory $mailTemplateFactory) {
        $this->mailTemplateFactory = $mailTemplateFactory;
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws BadRequestException
     */
    public function authorizedMerge($trunkId, $mergedId) {
        $this->trunkPerson = $this->servicePerson->findByPrimary($trunkId);
        $this->mergedPerson = $this->servicePerson->findByPrimary($mergedId);
        if (!$this->trunkPerson || !$this->mergedPerson) {
            throw new BadRequestException('Neexistující osoba.', 404);
        }
        $authorized = $this->getContestAuthorizator()->isAllowed($this->trunkPerson, 'merge', $this->getSelectedContest()) &&
            $this->getContestAuthorizator()->isAllowed($this->mergedPerson, 'merge', $this->getSelectedContest());
        $this->setAuthorized($authorized);
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws BadRequestException
     */
    public function authorizedDontMerge($trunkId, $mergedId) {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /**
     * @param $trunkId
     * @param $mergedId
     */
    public function actionMerge($trunkId, $mergedId) {
        $this->personMerger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm( $this->getComponent('mergeForm')->getForm());
    }

    /**
     * @param $trunkId
     * @param $mergedId
     * @throws \Nette\Application\AbortException
     */
    public function actionDontMerge($trunkId, $mergedId) {
        $mergedPI = $this->servicePersonInfo->findByPrimary($mergedId);
        $mergedData = ['duplicates' => trim($mergedPI->duplicates . ",not-same($trunkId)", ',')];
        $this->servicePersonInfo->updateModel($mergedPI, $mergedData);
        $this->servicePersonInfo->save($mergedPI);

        $trunkPI = $this->servicePersonInfo->findByPrimary($trunkId);
        $trunkData = ['duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ',')];
        $this->servicePersonInfo->updateModel($trunkPI, $trunkData);
        $this->servicePersonInfo->save($trunkPI);

        $this->flashMessage(_('Osoby úspešně nesloučeny.'), self::FLASH_SUCCESS);
        $this->backLinkRedirect(true);
    }

    public function titleMerge() {
        $this->setTitle(sprintf(_('Sloučení osob %s (%d) a %s (%d)'), $this->trunkPerson->getFullName(), $this->trunkPerson->person_id, $this->mergedPerson->getFullName(), $this->mergedPerson->person_id));
    }

    public function titleList() {
        $this->setTitle(_('Osoby'));
    }

    public function titleCreate() {
        $this->setTitle(_('Založit osobu'));
    }

    /**
     * @param $name
     * @return mixed|void
     */
    protected function createComponentCreateComponent($name) {
        // So far, there's no use case that creates bare person.
        throw new NotImplementedException();
    }

    /**
     * @param $name
     * @return mixed|void
     */
    protected function createComponentEditComponent($name) {
        // Persons are edited via referenced person containers.
        throw new NotImplementedException();
    }

    /**
     * @param $name
     * @return FormControl
     */
    protected function createComponentMergeForm($name) {
        $control = new FormControl();
        $form = $control->getForm();

        $form->addSubmit('send', _('Sloučit osoby'))->getControlPrototype()->addAttributes(['class' => 'btn-lg']);

        $form->addSubmit('cancel', _('Storno'))
            ->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $form->onSuccess[] = array($this, 'handleMergeFormSuccess');
        return $control;
    }

    /**
     * @param Form $form
     */
    private function updateMergeForm(Form $form) {
        if (false && !$form->isSubmitted()) { // new form is without any conflict, we use it to clear the session
            $this->setMergeConflicts(null);
            return;
        }

        $conflicts = $this->getMergeConflicts();

        foreach ($conflicts as $table => $pairs) {
            $form->addGroup($table);
            $tableContainer = $form->addContainer($table);

            foreach ($pairs as $pairId => $data) {
                if (!isset($data[Merger::IDX_TRUNK])) {
                    continue;
                }

                $pairSuffix = '';
                if (count($pairs) > 1) {
                    $pairSuffix = " ($pairId)";
                }
                $pairContainer = $tableContainer->addContainer($pairId);
                foreach ($data[Merger::IDX_TRUNK] as $column => $value) {
                    if (isset($data[Merger::IDX_RESOLUTION]) && array_key_exists($column, $data[Merger::IDX_RESOLUTION])) {
                        $default = $data[Merger::IDX_RESOLUTION][$column];
                    } else {
                        $default = $value; // default is trunk
                    }

                    $textElement = $pairContainer->addText($column, $column . $pairSuffix)
                        ->setDefaultValue($default);

                    $description = Html::el('div');

                    $trunk = Html::el('div');
                    $trunk->class('mergeSource');
                    $trunk->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($value);
                    $elVal->class('value');
                    $trunk->add(_('Trunk') . ': ');
                    $trunk->add($elVal);
                    $description->add($trunk);

                    $merged = Html::el('div');
                    $merged->class('mergeSource');
                    $merged->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($data[Merger::IDX_MERGED][$column]);
                    $elVal->class('value');
                    $merged->add(_('Merged') . ': ');
                    $merged->add($elVal);
                    $description->add($merged);

                    $textElement->setOption('description', $description);
                }
            }
        }
        $this->registerJSFile('js/mergeForm.js');
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function handleMergeFormSuccess(Form $form) {
        if ($form['cancel']->isSubmittedBy()) {
            $this->setMergeConflicts(null); // flush the session
            $this->backLinkRedirect(true);
        }

        $values = $form->getValues();
        $values = FormUtils::emptyStrToNull($values);

        $merger = $this->personMerger;
        $merger->setConflictResolution($values);
        $logger = new MemoryLogger();
        $merger->setLogger($logger);
        if ($merger->merge()) {
            $this->setMergeConflicts(null); // flush the session
            $this->flashMessage(_('Osoby úspešně sloučeny.'), self::FLASH_SUCCESS);
            $flashDump = $this->flashDumpFactory->createPersonMerge();
            $flashDump->dump($logger, $this);
            $this->backLinkRedirect(true);
        } else {
            $this->setMergeConflicts($merger->getConflicts());
            $this->flashMessage(_('Je třeba ručně vyřešit konflikty.'), self::FLASH_INFO);
            $this->redirect('this'); //this is correct
        }
    }

    /**
     * @param $name
     * @return mixed|void
     */
    protected function createComponentGrid($name) {
        // So far, there's no use case that would list all persons.
        throw new NotImplementedException();
    }

    /**
     * @param $id
     * @return \AbstractModelSingle|\Nette\Database\Table\ActiveRow|null
     */
    protected function loadModel($id) {
        return $this->servicePerson->findByPrimary($id);
    }

    /*     * ******************************
     * Storing conflicts in session
     * ****************************** */

    /**
     * @param $conflicts
     */
    private function setMergeConflicts($conflicts) {
        $section = $this->session->getSection('conflicts');
        if ($conflicts === null) {
            $section->remove();
        } else {
            $section->data = $conflicts;
        }
    }

    /**
     * @return array
     */
    private function getMergeConflicts() {
        $section = $this->session->getSection('conflicts');
        if (isset($section->data)) {
            return $section->data;
        } else {
            return [];
        }
    }

}
