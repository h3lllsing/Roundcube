@props(['id' => '', 'icon' => '', 'iconBg' => '#dbeafe', 'title' => '', 'description' => ''])

<div
    class="mo h"
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    x-show="openModal === '{{ $id }}'"
    x-cloak
    @keydown.escape.window="closeModal('{{ $id }}')"
    @click.self="closeModal('{{ $id }}')"
>
    <div class="mo-in">
        <div class="mo-hd">
            <div class="iw" style="background:{{ $iconBg }}">{{ $icon }}</div>
            <div>
                <h2 id="{{ $id }}-title">{{ $title }}</h2>
                @if($description)
                    <p>{{ $description }}</p>
                @endif
            </div>
        </div>
        <div class="mo-bd">
            {{ $slot }}
        </div>
        @if(isset($footer))
            <div class="mo-ft">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
