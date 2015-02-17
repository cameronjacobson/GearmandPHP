<?php

namespace GearmandPHP;

use \EventBase;
use \Exception;
use \GearmandPHP\EventStoreInterface;
use \GearmandPHP\EventStore;

class Config
{
	public $base;
	public $server;
	public $client;
	public $store;

	public function __construct($config){

		if(is_string($config)){
			switch(substr(strrchr($config, "."), 1)){
				case 'ini':
					$config = parse_ini_file($config,true);
					break;
				case 'json':
					$config = json_decode(file_get_contents($config));
					break;
				default:
					throw new Exception('GearmandPHP\Config config file: file extension is invalid');
					break;
			}
		}

		if(empty($config['base']) || !($config['base'] instanceof EventBase)){
			$config['base'] = new EventBase();
		}

		if(empty($config['server']['ip'])){
			@$config['server']['ip'] = '127.0.0.1';
		}

		if(empty($config['server']['client_port'])){
			$config['server']['client_port'] = 4730;
		}

		if(empty($config['server']['worker_port'])){
			$config['server']['worker_port'] = 4731;
		}

		if(empty($config['store']) || !($config['store'] instanceof EventStoreInterface)){
			if(empty($config['db'])){
				$config['db'] = array(
					'host'=>'localhost',
					'user'=>'gearmand',
					'pass'=>'password',
					'name'=>'GearmandPHP'
				);
			}
			$config['store'] = new EventStore(array('db'=>$config['db']));
		}

		$this->base = $config['base'];
		$this->server = $config['server'];
		$this->store = $config['store'];
	}
}
