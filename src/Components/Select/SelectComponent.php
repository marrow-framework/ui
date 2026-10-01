<?php

declare(strict_types=1);

namespace Marrow\Ui\Components\Select;

use Marrow\Template\Component;

/**
 * {{ component('select', {name: 'role', options: {admin: 'Administrator', editor: 'Editor'}, value: 'editor'}) }}
 *
 * A styled native <select> — not a custom listbox/combobox (no searchable
 * dropdown, no multi-select UI). Fully accessible and keyboard-usable for
 * free; a Radix-style custom listbox is a bigger, separate undertaking.
 *
 * `slot` is an escape hatch for raw <option>/<optgroup> markup instead of
 * (or in addition to) `options` — pre-rendered HTML, not auto-escaped, same
 * caveat as ButtonComponent.
 */
class SelectComponent extends Component
{
    public array $options = [];
    public string $value = '';
    public string $placeholder = '';
    public bool $disabled = false;
    public bool $error = false;
    public string $class = '';
    public string $slot = '';

    /** See InputComponent's docblock — can't be a plain public `$name`/`$id`. */
    private string $fieldName = '';
    private ?string $id = null;

    public function __construct(array $props = [])
    {
        $this->fieldName = (string) ($props['name'] ?? '');
        $this->id = isset($props['id']) ? (string) $props['id'] : null;
        // See InputComponent's docblock/constructor for why these must not
        // reach Component::__construct()'s naive `$this->$key = $value`.
        unset($props['name'], $props['id']);
        parent::__construct($props);
    }

    public function render(): string
    {
        return '@ui/components/select/select';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'name' => $this->fieldName,
            'id' => $this->id ?? $this->fieldName,
        ]);
    }
}
