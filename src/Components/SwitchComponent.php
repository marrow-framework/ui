<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 * {{ component('switch', {name: 'notifications', checked: true, label: 'Email notifications'}) }}
 *
 * Pure CSS (Tailwind `peer`/`peer-checked`) — no Alpine needed, unlike
 * Modal/Dropdown/dismissible Alert.
 */
class SwitchComponent extends Component
{
    public string $value = '1';
    public bool $checked = false;
    public bool $disabled = false;
    public string $label = '';
    public string $class = '';

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
        return '@ui/components/switch';
    }

    public function data(): array
    {
        return array_merge(parent::data(), [
            'name' => $this->fieldName,
            'id' => $this->id ?? $this->fieldName,
        ]);
    }
}
