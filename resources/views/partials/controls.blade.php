<div class="controls">
    <input type="text" class="search-box" placeholder="{{ $placeholder ?? 'Filter list...' }}"
        id="{{ $inputId ?? 'searchInput' }}">

    @if (!empty($showAdd) && $showAdd)
        <button class="add-btn" onclick="{{ $onClick ?? 'addFeature()' }}">
            {{ $buttonText ?? '+ Add Feature' }}
        </button>
    @endif
</div>
