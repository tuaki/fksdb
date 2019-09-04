<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Schedule\GroupControl;
use FKSDB\Components\Controls\Schedule\ItemControl;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Schedule\GroupsGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Localization\ITranslator;

/**
 * Class ScheduleFactory
 * @package FKSDB\Components\Factories
 */
class ScheduleFactory {
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * ScheduleFactory constructor.
     * @param ITranslator $translator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ITranslator $translator, TableReflectionFactory $tableReflectionFactory) {
        $this->translator = $translator;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param ModelEvent $event
     * @return GroupsGrid
     */
    public function createGroupsGrid(ModelEvent $event): GroupsGrid {
        return new GroupsGrid($event, $this->tableReflectionFactory);
    }

    /**
     * @return ItemsGrid
     */
    public function createItemsGrid(): ItemsGrid {
        return new ItemsGrid($this->tableReflectionFactory);
    }

    /**
     * @return PersonsGrid
     */
    public function createPersonsGrid(): PersonsGrid {
        return new PersonsGrid($this->tableReflectionFactory);
    }

    /**
     * @return GroupControl
     */
    public function createGroupControl(): GroupControl {
        return new GroupControl($this->translator, $this->tableReflectionFactory);
    }

    /**
     * @return ItemControl
     */
    public function createItemControl(): ItemControl {
        return new ItemControl($this->translator, $this->tableReflectionFactory);
    }

}
