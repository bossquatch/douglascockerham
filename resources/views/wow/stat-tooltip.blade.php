<button class="stat-tooltip-trigger" type="button" aria-describedby="stat-help-{{ strtolower($label) }}">{{ $label }}</button>
<span class="stat-tooltip" id="stat-help-{{ strtolower($label) }}" role="tooltip" popover="manual">
    <strong>{{ $label }}</strong>
    {{ $description }}
</span>
