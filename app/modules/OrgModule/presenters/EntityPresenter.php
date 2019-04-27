<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\ORM\IModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Abstract functionality for basic CRUD.
 *   - check ACL
 *   - fill default form values
 *   - handling submitted data must be implemented in descendants
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EntityPresenter extends BasePresenter {

    const COMP_EDIT_FORM = 'editComponent';
    const COMP_CREATE_FORM = 'createComponent';
    const COMP_GRID = 'grid';
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var IModel
     */
    private $model;

    /**
     * Name of the resource that is tested in operations.
     * @var string
     */
    protected $modelResourceId;

    /**
     * @throws BadRequestException
     */
    public function authorizedCreate() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->modelResourceId, 'create', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedEdit($id) {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->modelResourceId, 'list', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedDelete($id) {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($this->getModel(), 'delete', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function renderEdit($id) {
        $component = $this->getComponent(self::COMP_EDIT_FORM);
        $form = ($component instanceof FormControl) ? $component->getForm() : $component;
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @throws BadRequestException
     */
    public function renderCreate() {
        $component = $this->getComponent(self::COMP_CREATE_FORM);
        $form = ($component instanceof FormControl) ? $component->getForm() : $component;
        $this->setDefaults($this->getModel(), $form);
    }

    /**
     * @return \FKSDB\ORM\AbstractModelSingle|null|IModel
     * @deprecated
     */
    public final function getModel() {
        if (!$this->model) {
            $this->model = $this->getParameter('id') ? $this->loadModel($this->getParameter('id')) : null;
        }
        return $this->model;
    }

    /**
     * @param int $id
     * @return \FKSDB\ORM\AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function getModel2(int $id = null) {
        if (!$this->model) {
            $model = $this->loadModel($id ?: $this->id);
            if (!$model) {
                throw new BadRequestException('Neexistující model.', 404);
            }
            $this->model = $model;
        }
        return $this->model;
    }

    /**
     * @param IModel|null $model
     * @param Form $form
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        if (!$model) {
            return;
        }
        $form->setDefaults($model->toArray());
    }

    /**
     * @param $id
     * @return \FKSDB\ORM\AbstractModelSingle
     */
    abstract protected function loadModel($id);

    /**
     * @param $name
     * @return mixed
     */
    abstract protected function createComponentEditComponent($name);

    /**
     * @param $name
     * @return mixed
     */
    abstract protected function createComponentCreateComponent($name);

    /**
     * @param $name
     * @return mixed
     */
    abstract protected function createComponentGrid($name);
}