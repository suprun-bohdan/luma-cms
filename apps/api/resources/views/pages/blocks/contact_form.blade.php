<section class="contact-form">
    <h2>{{ $title }}</h2>

    @if(session('form_success'))
        <p class="contact-form__success">{{ session('form_success') }}</p>
    @endif

    @if($errors->any())
        <ul class="contact-form__errors">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ url('/public/forms/'.$form->slug.'/submit') }}">
        @csrf
        <input type="text" name="_hp" value="" tabindex="-1" autocomplete="off" class="contact-form__hp" aria-hidden="true">

        @foreach($form->fields as $field)
            <div class="contact-form__field">
                <label for="field-{{ $field->name }}">
                    {{ $field->label }}
                    @if($field->required)
                        <span aria-hidden="true">*</span>
                    @endif
                </label>

                @if($field->type->value === 'textarea')
                    <textarea
                        id="field-{{ $field->name }}"
                        name="{{ $field->name }}"
                        rows="5"
                        @if($field->required) required @endif
                    >{{ old($field->name) }}</textarea>
                @else
                    <input
                        id="field-{{ $field->name }}"
                        type="{{ $field->type->value === 'email' ? 'email' : 'text' }}"
                        name="{{ $field->name }}"
                        value="{{ old($field->name) }}"
                        @if($field->required) required @endif
                    >
                @endif
            </div>
        @endforeach

        <button type="submit">{{ $submitLabel }}</button>
    </form>
</section>
