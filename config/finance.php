<?php

return [
    // Procurement requests estimated above this amount are auto-flagged for
    // Treasurer review, in addition to the mandatory approval every request
    // already requires. 0 = no additional flagging.
    'procurement_approval_threshold' => env('PROCUREMENT_APPROVAL_THRESHOLD', 0),
];
