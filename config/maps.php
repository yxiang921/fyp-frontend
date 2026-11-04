<?php

return [
    // Browsers use our cached proxy so policy-block images are never retained
    // in the UI and upstream requests carry an identifiable application UA.
    'tile_url' => env('MAP_TILE_URL', '/map-tiles/{z}/{x}/{y}.png'),
    'upstream_url' => env('MAP_UPSTREAM_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'user_agent' => env('MAP_USER_AGENT', 'FindSight/1.0 ('.env('APP_URL', 'http://localhost').')'),
    'attribution' => env('MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
    'max_zoom' => (int) env('MAP_MAX_ZOOM', 19),
];
