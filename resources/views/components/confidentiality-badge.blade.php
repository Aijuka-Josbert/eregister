@props(['level' => 'internal'])

@php
    $config = match($level) {
        'public' => ['label' => 'Public', 'bg' => '#E6F4EA', 'text' => '#21865B', 'icon' => 'bi-globe2'],
        'internal' => ['label' => 'Internal', 'bg' => '#EAF2FA', 'text' => '#245B91', 'icon' => 'bi-building'],
        'confidential' => ['label' => 'Confidential', 'bg' => '#FEF3C7', 'text' => '#B45309', 'icon' => 'bi-lock-fill'],
        'strictly_confidential' => ['label' => 'Strictly Confidential', 'bg' => '#FEE2E2', 'text' => '#C74646', 'icon' => 'bi-shield-lock-fill'],
        default => ['label' => ucfirst($level), 'bg' => '#EAF2FA', 'text' => '#245B91', 'icon' => 'bi-file-lock'],
    };
@endphp

<span class="badge rounded-pill fw-semibold px-2 py-1" style="background-color: {{ $config['bg'] }}; color: {{ $config['text'] }}; font-size: 0.74rem;">
    <i class="bi {{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
</span>
