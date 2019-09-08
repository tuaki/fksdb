<?php

namespace Events\Accommodation;

use Tester\Assert;

$container = require '../../bootstrap.php';

class ScheduleTest extends ScheduleTestCase {


    public function testRegistration() {
        Assert::equal('2', $this->connection->fetchColumn('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));

        $request = $this->createAccommodationRequest();
        $response = $this->fixture->run($request);
        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::equal('2', $this->connection->fetchColumn('SELECT count(*) FROM person_schedule WHERE schedule_item_id = ?', $this->itemId));
    }

    public function getAccommodationCapacity() {
        return 2;
    }
}


$testCase = new ScheduleTest($container);
$testCase->run();