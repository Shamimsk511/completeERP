@props(['title', 'value', 'icon', 'trend' => null, 'color' => 'primary', 'link' => '#'])

<div class="col-lg-3 col-md-6">
    <div class="stats-card stats-card-{{ $color }}">
        <div class="stats-icon">
            <i class="fas fa-{{ $icon }}"></i>
        </div>
        <div class="stats-content">
            <h3 class="stats-number">{{ $value }}</h3>
            <p class="stats-label">{{ $title }}</p>
            @if($trend)
                <div class="stats-trend">
                    {!! $trend !!}
                </div>
            @endif
        </div>
        <a href="{{ $link }}" class="stats-link">
            View Details <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>