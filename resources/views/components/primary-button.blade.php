<button {{ $attributes->merge(['type' => 'submit', 'class' => 'gradient-btn']) }}>
    {{ $slot }}
</button>
