@php $person = $missingPerson ?? null; @endphp
<div class="border-b border-gray-200 pb-4 mb-5 mt-6">
    <h2 class="text-sm font-semibold text-gray-700">Physical & Identifying Details</h2>
    <p class="text-xs text-gray-400 mt-1">Use observable details that can help the public and officers identify the person.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach([
        'ethnicity' => ['Ethnicity / Bangsa', 'e.g. Malay'],
        'nationality' => ['Nationality', 'e.g. Malaysian'],
        'hair_type' => ['Hair Type', 'e.g. Short, curly'],
        'hair_color' => ['Hair Color', 'e.g. Black'],
        'eye_color' => ['Eye Color', 'e.g. Dark brown'],
    ] as $field => [$label, $placeholder])
    <div class="form-group">
        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
        <input type="text" id="{{ $field }}" name="{{ $field }}" class="form-input @error($field) border-red-500 @enderror" placeholder="{{ $placeholder }}" value="{{ old($field, $person?->{$field}) }}">
        @error($field)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endforeach

    <div class="form-group">
        <label for="height_cm" class="form-label">Height (cm)</label>
        <input type="number" id="height_cm" name="height_cm" min="30" max="250" class="form-input @error('height_cm') border-red-500 @enderror" placeholder="170" value="{{ old('height_cm', $person?->height_cm) }}">
        @error('height_cm')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="form-group">
        <label for="weight_kg" class="form-label">Weight (kg)</label>
        <input type="number" id="weight_kg" name="weight_kg" min="1" max="500" step="0.1" class="form-input @error('weight_kg') border-red-500 @enderror" placeholder="65" value="{{ old('weight_kg', $person?->weight_kg) }}">
        @error('weight_kg')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="form-group">
    <label for="clothing_description" class="form-label">Last-worn Clothing</label>
    <textarea id="clothing_description" name="clothing_description" rows="3" class="form-textarea @error('clothing_description') border-red-500 @enderror" placeholder="Colors and type of shirt, trousers, footwear, headwear, bag, etc.">{{ old('clothing_description', $person?->clothing_description) }}</textarea>
    @error('clothing_description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div class="form-group">
    <label for="identifying_features" class="form-label">Distinguishing Features</label>
    <textarea id="identifying_features" name="identifying_features" rows="3" class="form-textarea @error('identifying_features') border-red-500 @enderror" placeholder="Scars, tattoos, birthmarks, glasses, mobility aids, etc.">{{ old('identifying_features', $person?->identifying_features) }}</textarea>
    @error('identifying_features')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
