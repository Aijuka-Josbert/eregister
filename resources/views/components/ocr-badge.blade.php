@props(['status' => 'pending'])

@php
    $config = match($status) {
        'completed' => ['label' => 'OCR Indexed', 'bg' => '#E6F4EA', 'text' => '#21865B', 'icon' => 'bi-check2-circle'],
        'processing' => ['label' => 'OCR Processing', 'bg' => '#E0F2FE', 'text' => '#3283B8', 'icon' => 'bi-hourglass-split'],
        'pending' => ['label' => 'OCR Queued', 'bg' => '#FEF3C7', 'text' => '#D99A2B', 'icon' => 'bi-clock-history'],
        'failed' => ['label' => 'OCR Failed', 'bg' => '#FEE2E2', 'text' => '#C74646', 'icon' => 'bi-exclamation-triangle'],
        'skipped' => ['label' => 'Metadata Only', 'bg' => '#F1F5F9', 'text' => '#64748B', 'icon' => 'bi-dash-circle'],
        default => ['label' => ucfirst($status), 'bg' => '#F1F5F9', 'text' => '#64748B', 'icon' => 'bi-info-circle'],
    };
@endphp

<span class="badge rounded-pill fw-medium px-2 py-1" style="background-color: {{ $config['bg'] }}; color: {{ $config['text'] }}; font-size: 0.72rem;">
    <i class="bi {{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
</span>
