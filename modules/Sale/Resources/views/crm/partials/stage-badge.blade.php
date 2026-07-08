@if(isset($stage) && $stage)
    <span class="badge" style="background-color: {{ $stage->color ?? '#6c757d' }}; color: #fff;">
        {{ $stage->name }}
    </span>
@elseif(isset($stages) && $stages)
    <span class="d-none">{{ $stages->count() }}</span>
@else
    <span class="badge bg-secondary">Sin stage</span>
@endif
