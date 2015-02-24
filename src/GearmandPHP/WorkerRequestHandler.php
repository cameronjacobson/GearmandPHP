<?php

namespace GearmandPHP;

class WorkerRequestHandler
{
	// Request Types
	const CAN_DO = 1;
	const CANT_DO = 2;
	const RESET_ABILITIES = 3;
	const PRE_SLEEP = 4;
	const GRAB_JOB = 9;
	const WORK_STATUS = 12;
	const WORK_COMPLETE = 13;
	const WORK_FAIL = 14;
	const ECHO_REQ = 16;
	const SET_CLIENT_ID = 22;
	const CAN_DO_TIMEOUT = 23;
	const ALL_YOURS = 24;
	const WORK_EXCEPTION = 25;
	const OPTION_REQ = 26;
	const WORK_DATA = 28;
	const WORK_WARNING = 29;
	const GRAB_JOB_UNIQ = 30;

	// Response Types
	const NOOP = 6;
	const NO_JOB = 10;
	const JOB_ASSIGN = 11;
	const ECHO_RES = 17;
	const ERROR = 19;
	const OPTION_RES = 27;
	const JOB_ASSIGN_UNIQ = 31;

	private $bev;

	public function __construct($bev){
		$this->bev = $bev;
	}

	public function handle($headers,$data){
		$type = $headers['type'];
		switch($type){
			case self::CAN_DO:
				$this->handleCanDo($data);
				break;
			case self::CANT_DO:
				$this->handleCantDo($data);
				break;
			case self::RESET_ABILITIES:
				$this->handleResetAbilities($data);
				break;
			case self::PRE_SLEEP:
				$this->handlePreSleep($data);
				break;
			case self::GRAB_JOB:
				$this->handleGrabJob($data);
				break;
			case self::WORK_STATUS:
				$this->handleWorkStatus($data);
				break;
			case self::WORK_COMPLETE:
				$this->handleWorkComplete($data);
				break;
			case self::WORK_FAIL:
				$this->handleWorkFail($data);
				break;
			case self::ECHO_REQ:
				$this->handleEchoReq($data);
				break;
			case self::SET_CLIENT_ID:
				$this->handleSetClientID($data);
				break;
			case self::CAN_DO_TIMEOUT:
				$this->handleCanDoTimeout($data);
				break;
			case self::ALL_YOURS:
				$this->handleAllYours($data);
				break;
			case self::WORK_EXCEPTION:
				$this->handleWorkException($data);
				break;
			case self::OPTION_REQ:
				$this->handleOptionReq($data);
				break;
			case self::WORK_DATA:
				$this->handleWorkData($data);
				break;
			case self::WORK_WARNING:
				$this->handleWorkWarning($data);
				break;
			case self::GRAB_JOB_UNIQ:
				$this->handleGrabJobUniq($data);
				break;
			default:
				//INVALID WORKER REQUEST TYPE
				break;
		}
	}

	private function handleCanDo($data){
		$function_name = $data;
	}

	private function handleCantDo($data){
		$function_name = $data;
	}

	private function handleResetAbilities($data){
		// $data is empty
		// RESET "abilities" to empty
	}

	private function handlePreSleep($data){
		// $data is empty
		// Set "status" to "sleeping"
		//   which means server needs to wake up worker with "NOOP"
		//   if a job comes in that the worker can do
	}

	private function handleGrabJob($data){
		// $data is empty
		// server responds with "NO_JOB" or "JOB_ASSIGN"
	}

	private function handleWorkStatus($data){
		list($handle,$percent_numerator,$percent_denominator) = explode(0x00,$data);
		// relay "percentage complete" to client, and update on server
	}

	private function handleWorkComplete($data){
		list($handle,$data) = explode(0x00,$data);
		// notify server / clients that the job completed successfully
	}

	private function handleWorkFail($data){
		$handle = $data;
		// notify server / clients that job failed
	}

	private function handleEchoReq($data){
		$this->sendResponse(self::ECHO_RES,$data);
	}

	private function handleSetClientID($data){
		$client_id = $data;
		// unique string to identify the worker instance
	}

	private function handleCanDoTimeout($data){
		list($function_name,$timeout) = explode(0x00,$data);
		// same as "CAN_DO", but $timeout indicates how long the job can run
		// if the job takes longer than $timeout seconds, it will fail
	}

	private function handleAllYours($data){
		// $data is empty
		// notify server that the worker is connected exclusively
	}

	private function handleWorkException($data){
		list($handle,$data) = explode(0x00,$data);
		// notify server / clients that the job failed
		// $data is info about the exception
	}

	private function handleOptionReq($data){
		$option = $data;
		// currently only "exceptions" is a possibility here
		switch($option){
			case 'exceptions':
				// notify server it should forward "WORK_EXCEPTION" packets to client
				$this->sendResponse(self::OPTION_RES,$option);
				break;
		}
	}

	private function handleWorkData($data){
		list($handle,$data) = explode(0x00,$data);
		// supposed to relay progress info or job info to client
	}

	private function handleWorkWarning($data){
		list($handle,$data) = explode(0x00,$data);
		// relay "warning" data to the client
	}

	private function handleGrabJobUniq($data){
		// $data is empty
		// server responds with "NO_JOB" or "JOB_ASSIGN_UNIQ"
	}


	public function sendResponse($type, $message){

		$response = pack('c4',0x00,ord('R'),ord('E'),ord('S'));
		$response.= pack('N',$type);
		$response.= pack('N',strlen($message));
		$response.= $message;

		$output = $this->bev->output;
		return $output->add($response);
	}

}
