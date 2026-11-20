@props(['level' => 'internal'])

@php
    $config = match($level) {
        'public' => ['label' => 'Public', 'bg' => '#DAE5D4', 'text' => '#2B4430', 'border' => '#7F9B7D', 'icon' => 'bi-globe2'],
        'internal' => ['label' => 'Internal', 'bg' => '#E2E8DA', 'text' => '#5D6659', 'border' => '#CBD3C3', 'icon' => 'bi-building'],
        'confidential' => ['label' => 'Confidential', 'bg' => '#F4ECD8', 'text' => '#8A6D3B', 'border' => '#D4C39A', 'icon' => 'bi-lock-fill'],
        'strictly_confidential' => ['label' => 'Strictly Confidential', 'bg' => '#F2DCDD', 'text' => '#8E1C2A', 'border' => '#8E1C2A', 'icon' => 'bi-shield-lock-fill'],
        default => ['label' => ucfirst(str_replace('_', ' ', (string) $level)), 'bg' => '#E2E8DA', 'text' => '#5D6659', 'border' => '#CBD3C3', 'icon' => 'bi-file-lock'],
    };
@endphp

<span class="lmo-chip" style="background-color: {{ $config['bg'] }}; color: {{ $config['text'] }}; border-color: {{ $config['border'] }};">
    <i class="bi {{ $config['icon'] }}"></i>{{ $config['label'] }}
</span>
