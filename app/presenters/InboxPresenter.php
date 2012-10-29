<?php

use Nette\Application\UI\Form;

class InboxPresenter extends TaskTimesContestantPresenter {

    public function renderDefault() {
        $this->template->contestants = $this->getContestants();
        $this->template->tasks = $this->getTasks()->fetchPairs('task_id');
    }

    protected function createComponentInboxForm($name) {
        $form = new Form($this, $name);

        $contestants = $this->getContestants();
        $tasks = $this->getTasks();
        $submitsTable = $this->getSubmitsTable();

        $grid = $form->addContainer('grid');
        foreach ($contestants as $contestant) {
            $container = $grid->addContainer($contestant->ct_id);

            foreach ($tasks as $task) {
                $subcontainer = $container->addContainer($task->task_id);
                $text = $subcontainer->addText('submitted_on', null, 8);
                $note = $subcontainer->addText('note', null, 4);
                $note->getControlPrototype()->style('display:none');
                if (isset($submitsTable[$contestant->ct_id][$task->task_id])) {
                    $submit = $submitsTable[$contestant->ct_id][$task->task_id];
                    $text->setDefaultValue($submit->submitted_on);
                    $note->setDefaultValue($submit->note);
                }
            }
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');
    }

    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();
        $grid = $values['grid'];
        Nette\Diagnostics\Debugger::log(Nette\Diagnostics\Debugger::dump($grid, true));
        $submitsTable = $this->getSubmitsTable();
        $serviceSubmit = $this->context->getService('ServiceSubmit');
        $serviceSubmit->getConnection()->beginTransaction();

        foreach ($grid as $ct_id => $tasks) {
            foreach ($tasks as $task_id => $elements) {
                if (isset($submitsTable[$ct_id][$task_id])) { // is in the table
                    $submit = $submitsTable[$ct_id][$task_id];
                } else {
                    $submit = $serviceSubmit->createNew(array(
                        'ct_id' => $ct_id,
                        'task_id' => $task_id,
                        'source' => ModelSubmit::SOURCE_POST,
                            ));
                }

                $submit->note = $elements['note'];
                if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
                    $submit->submitted_on = $elements['submitted_on'];
                }

                if ($submit->isEmpty() && $submit->source != ModelSubmit::SOURCE_UPLOAD) {
                    $serviceSubmit->dispose($submit);
                } else {
                    $serviceSubmit->save($submit);
                }
            }
        }
        $serviceSubmit->getConnection()->commit();
        $this->flashMessage('Informace o řešeních uložena.');
        $this->redirect('this');
    }

}
