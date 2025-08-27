@if (!empty($removable) && $removable)
    <button class="remove-btn" title="Remove condition">×</button>
@endif

<select name="context" class="form-select w-auto">
    <option value="user" {{ isset($condition['context']) && $condition['context'] === 'user' ? 'selected' : '' }}>
        User</option>
</select>

<select name="operation" class="form-select w-auto operation-select">
    <option value="in" {{ isset($condition['operation']) && $condition['operation'] === 'in' ? 'selected' : '' }}>in
    </option>
    <option value="equals"
        {{ isset($condition['operation']) && $condition['operation'] === 'equals' ? 'selected' : '' }}>equals</option>
    <option value="contains"
        {{ isset($condition['operation']) && $condition['operation'] === 'contains' ? 'selected' : '' }}>contains
    </option>
</select>


<input type="text" name="key" placeholder="key" value="{{ $condition['key'] ?? '' }}"
    class="form-control value-key w-auto {{ ($condition['operation'] ?? '') === 'in' || is_null($condition) ? 'd-none' : '' }}" />
<input type="text" name="value" placeholder="value" value="{{ $condition['value'] ?? '' }}"
    class="form-control value-input w-auto {{ ($condition['operation'] ?? '') === 'in' || is_null($condition) ? 'd-none' : '' }}" />

<select name="value"
    class="form-select w-auto value-select {{ ($condition['operation'] ?? '') === 'in' || is_null($condition) ? '' : 'd-none' }}"
    required>
    <option value="" disabled selected hidden>Select a option</option>
    @foreach ($userOptions as $option)
        <option value="{{ $option }}"
            {{ isset($condition['value']) && $condition['value'] === $option ? 'selected' : '' }}>{{ $option }}
        </option>
    @endforeach
</select>
