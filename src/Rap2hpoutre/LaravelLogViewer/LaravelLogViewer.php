<?php
namespace Rap2hpoutre\LaravelLogViewer;

use Illuminate\Support\Facades\File;
use Psr\Log\LogLevel;
use ReflectionClass;

class LaravelLogViewer
{
    public static function all()
    {
        $log = array();

        $class = new ReflectionClass(new LogLevel);
        $log_levels = $class->getConstants();

        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';

        $log_file = storage_path() . '/logs/laravel.log';

        $file = File::get($log_file);

        preg_match_all($pattern, $file, $headings);

        $log_data = preg_split($pattern, $file);

        if ($log_data[0] < 1) {
            $trash = array_shift($log_data);
            unset($trash);
        }

        foreach ($headings as $h) {
            for ($i=0, $j = count($h); $i < $j; $i++) {
                foreach ($log_levels as $ll) {
                    if (strpos(strtolower($h[$i]), strtolower('.'.$ll))) {

                        $level = strtoupper($ll);

                        preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.' . $level . ': (.*?)( in .*?:[0-9]+)?$/', $h[$i], $current);

                        $log[] = array(
                            'level' => $ll,
                            'date' => $current[1],
                            'text' => $current[2],
                            'in_file' => isset($current[3]) ? $current[3] : null,
                            'stack' => $log_data[$i]
                        );
                    }
                }
            }
        }

        $log = array_reverse($log);
        return $log;
    }
}