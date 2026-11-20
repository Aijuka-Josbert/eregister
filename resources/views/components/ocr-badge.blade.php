@props(['status' => 'pending'])

@php
    $config = match($status) {
        'completed' => ['label' => 'OCR Indexed', 'bg' => '#DAE5D4', 'text' => '#2B4430', 'border' => '#7F9B7D', 'icon' => 'bi-check2-circle'],
        'processing' => ['label' => 'OCR Processing', 'bg' => '#F4ECD8', 'text' => '#8A6D3B', 'border' => '#D4C39A', 'icon' => 'bi-hourglass-split'],
        'pending' => ['label' => 'OCR Queued', 'bg' => '#F4ECD8', 'text' => '#8A6D3B', 'border' => '#D4C39A', 'icon' => 'bi-clock-history'],
        'failed' => ['label' => 'OCR Failed', 'bg' => '#F2DCDD', 'text' => '#8E1C2A', 'border' => '#8E1C2A', 'icon' => 'bi-exclamation-triangle'],
        'skipped' => ['label' => 'No Scan', 'bg' => '#E2E8DA', 'text' => '#5D6659', 'border' => '#CBD3C3', 'icon' => 'bi-dash-circle'],
        default => ['label' => ucfirst((string) $status), 'bg' => '#E2E8DA', 'text' => '#5D6659', 'border' => '#CBD3C3', 'icon' => 'bi-info-circle'],
    };
@endphp

<span class="lmo-chip" style="background-color: {{ $config['bg'] }}; color: {{ $config['text'] }}; border-color: {{ $config['border'] }};">
    <i class="bi {{ $config['icon'] }}"></i>{{ $config['label'] }}
</span>
