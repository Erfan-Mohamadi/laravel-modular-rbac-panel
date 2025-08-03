<?php

namespace App\Helpers;

class Helper
{
    /**
     * Add an activity log entry.
     *
     * @param  string  $logName  The log name/category (e.g. 'تخصص‌ها')
     * @param  object  $model  The model instance related to the log
     * @param  string  $message  The log message (e.g. 'تخصص ثبت شد')
     * @param  array  $properties  Associative array of properties to add with the log
     */
    public static function addToLog(string $logName, object $model, string $message, array $properties = [])
    {
        activity($logName)
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }
}
