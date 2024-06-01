<?php

const LOG_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'logs';

function dd(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(PHP_EOL . '------------ PROCESS DIE INVOKED ------------' . PHP_EOL);
}

function printLine(string $text, int $subsetCount = 0, bool $appendLineBreak = false)
{
    print(str_repeat(' ', $subsetCount * 4) . '. ' . $text . ($appendLineBreak ? PHP_EOL : ' '));
}

function logFilePath(string $fileName)
{
    return LOG_DIR . DIRECTORY_SEPARATOR . $fileName;
}

/**
 * Get all processes
 * @return mixed
 */
function getAllProcessesData(&$dataBag)
{
    printLine('PROCESS LISTING STARTED ON ' . (new DateTime('now', new DateTimeZone('+0530')))->format('d M, Y \a\t h:i:s p'), 0, true);
    if (count(scandir(LOG_DIR)) == 2) {
        printLine('Fetching processes list ...', 1);
        $processString = shell_exec('tasklist /v /fo list');
        $lines = array_filter(explode("\n", $processString));
        print('Done. Found ' . number_format(count($lines)) . ' processes.' . PHP_EOL);
        file_put_contents(logFilePath('processList_' . time() . '.log'), $processString);
    } else {
        $processLogFiles = array_filter(scandir(LOG_DIR), function ($str) {
            return str_starts_with($str, 'processList_') && str_ends_with($str, '.log');
        });
        printLine('Found ' . number_format(count($processLogFiles)) . ' log file(s), reading the latest one (' . end($processLogFiles) . ') ...', 1);
        sort($processLogFiles);
        $processString = file_get_contents(end($processLogFiles));
        print('Done.' . PHP_EOL);
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

    printLine('Processing process list ... ', 2, true);
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
    if (!file_exists(LOG_DIR)) {
        mkdir(LOG_DIR, 0770, true);
    }
    $processDataBag = new stdClass;
    getAllProcessesData($processDataBag);
    dd([
        'keys' => implode(', ', array_keys((array)$processDataBag)),
        'totalProcessesDetected' => $processDataBag->count
    ]);
} catch (Error $error) {
    file_put_contents(logFilePath('error_' . time() . '.log'), $error);
    throw new Error(PHP_EOL . 'Something went wrong! Check error log.' . PHP_EOL);
}

print(PHP_EOL . '---------------- Operation complete ----------------' . PHP_EOL);
return true;
