@php
    // Filtrar solo los spots que tienen imagen y URL
    $spotsArray = isset($spots) ? $spots->toArray() : [];
    $spotsArray = array_filter($spotsArray, function($spot) {
        return $spot && !empty($spot['image_url']) && !empty($spot['spot_url']);
    });
@endphp

@if(count($spotsArray) > 0)
<div class="container offers">
    <div class="row d-flex justify-content-center">
        @foreach(array_slice($spotsArray, 0, 2) as $index => $spot)
            <div class="col-12 col-md-6 col-sm-6 mb-3 image-offers-container">
                <a href="{{ $spot['spot_url'] }}" target="_blank" rel="noopener noreferrer">
                    <img class="image-offers" src="{{ $spot['image_url'] }}" alt="Anuncio {{ $index + 1 }}" width="100%"/>
                </a>
            </div>
        @endforeach
    </div>
    @if(count($spotsArray) > 2)
    <div class="row d-flex justify-content-center">
        @foreach(array_slice($spotsArray, 2, 2) as $index => $spot)
            <div class="col-12 col-md-6 col-sm-6 mb-3 image-offers-container">
                <a href="{{ $spot['spot_url'] }}" target="_blank" rel="noopener noreferrer">
                    <img class="image-offers" src="{{ $spot['image_url'] }}" alt="Anuncio {{ $index + 3 }}" width="100%"/>
                </a>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endif