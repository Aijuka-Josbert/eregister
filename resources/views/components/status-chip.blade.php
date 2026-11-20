@props(['status' => 'registered', 'archived' => false])

@php
    $effective = $archived ? 'archived' : $status;
    $config = match($effective) {
        'registered' => ['label' => 'Received', 'class' => 'st-received'],
        'in_review' => ['label' => 'With officer', 'class' => 'st-officer'],
        'actioned' => ['label' => 'Action taken', 'class' => 'st-action'],
        'archived' => ['label' => 'Filed', 'class' => 'st-filed'],
        'draft' => ['label' => 'Draft', 'class' => 'st-warn'],
        default => ['label' => ucfirst(str_replace('_', ' ', (string) $effective)), 'class' => 'st-filed'],
    };
@endphp

<span class="lmo-chip {{ $config['class'] }}">{{ $config['label'] }}</span>
