<?php

namespace FKSDB\Components\Forms\Factories\EventAccommodation;


use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\DateTimeBox;

class Factory {
    public function createForm() {
        $container = new ModelContainer();

        $container->addText('name', _('Name'))->setRequired(true);
        $container->addText('capacity', _('Capacity'))->setRequired(true);

        $container->addText('price_kc', _('Price Kč'));

        $container->addText('price_eur', _('Price €'));
        $container->addComponent(new DateTimeBox(_('Date')), 'date');
        return $container;
    }
}