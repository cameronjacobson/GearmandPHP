<?php

namespace GearmandPHP;

use \WindowSeat\EventHandlerInterface;
use \WindowSeat\EventInterface;
use \GearmandPHP\Event;

class EventHandler implements EventHandlerInterface
{
    public function handle(EventInterface $event){
		$job = $event->getEvent();
		if($worker = $this->findWorker($job)){
			// send job to worker
		}
		else{
			GearmandPHP::$priority_queue->insert($job,$job);
		}
		error_log(var_export($event->getEvent(),true));
	}

    public function createEvent($data = null){
		return new Event($data);
	}

	private function findWorker($job){
		$workers = $this->canDoTheWork($job);
	}

	private function canDoTheWork($job){
		$workers = array();
		foreach(GearmandPHP::$state['worker'] as $ident=>$worker){
			if(in_array($job->function_name, array_keys($worker['functions']))){
				$workers[$ident] = $worker;
			}
		}
		return $workers;
	}

	private function isImmediatelyAvailable($worker){
		switch($worker['state']){
			case 'sleeping':
			case 'busy':
				return false;
				break;
			default:
				return true;
				break;
		}
	}
}
