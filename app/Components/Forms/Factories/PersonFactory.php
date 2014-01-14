<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKS\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKS\Components\Forms\Controls\PersonId;
use FKS\Components\Forms\Controls\URLTextBox;
use FKS\Localization\GettextTranslator;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\PersonContainer;
use FKSDB\Components\Forms\Containers\PersonInfoContainer;
use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\Components\Forms\Rules\UniqueEmailFactory;
use ModelPerson;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use ServicePerson;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonFactory {
    // For person

    const SHOW_DISPLAY_NAME = 0x1;
    const SHOW_GENDER = 0x2;
    const DISABLED = 0x4;

    // For person_info
    /** @const Show iformation important for organizers. */
    const SHOW_ORG_INFO = 0x8;
    const SHOW_EMAIL = 0x10;
    const REQUIRE_AGREEMENT = 0x20;
    const SHOW_LOGIN_CREATION = 0x40;
    /** @const Display origin and agreement only (supplement to other form containers). */
    const SHOW_LIKE_SUPPLEMENT = 0x100;
    const REQUIRE_EMAIL = 0x200;

    // For person_history
    const REQUIRE_SCHOOL = 0x400;
    const REQUIRE_STUDY_YEAR = 0x800;
    /** @const Display school, study year and class only (supplement to other form containers). */
    const SHOW_LIKE_CONTESTANT = 0x1000;

    /* Encapsulation condition argument (workaround) */
    const IDX_CONTROL = 'control';
    const IDX_OPERATION = 'op';
    const IDX_VALUE = 'val';

    /* Subcontainers names */
    const CONT_LOGIN = 'logincr';

    /* Element names */
    const EL_CREATE_LOGIN = 'createLogin';
    const EL_CREATE_LOGIN_LANG = 'lang';

    /**
     *
     * @var GettextTranslator
     */
    private $translator;

    /**
     * @var UniqueEmailFactory
     */
    private $uniqueEmailFactory;

    /**
     * @var SchoolFactory
     */
    private $factorySchool;

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(GettextTranslator $translator, UniqueEmailFactory $uniqueEmailFactory, SchoolFactory $factorySchool, YearCalculator $yearCalculator, ServicePerson $servicePerson) {
        $this->translator = $translator;
        $this->uniqueEmailFactory = $uniqueEmailFactory;
        $this->factorySchool = $factorySchool;
        $this->yearCalculator = $yearCalculator;
        $this->servicePerson = $servicePerson;
    }

    public function createPerson($options = 0, ControlGroup $group = null, array $requiredCondition = null) {
        $disabled = (bool) ($options & self::DISABLED);

        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $control = $container->addText('other_name', _('Křestní jméno'))
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Křestní jméno je povinné.'));



        $control = $container->addText('family_name', _('Příjmení'))
                ->setDisabled($disabled)
                ->setOption('description', _('Příp. další jména oddělená mezerou.'));

        if ($requiredCondition) {
            $rules = $control->addConditionOn($requiredCondition[self::IDX_CONTROL], $requiredCondition[self::IDX_OPERATION], $requiredCondition[self::IDX_VALUE]);
        } else {
            $rules = $control;
        }
        $rules->addRule(Form::FILLED, _('Příjmení je povinné.'));



        if ($options & self::SHOW_DISPLAY_NAME) {
            $control = $container->addText('display_name', _('Zobrazované jméno'))
                    ->setDisabled($disabled)
                    ->setOption('description', _('Pouze pokud je odlišené od "jméno příjmení".'));
        }



        if ($options & self::SHOW_GENDER) {
            $control = $container->addRadioList('gender', _('Pohlaví'), array('M' => 'muž', 'F' => 'žena'))
                    ->setDefaultValue('M')
                    ->setDisabled($disabled);
        }

        return $container;
    }

    public function createPersonInfo($options = 0, ControlGroup $group = null, $emailRule = null) {
        $container = new PersonInfoContainer();
        $container->setCurrentGroup($group);

        if (!($options & self::SHOW_LIKE_SUPPLEMENT)) {
            if ($options & self::SHOW_EMAIL) {
                $this->appendEmailWithLogin($container, $emailRule, $options);
            }

            $container->addDatePicker('born', 'Datum narození');

            $container->addText('id_number', _('Číslo OP'))
                    ->setOption('description', _('U cizinců číslo pasu.'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('born_id', _('Rodné číslo'))
                    ->setOption('description', _('U cizinců prázdné.'))
                    ->addCondition(Form::FILLED)
                    ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));


            $container->addText('phone', _('Telefonní číslo'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('im', _('ICQ, Jabber, apod.'))
                    ->addRule(Form::MAX_LENGTH, null, 32);

            $container->addText('birthplace', _('Místo narození'))
                    ->setOption('description', _('Město a okres (kvůli diplomům).'))
                    ->addRule(Form::MAX_LENGTH, null, 255);



            if ($options & self::SHOW_ORG_INFO) {
                $container->addText('uk_login', _('Login UK'))
                        ->addRule(Form::MAX_LENGTH, null, 8);

                $container->addText('account', _('Číslo bankovního účtu'))
                        ->addRule(Form::MAX_LENGTH, null, 32);

                $container->addText('tex_signature', _('TeX identifikátor'))
                        ->addRule(Form::MAX_LENGTH, null, 32)
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');

                $container->addText('domain_alias', _('Jméno v doméně fykos.cz'))
                        ->addRule(Form::MAX_LENGTH, null, 32)
                        ->addCondition(Form::FILLED)
                        ->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');

                $container->addTextArea('career', _('Co právě dělá'))
                        ->setOption('description', _('Zobrazeno v síni slávy'));

                $url = new URLTextBox(_('Homepage'));
                $url->addRule(Form::MAX_LENGTH, null, 255);
                $container->addComponent($url, 'homepage');
            }

            $container->addTextArea('note', _('Poznámka'));
        }
        $container->addTextArea('origin', _('Jak jsi se o nás dozvěděl(a)?'));


        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->href = _("http://fykos.cz/doc/souhlas.pdf");

        $agreement = $container->addCheckbox('agreed', _('Souhlasím se zpracováním osobních údajů'))
                ->setOption('description', $link);

        if ($options & self::REQUIRE_AGREEMENT) {
            $agreement->addRule(Form::FILLED, _('Bez souhlasu nelze bohužel pokračovat.'));
        }


        return $container;
    }

    public function appendEmailWithLogin(Container $container, callable $emailRule = null, $options = 0) {
        $emailElement = $container->addText('email', _('E-mail'));
        if ($options & self::REQUIRE_EMAIL) {
            $emailElement->addRule(Form::FILLED, _('E-mail je třeba zadat.'));
        }

        $filledEmailCondition = $emailElement->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        if ($emailRule) {
            $filledEmailCondition->addRule($emailRule, _('Daný e-mail je již použit u někoho jiného.'));
        }

        if ($options & self::SHOW_LOGIN_CREATION) {
            $loginContainer = $container->addContainer(self::CONT_LOGIN);

            $createLogin = $loginContainer->addCheckbox(self::EL_CREATE_LOGIN, _('Vytvořit login'))
                    ->setDefaultValue(true)
                    ->setOption('description', _('Vytvoří login a pošle e-mail s instrukcemi pro první přihlášení.'));
            $emailElement->addConditionOn($createLogin, Form::FILLED)
                    ->addRule(Form::FILLED, _('Pro vytvoření loginu je třeba zadat e-mail.'));

            $langElement = $loginContainer->addSelect(self::EL_CREATE_LOGIN_LANG, _('Jazyk pozvánky'))
                    ->setItems($this->translator->getSupportedLanguages(), false)
                    ->setDefaultValue($this->translator->getLang());

            $createLogin->addCondition(Form::EQUAL, true);
            //TODO support in Nette         ->toggle($langElement->getHtmlId() . BootstrapRenderer::PAIR_ID_SUFFIX, true);
            //TODO support in Nette $filledEmailCondition->toggle($createLogin->getHtmlId() . BootstrapRenderer::PAIR_ID_SUFFIX, true);
        }
    }

    public function modifyLoginContainer(Container $container, ModelPerson $person) {

        $login = $person->getLogin();
        $personInfo = $person->getInfo();
        $hasEmail = $personInfo && isset($personInfo->email);
        $showLogin = !$login || !$hasEmail;

        //$container = $form[self::CONT_PERSON][PersonFactory::CONT_LOGIN];
        $loginContainer = $container[self::CONT_LOGIN];
        if (!$showLogin) {
            foreach ($loginContainer->getControls() as $control) {
                $control->setDisabled();
            }
        }
        if ($login) {
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setDefaultValue(true);
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setDisabled();
            $loginContainer[PersonFactory::EL_CREATE_LOGIN]->setOption('description', _('Login už existuje.'));
        }

        //$emailElement = $form[self::CONT_PERSON]['email'];
        $emailElement = $container['email'];
        $email = ($personInfo && isset($personInfo->email)) ? $personInfo->email : null;
        $emailElement->setDefaultValue($email);


        $emailRule = $this->uniqueEmailFactory->create($person);
        $emailElement->addRule($emailRule, _('Daný e-mail již někdo používá.'));
    }

    public function createPersonSelect($ajax, $label, IDataProvider $dataProvider, $renderMethod = null) {
        if ($renderMethod === null) {
            $renderMethod = 'return $("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        return $select;
    }

    public function createPersonHistory($options = 0, ControlGroup $group = null, $acYear = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);
        $acYear = $acYear ? : $this->yearCalculator->getCurrentAcademicYear();

        if ($options & self::REQUIRE_SCHOOL) {
            $school = $this->factorySchool->createSchoolSelect(SchoolFactory::SHOW_UNKNOWN_SCHOOL_HINT);
            $school->addRule(Form::FILLED, _('Je třeba zadat školu.'));
        } else {
            $school = $this->factorySchool->createSchoolSelect();
        }
        $container->addComponent($school, 'school_id');

        $studyYear = $this->createStudyYear($acYear);
        $container->addComponent($studyYear, 'study_year');

        if ($options & self::REQUIRE_STUDY_YEAR) {
            $studyYear->addRule(Form::FILLED, _('Je třeba zadat ročník.'));
        }


        $container->addText('class', _('Třída'))
                ->setOption('description', _('Kvůli případné školní korespondenci.'));

//       For later use.        
//        if (!($options & self::SHOW_LIKE_SUPPLEMENT)) {
//            
//        }
        return $container;
    }

    private function createStudyYear($acYear) {
        $studyYear = new SelectBox(_('Ročník'));

        $hsYears = array();
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $acYear + (5 - $study_year));
        }

        $primaryYears = array();
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $acYear + (5 - ($study_year - 9)));
        }

        $studyYear->setItems(array(
                    _('střední škola') => $hsYears,
                    _('základní škola nebo víceleté gymnázium') => $primaryYears,
                ))->setOption('description', _('Kvůli zařazení do kategorie.'))
                ->setPrompt(_('Zvolit ročník'));

        return $studyYear;
    }

    /*     * ***********************************************
     * NEW APPROACH
     * *********************************************** */

    const EX_HIDDEN = 'hidden';
    const EX_DISABLED = 'disabled';
    const EX_MODIFIABLE = 'modifiable';
    const SEARCH_EMAIL = 'email';
    const SEARCH_NAME = 'name';
    const SEARCH_NONE = 'none';

    public function createDynamicPerson($searchType, $allowClear, $filledFields, $fieldsDefinition, $acYear, $personProvider) {
        $hiddenField = new PersonId($this->servicePerson, $acYear, $filledFields, $allowClear);

        $container = new PersonContainer($hiddenField, $this, $personProvider, $this->servicePerson);
        $container->setAllowClear($allowClear);
        $container->setSearchType($searchType);

        $fieldControls = array();
        foreach ($fieldsDefinition as $sub => $fields) {
            $subcontainer = new Container();
            $container->addComponent($subcontainer, $sub);
            $fieldControls[$sub] = array();

            foreach ($fields as $fieldName => $required) {
                $control = $this->createField($sub, $fieldName);
                if ($required) {
                    $control->addConditionOn($hiddenField, Form::FILLED)->addRule(Form::FILLED, 'Pole %label je povinné.');
                }

                $subcontainer->addComponent($control, $fieldName);
                $fieldControls[$sub][$fieldName] = $control;
            }
        }



        return array(
            $hiddenField,
            $container,
        );
    }

    private function createField($sub, $field) {
        //TODO
        $control = new TextInput($field);

        return $control;
    }

}

