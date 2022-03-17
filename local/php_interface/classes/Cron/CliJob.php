<?php

namespace App\Cron;

use Bitrix\Main\{Result, Diag\Debug};

/**
 * Class CliJob
 * @package App\Cron
 */
abstract class CliJob
{
    /**
     * CliJob constructor.
     * @param string $file Running file path
     * @throws \Exception
     */
    public function __construct(string $file)
    {
        $dir = realpath(pathinfo($file, PATHINFO_DIRNAME));
        $basename = pathinfo($file, PATHINFO_BASENAME);
        $filename = pathinfo($file, PATHINFO_FILENAME);

        // проверка на запуск одной копии скрипта
        $lockFile = $dir . '/' . $filename . '.lock';
        @$lockHandle = fopen($lockFile, 'a');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            throw new \Exception('Script ' . $basename . ' is already running, exiting' . PHP_EOL);
        }
        fwrite($lockHandle, getmypid());

        // запускаем работу с замером времени выполнения
        Debug::startTimeLabel($filename);
        echo 'Running ' . $basename . PHP_EOL;
        $this->run();
        Debug::endTimeLabel($filename);
        $timeLabels = Debug::getTimeLabels();
        $timeLabel = $timeLabels[$filename];

        // удаляем файл флага запуска скрипта
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        unlink($lockFile);

        file_put_contents(
            $dir . '/' . $filename . '.log',
            PHP_EOL . date('d.m.Y H:i:s') . PHP_EOL . var_export($timeLabel, true) . PHP_EOL,
            FILE_APPEND
        );

        echo Debug::dump($timeLabel, 'time', true);
        echo 'Script ' . $basename . ' job done' . PHP_EOL;
    }

    /**
     * @return mixed
     */
    abstract protected function run() : Result;
}