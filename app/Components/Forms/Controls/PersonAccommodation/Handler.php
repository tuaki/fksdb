<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Messages\Message;
use Nette\Diagnostics\Debugger;

class Handler {
    private $serviceEventPersonAccommodation;
    private $serviceEventAccommodation;

    public function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    /**
     * @param \Nette\ArrayHash $data
     * @param \ModelPerson $person
     * @param integer $eventId
     * @return Message[]
     */
    public function prepareAndUpdate(\Nette\ArrayHash $data, \ModelPerson $person, $eventId) {
        $messages = [];
        $oldRows = $this->serviceEventPersonAccommodation->getTable()->where('person_id', $person->person_id)->where('event_accommodation.event_id', $eventId);

        $newAccommodationIds = $this->prepareData($data);
        /**
         * @var $row \ModelEventPersonAccommodation
         */
        foreach ($oldRows as $row) {
            if (in_array($row->event_accommodation_id, $newAccommodationIds)) {
                // do nothing
                $index = array_search($row->event_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                $row->delete();
            }
        }
        foreach ($newAccommodationIds as $id) {
            $model = $this->serviceEventPersonAccommodation->createNew(['person_id' => $person->person_id, 'event_accommodation_id' => $id]);
            $query = $this->serviceEventAccommodation->findByPrimary($id);
            $eventAccommodation = \ModelEventAccommodation::createFromTableRow($query);
            if ($eventAccommodation->getAvailableCapacity() > 0) {
                $this->serviceEventPersonAccommodation->save($model);
            } else {
                //$model->delete();
                $messages[] = new Message(sprintf(_('Osobu %s sa nepodarilo ubytovať na hotely "%s" v dni %s'),
                    $person->getFullName(),
                    $eventAccommodation->name,
                    $eventAccommodation->date->format(\ModelEventAccommodation::ACC_DATE_FORMAT)
                ), 'danger');
            }
        }
        Debugger::barDump($messages, 'messages');
        return $messages;
    }

    /**
     * @param \Nette\ArrayHash $data
     * @return integer[]
     */
    private function prepareData(\Nette\ArrayHash $data) {
        foreach ($data as $type => $datum) {
            switch ($type) {
                case Matrix::ResolutionId:
                    $data = (array)json_decode($datum);
                    break;
                default:
                    throw new \Nette\NotImplementedException(sprintf(_('Type "%s" is not implement.'), $type));
            }
        }

        return array_values($data);
    }
}
