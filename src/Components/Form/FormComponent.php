<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Form;

use Marrow\Template\Component;

/**
 * A styled `<form>` wrapper with an optional, fully-automatic bridge to
 * `marrow/form-builder`: hand it a `Form` instance and every declared field
 * renders through this package's own `field`/`checkbox`/`input` components
 * — no manual `{% for %}` over `$form->fields()` needed.
 *
 *   $form = RegisterForm::fromRequest($request); // marrow/form-builder
 *   // ...
 *   {% set submit %}{{ component('button', {type: 'submit', slot: 'Create account'}) }}{% endset %}
 *   {{ component('form', {form: form, method: 'POST', slot: submit}) }}
 *
 * `form` is untyped as `?object` on purpose, not `?Marrow\FormBuilder\Form`
 * — this package has no hard dependency on form-builder (see composer.json
 * `suggest`), and PHP must not need that class loadable just to parse this
 * one's property declaration. The actual field-type mapping below checks
 * `instanceof` against form-builder's concrete Field classes one by one;
 * `instanceof` against a class that isn't installed simply evaluates false
 * (verified: it does not autoload, and does not throw), so this component
 * degrades to "no fields recognized" rather than fataling when form-builder
 * is absent — the realistic case is moot anyway, since nobody can construct
 * a real `Form` instance to pass in without the package installed.
 *
 * `form: null` (the default) still renders a plain styled `<form>` — CSRF
 * field, method-spoofing for non-GET/POST — around whatever you put in
 * `slot` yourself; the form-builder bridge is additive, not required.
 *
 * Method spoofing (PUT/PATCH/DELETE) uses the framework's own
 * `method_field()` Twig function — the real `<form method="">` stays
 * GET/POST, a hidden `_method` field carries the real one, exactly like any
 * other Marrow form.
 *
 * Known gap: form-builder's `Field` base class has no `attrs`/`class`
 * carrying mechanism, so an auto-rendered field can't get a per-field
 * `autocomplete`/`inputmode`/custom class this way — those are lost for
 * fields rendered through the auto-loop. A field that needs one (an OTP
 * code input wanting `inputmode: numeric`, say) is better rendered by hand
 * via the plain `field` component instead, using `form.field('name').errors`
 * for `errorMessages` to keep validation wired to the same Form — see
 * `marrow/warden`'s `two-factor-challenge.html.twig` for exactly this.
 */
class FormComponent extends Component
{
    public ?object $form = null;
    public string $action = '';
    public string $method = 'POST';
    public string $class = '';
    public string $slot = '';

    public function render(): string
    {
        return '@ui/components/form/form';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'fields' => $this->fieldDescriptors(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function fieldDescriptors(): array
    {
        if ($this->form === null || !method_exists($this->form, 'fields')) {
            return [];
        }

        $descriptors = [];
        foreach ($this->form->fields() as $field) {
            $descriptors[] = $this->describeField($field);
        }

        return $descriptors;
    }

    /** @return array<string, mixed> */
    private function describeField(object $field): array
    {
        $errorMessages = property_exists($field, 'errors') ? (array) $field->errors : [];
        $name = property_exists($field, 'name') ? (string) $field->name : '';
        $label = property_exists($field, 'label') && $field->label !== '' ? $field->label : ucfirst($name);
        $help = property_exists($field, 'help') && $field->help !== '' ? $field->help : null;
        $required = method_exists($field, 'isRequired') && $field->isRequired();
        $value = property_exists($field, 'value') ? (string) ($field->value ?? '') : '';
        $placeholder = property_exists($field, 'placeholder') ? ($field->placeholder ?? '') : '';

        if ($field instanceof \Marrow\FormBuilder\Fields\HiddenField) {
            return ['kind' => 'hidden', 'name' => $name, 'value' => $value];
        }

        if ($field instanceof \Marrow\FormBuilder\Fields\BooleanField) {
            return [
                'kind' => 'checkbox',
                'name' => $name,
                'label' => $label,
                'help' => $help,
                'checked' => (bool) $field->value,
                'errorMessages' => $errorMessages,
            ];
        }

        $base = [
            'kind' => 'field',
            'name' => $name,
            'label' => $label,
            'required' => $required,
            'value' => $value,
            'placeholder' => (string) $placeholder,
            'help' => $help,
            'errorMessages' => $errorMessages,
        ];

        if ($field instanceof \Marrow\FormBuilder\Fields\ChoiceField) {
            return $base + ['options' => $field->choices];
        }

        if ($field instanceof \Marrow\FormBuilder\Fields\TextareaField) {
            return $base + ['multiline' => true, 'rows' => $field->rows];
        }

        if ($field instanceof \Marrow\FormBuilder\Fields\PasswordField) {
            return $base + ['type' => 'password', 'value' => ''];
        }

        if ($field instanceof \Marrow\FormBuilder\Fields\EmailField) {
            return $base + ['type' => 'email'];
        }

        if ($field instanceof \Marrow\FormBuilder\Fields\IntegerField) {
            return $base + ['type' => 'number'];
        }

        // CharField, and any other/unrecognized Field subclass: plain text.
        return $base + ['type' => 'text'];
    }
}
