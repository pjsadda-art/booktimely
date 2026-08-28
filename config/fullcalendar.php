<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scheduler License Key
    |--------------------------------------------------------------------------
    |
    | The Booking V2 calendar uses the FullCalendar premium Scheduler plugin for
    | its `resourceTimeGridDay` view (one vertical day column per staff member).
    | Drop your purchased key in .env as FULLCALENDAR_LICENSE_KEY. The default
    | below is FullCalendar's own open-source key, which renders the calendar
    | with a "license" watermark but is otherwise fully functional.
    |
    */

    'scheduler_license_key' => env('FULLCALENDAR_LICENSE_KEY', 'GPL-My-Project-Is-Open-Source'),

    /*
    |--------------------------------------------------------------------------
    | Default Slot Interval (minutes)
    |--------------------------------------------------------------------------
    |
    | Granularity of the calendar's time grid. A business may override this with
    | the `calendar_slot_interval` company setting; this value is the fallback
    | when no such setting exists. Values below 5 are clamped to 5 by the
    | controller to keep the grid renderable.
    |
    */

    'slot_interval' => env('FULLCALENDAR_SLOT_INTERVAL', 15),

];
