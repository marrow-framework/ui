<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/** {{ component('input', {name: 'email', type: 'email', placeholder: 'you@example.com', value: old('email')}) }} */
class InputComponent extends Component
{
    public string $type = 'text';
    public string $value = '';
    public string $placeholder = '';
    public bool $disabled = false;
    public bool $error = false;
    public string $class = '';

    /**
     * Not a plain public property: `Component::$name` is already declared,
     * statically, for component-name overrides — a subclass can't
     * redeclare the same property non-static (PHP fatal error), so the
     * incoming `name` prop (the HTML `name=""` attribute) is captured here
     * instead and exposed to the template as `name` via data().
     */
    private string $fieldName = '';
    private ?string $id = null;

    public function __construct(array $props = [])
    {
        $this->fieldName = (string) ($props['name'] ?? '');
        $this->id = isset($props['id']) ? (string) $props['id'] : null;
        // Component::__construct() assigns via `$this->$key = $value` with
        // no staticness check — leaving 'name' in here would attempt to
        // write Component's *static* $name property through an instance,
        // which PHP warns about (and wouldn't do what the caller intended
        // anyway, now that it's been captured into $fieldName above).
        unset($props['name'], $props['id']);
        parent::__construct($props);
    }

    public function render(): string
    {
        return '@ui/components/input';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'name' => $this->fieldName,
            'id' => $this->id ?? $this->fieldName,
        ]);
    }
}
