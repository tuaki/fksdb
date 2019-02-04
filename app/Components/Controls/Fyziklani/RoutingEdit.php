<?php

namespace FKSDB\Components\Controls\Fyziklani;

use Nette\Utils\Json;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniReactControl {

    public function getData(): string {

        return Json::encode([
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->event),
            'rooms' => $this->getRooms(),
        ]);
    }

    public function getMode(): string {
        return '';
    }

    public function getComponentName(): string {
        return 'routing';
    }

    public function getActions(): array {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    public function handleSave() {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
        $response = new \ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new \ReactMessage(_('Zmeny boli uložené'), \BasePresenter::FLASH_SUCCESS));
        $this->getPresenter()->sendResponse($response);
    }
}