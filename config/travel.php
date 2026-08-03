<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Travel Order Code Sequence Start
    |--------------------------------------------------------------------------
    |
    | When no TO codes exist yet for the current year, numbering starts after
    | this offset (legacy backfill). Set via TO_CODE_SEQUENCE_START in .env.
    |
    */

    'to_code_sequence_start' => (int) env('TO_CODE_SEQUENCE_START', 149),

];
