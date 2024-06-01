<?php

$processBagData = new stdClass;

function dd(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(PHP_EOL . '------------ PROCESS DIE INVOKED ------------' . PHP_EOL);
}

/**
 * Get all processes
 * @return mixed
 */
function getAllProcessesData(&$dataBag)
{
    // $processString = shell_exec('tasklist /v /fo list');
    $processString = file_get_contents('processList.log');;
    $lines = array_filter(explode("\n", $processString));
    $process = new stdClass;
    $processBag = [];
    function processKeyValue($line, &$process)
    {
        $lineParts = array_map(function ($part) {
            return trim($part);
        }, explode(':', $line));
        $key = str_replace(' ', '_', strtolower(reset($lineParts)));
        $process->$key = next($lineParts);
    }
    foreach (array_chunk($lines, 9) as $lineBag) {
        foreach ($lineBag as $line) {
            processKeyValue($line, $process);
            array_push($processBag, $process);
        }
    }
    $dataBag->processes = $processBag;
    $dataBag->count = count($processBag);
}

getAllProcessesData($processBagData);
dd(array_keys((array)$processBagData));

print(PHP_EOL . '---------------- Operation complete ----------------' . PHP_EOL);
return true;
