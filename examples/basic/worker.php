<?php

echo "Starting\n";

$gmworker= new GearmanWorker();

$gmworker->addServer('localhost', 4731);

$gmworker->addFunction("reverse", "reverse_fn");

print "Waiting for job...\n";
for($x=0; $x<10; $x++) {
	if($gmworker->work()) {
		if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
			echo "return_code: " . $gmworker->returnCode() . "\n";
			break;
		}
	}
	else{
		echo "no work: return_code: ".$gmworker->returnCode().PHP_EOL;
		sleep(1);
	}
}

function reverse_fn($job) {
  echo "Received job: " . $job->handle() . "\n";

  $workload = $job->workload();
  $workload_size = $job->workloadSize();

  echo "Workload: $workload ($workload_size)\n";

  # This status loop is not needed, just showing how it works
  for ($x= 0; $x < $workload_size; $x++)
  {
    echo "Sending status: " . ($x + 1) . "/$workload_size complete\n";
    $job->sendStatus($x, $workload_size);
    sleep(1);
  }

  $result= strrev($workload);
  echo "Result: $result\n";

  return $result;
}

function reverse_fn_fast($job) {
  return strrev($job->workload());
}
