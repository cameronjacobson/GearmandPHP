<?php

namespace GearmandPHP

use \EventBase;
use \EventUtil;
use \EventListener;
use \Event;
use \WindowSeat\WindowSeat;
use \GearmandPHP\Config;

class Gearmand
{
	private $config;
	private $listener;
	private $base;
	private $persistent_store;

	public function __construct(Config $config){
		$this->config = $config;

		// TODO:
		// Are we recovering from crash?
		// 1) Look at persistent_store and see if there is anything in changes feed
		// 2) Once updated, re-send unfinished jobs
		// 3) Proceed with normal setup

		self::$conn = array('worker'=>array(),'client'=>array());
		$this->base = $config->base;
		$this->persistent_store = $config->store;

		$this->client_listener = new EventListener($this->base,
			array($this, 'clientConnect'), $this->base,
			EventListener::OPT_CLOSE_ON_FREE | EventListener::OPT_REUSEABLE, -1,
			$config->server['ip'].':'.$config->server['client_port']
		);

		$this->worker_listener = new EventListener($this->base,
			array($this, 'workerConnect'), $this->base,
			EventListener::OPT_CLOSE_ON_FREE | EventListener::OPT_REUSEABLE, -1,
			$config->server['ip'].':'.$config->server['worker_port']
		);

		$this->client_listener->setErrorCallback(array($this, "accept_error_cb"));
		$this->worker_listener->setErrorCallback(array($this, "accept_error_cb"));

	}

	public function loop(){
		$this->base->loop();
	}

	public function __destruct() {
		foreach (self::$conn as &$c) $c = NULL;
	}

	public function dispatch() {
		$this->base->dispatch();
	}

	public function clientConnect($listener, $fd, $address, $ctx) {
		$base = $this->base;
		$ident = $this->getUUID();
		self::$conn['client'][$ident] = new ClientConnection($base, $fd, $ident, $this->persistent_store);
	}

	public function workerConnect($listener, $fd, $address, $ctx) {
		$base = $this->base;
		$ident = $this->getUUID();
		self::$conn['worker'][$ident] = new WorkerConnection($base, $fd, $ident, $this->persistent_store);
	}

	public function accept_error_cb($listener, $ctx) {
		$base = $this->base;

		fprintf(STDERR, "Got an error %d (%s) on the listener. "
			."Shutting down.\n",
			EventUtil::getLastSocketErrno(),
			EventUtil::getLastSocketError());

		$base->exit(NULL);
	}

	private function getUUID(){
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	private function E($val){
		error_log(var_export($val,true));
	}
}
