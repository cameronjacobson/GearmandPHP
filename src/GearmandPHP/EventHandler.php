<?php

namespace GearmandPHP;

use \WindowSeat\EventHandlerInterface;
use \WindowSeat\EventInterface;
use \GearmandPHP\Event;
use \GearmandPHP\Job;

class EventHandler implements EventHandlerInterface
{
	public function handle(EventInterface $event){
		$job = $event->getEvent();
		GearmandPHP::$priority_queue->insert($job,$job);
		foreach(GearmandPHP::$state['worker'] as $ident=>$worker){
			if($this->workerIsSleeping($worker) && $this->workerCanDoJob($ident, $worker,$job)){
				GearmandPHP::$state['worker'][$ident]->sendResponse(WorkerRequestHandler::NOOP, '');
			}
		}
	}

	private function workerCanDoJob($worker, $job){
		return !empty($worker['functions'][$job->function_name]);
	}

	private function workerIsSleeping($worker){
		return !empty($worker['state']) && ($worker['state'] === 'sleeping');
	}

	public function createEvent($event_id, Job $job = null){
		if(!empty($job)){
			$job->uuid = $event_id;
		}
		return new Event($job);
	}
}
