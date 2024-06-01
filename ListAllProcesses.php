<?php

$processBagData = new stdClass;

function dd(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(PHP_EOL . '------------ PROCESS DIE INVOKED ------------' . PHP_EOL);
}

function printLine(string $text, int $subsetCount = 0, bool $appendLineBreak = true)
{
    print(str_repeat(' ', $subsetCount * 4) . '. ' . $text . ($appendLineBreak ? PHP_EOL : ' '));
}

/**
 * Get all processes
 * @return mixed
 */
function getAllProcessesData(&$dataBag)
{
    printLine('PROCESS LISTING STARTED ON ' . (new DateTime('now', new DateTimeZone('+0530')))->format('d M, Y \a\t h:i:s p'));
    if (!file_exists('processList.log')) {
        printLine('Fetching processes list ...', 1, false);
        $processString = shell_exec('tasklist /v /fo list');
        $lines = array_filter(explode("\n", $processString));
        print('Done. Found ' . number_format(count($lines)) . ' processes.' . PHP_EOL);
        file_put_contents('processList_' . time() . '.log', $processString);
    } else {
        $processLogFiles = array_filter(scandir(__DIR__), function ($str) {
            return str_starts_with($str, 'processList_') && str_ends_with($str, '.log');
        });
        $processString = file_get_contents(end($processLogFiles));
    }
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

// Function & main algotithm code
try {
    getAllProcessesData($processBagData);
    dd([
        'keys' => implode(', ', array_keys((array)$processBagData)),
        'totalProcessesDetected' => $processBagData->count
    ]);
} catch (Error $error) {
    file_put_contents('error_' . time() . '.log', $error);
    throw new Error(PHP_EOL . 'Something went wrong! Check error log.' . PHP_EOL);
}

print(PHP_EOL . '---------------- Operation complete ----------------' . PHP_EOL);
return true;
