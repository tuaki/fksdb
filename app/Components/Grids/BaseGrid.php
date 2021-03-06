<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Controls\FormControl\FormControl;
use Nette\Application\UI\Form;
use Nette\InvalidStateException;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use NiftyGrid\Components\Button;
use NiftyGrid\Components\GlobalButton;
use NiftyGrid\Grid;
use NiftyGrid\GridPaginator;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class BaseGrid extends Grid {

    /** @persistent string */
    public $searchTerm;

    /**
     * @param $presenter
     */
    protected function configure($presenter) {
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.latte');
        /**
         * @var GridPaginator $paginator
         */
        $paginator = $this->getComponent('paginator');
        $paginator->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.latte');
    }

    /**
     * @param null $class
     * @return \Nette\Templating\ITemplate
     */
    protected function createTemplate($class = NULL): ITemplate {
        /**
         * @var GridPaginator $paginator
         * @var FileTemplate $template
         */
        $paginator = $this->getComponent('paginator');
        $paginator->getTemplate()->setTranslator($this->presenter->getTranslator());
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    /*     * *****************************
     * Extended rendering for the paginator
     * ***************************** */

    public function render() {
        $paginator = $this->getPaginator();

        // this has to be done already here (and in the parent call again :-( )
        if ($this->searchTerm) {
            $this->dataSource->applyFilter($this->searchTerm);
        }
        $count = $this->getCount();
        $this->getPaginator()->itemCount = $count;
        /*
         * Credits David Grudl.
         * @see http://addons.nette.org/cs/visualpaginator
         */
        $page = $paginator->page;
        if ($paginator->pageCount < 2) {
            $steps = array($page);
        } else {
            $arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
            $count = 4;
            $quotient = ($paginator->pageCount - 1) / $count;
            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $paginator->firstPage;
            }
            sort($arr);
            $steps = array_values(array_unique($arr));
        }
        $this->getComponent('paginator')->getTemplate()->steps = $steps;

        parent::render();
    }

    /*     * ******************************
     * Search
     * ****************************** */

    /**
     * @return bool
     */
    public function isSearchable(): bool {
        return $this->dataSource instanceof SearchableDataSource;
    }

    /**
     * @return FormControl
     */
    protected function createComponentSearchForm(): FormControl {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }
        $control = new FormControl();
        $form = $control->getForm();
        //$form = new Form();
        $form->setMethod(Form::GET);
        $form->addText('term')->setDefaultValue($this->searchTerm)->setAttribute('placeholder', _('Vyhledat'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->searchTerm = $values['term'];
            $this->dataSource->applyFilter($values['term']);
            // TODO is this vv needed? vv
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

    /*     * ***************************
     * Apperance
     * *************************** */

    /**
     * Adds button with Bootstrap CSS classes (default is 'default').
     * @param string $name
     * @param string $label
     * @return Button
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function addButton($name, $label = NULL): Button {
        $button = parent::addButton($name, $label);
        $button->setClass('btn btn-sm btn-secondary');
        return $button;
    }

    /**
     * @param $name
     * @param null $label
     * @return GlobalButton
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    public function addGlobalButton($name, $label = NULL): GlobalButton {
        $button = parent::addGlobalButton($name, $label);
        $button->setClass('btn btn-sm btn-primary');
        return $button;
    }

}
