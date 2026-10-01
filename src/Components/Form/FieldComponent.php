<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Form;

use Marrow\Template\Component;

/**
 * Composite `label` + `input`/`textarea` + inline validation error + help
 * text — the repeated unit every hand-rolled form ends up building anyway.
 * Built entirely out of this package's own `label`/`input`/`textarea`
 * components (via `component()` calls inside its own template, not PHP
 * composition), so overriding any of those three also reskins every
 * `field`.
 *
 *   {{ component('field', {name: 'email', type: 'email', label: 'Email', required: true}) }}
 *   {{ component('field', {name: 'bio', label: 'Bio', multiline: true, rows: 4, help: 'Markdown supported.'}) }}
 *   {{ component('field', {name: 'code', label: 'Code', attrs: {inputmode: 'numeric', autocomplete: 'one-time-code'}}) }}
 *   {{ component('field', {name: 'role', label: 'Role', options: {admin: 'Administrator', editor: 'Editor'}}) }}
 *
 * Wires the framework's own `old()`/`has_error()`/`errors()` Twig functions
 * automatically — no need to repeat `{% if has_error(...) %}` around every
 * field by hand. `old()` is **never** applied to a `password`-type field
 * (a password should never be echoed back into a form, old input or not).
 *
 * `errorMessages` overrides the `has_error()`/`errors()` session-based
 * lookup when non-empty — for a caller that already has its own error
 * messages in hand (e.g. `marrow/form-builder`'s `Field::$errors`, built
 * directly on the Field object rather than round-tripped through session
 * flash; see FormComponent) rather than the framework's flash-based
 * validation errors.
 *
 * `attrs` — see InputComponent's docblock; forwarded as-is to the
 * underlying `input`/`textarea`/`select`.
 */
class FieldComponent extends Component
{
    public string $type = 'text';
    public ?string $label = null;
    public bool $required = false;
    public string $value = '';
    public string $placeholder = '';
    public ?string $help = null;
    public bool $multiline = false;
    public int $rows = 3;
    public bool $disabled = false;
    public bool $autofocus = false;
    public string $class = '';
    public array $attrs = [];
    /** @var array<string, string> value => label, same shape as SelectComponent's own `options` */
    public array $options = [];
    /** @var string[] */
    public array $errorMessages = [];

    /** See InputComponent's docblock — can't be a plain public `$name`/`$id`. */
    private string $fieldName = '';
    private ?string $id = null;

    public function __construct(array $props = [])
    {
        $this->fieldName = (string) ($props['name'] ?? '');
        $this->id = isset($props['id']) ? (string) $props['id'] : null;
        unset($props['name'], $props['id']);
        parent::__construct($props);
    }

    public function render(): string
    {
        return '@ui/components/form/field';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'name' => $this->fieldName,
            'id' => $this->id ?? $this->fieldName,
        ]);
    }
}
