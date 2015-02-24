<?php

namespace GearmandPHP;

use \WindowSeat\EventHandlerInterface;
use \WindowSeat\EventInterface;
use \GearmandPHP\Event;

class EventHandler implements EventHandlerInterface
{
    public function handle(EventInterface $event){
		error_log(var_export($event->getEvent(),true));
	}

    public function createEvent($data = null){
		return new Event($data);
	}
}
