<?php

return [
    'evidence_retention_days' => (int) env('EVIDENCE_RETENTION_DAYS', 180),
    'witness_retention_days' => (int) env('WITNESS_RETENTION_DAYS', 180),
    'access_log_retention_days' => (int) env('ACCESS_LOG_RETENTION_DAYS', 365),
    'face_duplicate_threshold' => (float) env('FACE_DUPLICATE_THRESHOLD', 0.65),
];
