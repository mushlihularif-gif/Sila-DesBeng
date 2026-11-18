@once
<script src="{{ asset('js/segidaerah-maps.js') }}?v={{ filemtime(public_path('js/segidaerah-maps.js')) }}"
        data-google-maps-key="{{ config('services.google_maps.api_key') }}"
        data-google-map-id="{{ config('services.google_maps.map_id', 'DEMO_MAP_ID') }}"></script>
@endonce
