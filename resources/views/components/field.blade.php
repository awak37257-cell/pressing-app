@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="form-label">{{ $label }}@if($required) *@endif</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           @required($required) {{ $attributes->except('class') }}
           class="form-input @error($name) border-red-400 @enderror">
    @error($name)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
