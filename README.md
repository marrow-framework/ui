# Marrow UI

A ready-to-use Tailwind + [Alpine.js](https://alpinejs.dev) component library for
[Marrow](https://github.com/marrow-framework/core) — 25 components (Button, Alert, Card, Modal, Tabs, a
Sonner-style toaster, and more) built entirely on the framework's own `Marrow\Template\Component` /
`ComponentRegistry` primitives, which core already shipped but nothing had ever actually used.

## Install

```bash
composer require marrow/ui
php forge ui:install   # wires Alpine.js into resources/js/app.js + resources/css/app.css
npm install alpinejs
npm run dev
```

That's it — components register themselves the moment the package is required (`UiModule::boot()`), no
publishing, no config. Unlike `marrow/warden`, there is nothing to "own": this is a real shared dependency,
upgraded with `composer update` like any other.

## Usage

```twig
{{ component('button', {variant: 'primary', slot: 'Save'}) }}
{{ component('alert', {type: 'success', title: 'Saved', slot: 'Your changes were saved.'}) }}
{{ component('badge', {variant: 'success', slot: 'Active'}) }}
```

Every component is a PHP class (props + a little logic) paired with a Twig template, resolved by name through
the framework's `component()` function. That function call is the one form that always works; three more
exist for ergonomics, each suited to a different shape of content.

### 1. Function call — `{{ component(...) }}`

The base form, usable everywhere, for any component:

```twig
{{ component('button', {href: '/posts', variant: 'ghost', slot: 'Cancel'}) }}
{{ component('alert', {type: 'danger', dismissible: true, slot: errors('email')[0]}) }}
```

Multi-line content is built with Twig's own `{% set %}...{% endset %}` capture block first:

```twig
{% set body %}<p>Manage your account settings below.</p>{% endset %}
{{ component('card', {title: 'Account', slot: body}) }}
```

### 2. One slot — `{% component %}...{% endcomponent %}`

Sugar over the function call for a component with one content prop (`Card`, `Alert`, `Badge`, `Tooltip`'s
`slot`, ...): the tag's body is captured and passed as `slot` automatically, so there's no `{% set %}` to
write by hand.

```twig
{% component 'card' with {title: 'Account'} %}
    <p>Manage your account settings below.</p>
{% endcomponent %}

{% component 'badge' with {variant: 'success'} %}Active{% endcomponent %}
```

`with {...}` is optional, and the tag nests freely — a `{% component %}` body can contain another
`{% component %}`.

### 3. Two slots (`trigger` + a body) — Twig's native `{% embed %}`

`Modal` and `Dropdown` take two pieces of pre-rendered content, not one — `{% component %}` above only
captures a single default slot, so it doesn't fit them. Rather than a second, more elaborate tag with nested
named blocks, both components' templates declare real Twig blocks (`{% block trigger %}` / `{% block body %}`),
which Twig's own `{% embed %}` tag can already override — no custom parser needed:

```twig
{% embed '@ui/components/modal.html.twig' with {title: 'Confirm deletion', maxWidth: 'lg'} %}
    {% block trigger %}
        {{ component('button', {variant: 'danger', slot: 'Delete account'}) }}
    {% endblock %}
    {% block body %}
        <p>Are you sure you want to delete your account?</p>
    {% endblock %}
{% endembed %}
```

Note the full filename (`modal.html.twig`, not just `modal`) — unlike `component()`/`view()`, Twig's own tags
don't go through this package's `.html.twig` auto-suffixing. Any prop that affects markup (`maxWidth`,
`align`, ...) is resolved to its Tailwind class **inside the template itself** (a plain Twig dict lookup), not
computed in the PHP `Component` class — `{% embed %}` never instantiates that class, so a value computed only
in PHP would silently be missing.

### 4. HTML-like — `<mui-button>...</mui-button>`

For templates that read more naturally as markup than as Twig tags — rewritten to the `{% component %}` form
above **before Twig's own lexer ever sees the template** (there is no native HTML-tag syntax in Twig; this is
a small, self-contained source-to-source rewrite — see `Twig\HtmlComponentTag`):

```twig
<mui-button variant="primary">Save</mui-button>
<mui-separator />
<mui-avatar initials="JD" size="lg" />

<mui-card title="Account">
    <mui-badge variant="success">Active</mui-badge>
</mui-card>
```

Attributes follow an HTML-ish, slightly Vue/Alpine-flavored convention:

```twig
{# whole value = one expression, not stringified: #}
<mui-button variant="{{ isDanger ? 'danger' : 'primary' }}">Save</mui-button>

{# mixed text/expression -> Twig string interpolation: #}
<mui-card title="Hello {{ name }}!">...</mui-card>

{# :attr -- raw expression, no quotes, no {{ }} needed (Vue/Alpine-style bind): #}
<mui-tabs :tabs="myTabsArray">...</mui-tabs>

{# boolean attribute -> disabled: true : #}
<mui-button disabled>Save</mui-button>

{# kebab-case -> camelCase: maxWidth #}
<mui-modal max-width="lg">...</mui-modal>
```

`<mui-x>` nests freely, including another `<mui-x>` of the same name. It does **not** give you a second way
to fill `Modal`/`Dropdown`'s two named slots — the tag it compiles down to only has one default slot, same as
`{% component %}` above; use `{% embed %}` for those instead.

Accepted limitations, not bugs: the rewrite has no awareness of Twig's own `{# comments #}` or
`{% verbatim %}` blocks, so literal `<mui-...>` text inside either is rewritten anyway — avoid the `<mui-`
prefix in a template for anything other than an actual component. Malformed markup (an unterminated tag, a
stray `/>` with no opening match) is left as literal text rather than guessed at, so a typo shows up as stray
text on the page rather than corrupting the rest of the template.

---

`slot`/`trigger`/`footer` props — and a captured `{% component %}`/`<mui-x>` body — are rendered with `|raw`
in every template here; they're pre-built HTML, not auto-escaped. Compose them from trusted template
fragments (as shown above); never feed raw user input into one directly. `table`'s `rows` is the one
exception — see the components table below.

## Components

Standalone components keep one PHP class per component, flat under `Components/` (e.g.
`Components/ButtonComponent.php`). A component with sub-pieces (Card, Breadcrumb, Select, Form) groups its
whole family under `Components/{Family}/` instead, mirrored by `Views/components/{family}/` for the matching
templates — e.g. `Components/Card/CardHeaderComponent.php` renders `Views/components/card/card-header.html.twig`.
This only affects where the PHP/Twig *files* live; the registered component *name* you actually call
(`card-header`) is the same either way.

Every component below accepts two props beyond the ones listed in its own row (omitted from the tables for
brevity, since they're universal): `class`, a string **merged additively** with the component's own base
Tailwind classes (never replacing them), and `attrs`, an array of arbitrary HTML attribute name/value pairs
rendered onto the component's root element — the mechanism for `style`, `data-*`, `aria-*`, or anything else
with no dedicated prop (see `InputComponent`'s docblock for the exact `attrs` value rules: `true` → bare
boolean attribute, `false`/`null` → omitted). This holds unless a component's own docs say otherwise (`form`,
for instance — see its own section below).

| Component    | Props                                                                                                                 | Notes                                                                                                                                    |
| ------------ | ---------------------------------------------------------------------------------------------------------------------| ------------------------------------------------------------------------------------------------------------------------------------------|
| `button`     | `variant` (primary/secondary/danger/ghost/outline), `size` (sm/md/lg), `type`, `href`, `disabled`, `loading`, `slot`  | Renders `<a>` instead of `<button>` when `href` is set.                                                                                  |
| `alert`      | `type` (info/success/warning/danger), `title`, `dismissible`, `slot`                                                 | `dismissible` needs Alpine (`x-show`/`x-cloak`).                                                                                         |
| `badge`      | `variant` (default/success/warning/danger/info), `slot`                                                              | Plain, no JS.                                                                                                                             |
| `modal`      | `title`, `trigger`, `slot`, `maxWidth` (sm/md/lg/xl)                                                                  | Self-contained Alpine `open` state per instance — safe to use more than once per page.                                                   |
| `dropdown`   | `trigger`, `slot`, `align` (left/right)                                                                               | Closes on outside click (`@click.outside`).                                                                                              |
| `input`      | `name`, `type`, `value`, `placeholder`, `disabled`, `autofocus`, `error`, `attrs`                                     | Plain styled `<input>`, no JS. `attrs` — arbitrary HTML attribute passthrough (`autocomplete`, `inputmode`, ...), see its own docblock.   |
| `textarea`   | `name`, `value`, `placeholder`, `rows`, `disabled`, `autofocus`, `error`, `attrs`                                     | Plain, no JS.                                                                                                                             |
| `label`      | `for`, `required`, `slot`                                                                                             | Plain, no JS.                                                                                                                             |
| `checkbox`   | `name`, `value`, `checked`, `disabled`, `label`                                                                       | Native checkbox, browser-styled accent color.                                                                                            |
| `radio`      | `name`, `value`, `checked`, `disabled`, `label`                                                                       | Native radio.                                                                                                                             |
| `switch`     | `name`, `value`, `checked`, `disabled`, `label`                                                                       | Pure CSS (`peer`/`peer-checked`) toggle — **no Alpine needed**.                                                                           |
| `separator`  | `orientation` (horizontal/vertical)                                                                                   | Plain divider, no JS.                                                                                                                     |
| `avatar`     | `src`, `alt`, `initials`, `size` (sm/md/lg)                                                                           | Falls back to `initials` when `src` is empty.                                                                                             |
| `spinner`    | `size` (sm/md/lg)                                                                                                     | The same spinner `button[loading]` uses internally, usable standalone.                                                                   |
| `progress`   | `value`, `max`                                                                                                        | Plain, no JS.                                                                                                                             |
| `skeleton`   | `class`                                                                                                                | Sizing is entirely via `class` (e.g. `h-4 w-32`).                                                                                         |
| `tooltip`    | `trigger`, `slot`, `side` (top/bottom/left/right)                                                                     | Pure CSS (`group-hover`) — fixed side, no Floating-UI-style auto-flip near viewport edges.                                                |
| `tabs`       | `tabs` (array of `{id, label, content}`)                                                                              | `content` is pre-rendered HTML per tab, passed as one structured array (no compound `<Tabs.Trigger>`/`<Tabs.Content>` API).              |
| `accordion`  | `items` (array of `{title, content}`)                                                                                 | Single-open (classic accordion), not multi-open.                                                                                          |
| `pagination` | `currentPage`, `lastPage`, `urlPattern` (must contain `{page}`), `siblings`                                           | Windowed with ellipses; builds URLs from a pattern string, not a Router — no Router dependency in a presentational class.                |
| `table`      | `headers` (strings), `rows` (array of string arrays)                                                                  | Cell values **are auto-escaped** (the one component here with no `raw` filter) — plain tabular data is the common case.                  |
| `toaster`    | _(none — render it once)_                                                                                             | Sonner-style toast stack. See [Toasts](#toasts-sonner-style) below.                                                                       |

### Card family (`Components/Card/`)

| Component          | Props            | Notes                                                                 |
| ------------------ | ---------------- | ---------------------------------------------------------------------|
| `card`              | `title`, `footer`, `slot`, `composed` | Plain shortcut (`title`/`footer` strings) by default; pass `composed: true` to assemble the pieces below instead — see CardComponent's docblock. |
| `card-header`       | `class`, `slot`  | Compositional sub-piece.                                              |
| `card-title`        | `class`, `slot`  | Compositional sub-piece.                                              |
| `card-description`  | `class`, `slot`  | Compositional sub-piece.                                              |
| `card-content`      | `class`, `slot`  | Compositional sub-piece.                                              |
| `card-footer`       | `class`, `slot`  | Compositional sub-piece.                                              |

```twig
{% component 'card' with {composed: true} %}
    {% component 'card-header' %}
        {% component 'card-title' %}Account{% endcomponent %}
        {% component 'card-description' %}Manage your account settings.{% endcomponent %}
    {% endcomponent %}
    {% component 'card-content' %}...{% endcomponent %}
{% endcomponent %}
```

### Breadcrumb family (`Components/Breadcrumb/`)

| Component          | Props                   | Notes                                                              |
| ------------------- | ----------------------- | ------------------------------------------------------------------|
| `breadcrumb`         | `items` (array of `{label, href?}`), `slot` | `items` is the plain shortcut; omit it and nest `breadcrumb-item` instead for the compositional form. |
| `breadcrumb-item`    | `href`, `class`, `slot` | Renders a link when `href` is given, plain current-page text otherwise. |

```twig
{% component 'breadcrumb' %}
    {% component 'breadcrumb-item' with {href: '/'} %}Dashboard{% endcomponent %}
    {% component 'breadcrumb-item' %}Settings{% endcomponent %}
{% endcomponent %}
```

Separators between compositional items are pure CSS (a `:not(:first-child)` sibling selector on the `<ol>`) —
there's no shared loop context between independently-rendered items to ask "am I first?".

### Select family (`Components/Select/`)

| Component       | Props                                                          | Notes                                                                                   |
| ---------------- | --------------------------------------------------------------| -----------------------------------------------------------------------------------------|
| `select`          | `name`, `options` (dict), `value`, `placeholder`, `disabled`, `error`, `attrs`, `slot` | Styled **native** `<select>`, not a custom searchable listbox. `options` is the plain shortcut; `slot` also accepts raw `<option>`/`<optgroup>` markup, including nested `select-item` calls. |
| `select-item`     | `value`, `selected`, `disabled`, `slot`                        | One `<option>`. `selected` isn't auto-computed from `select`'s `value` (items render independently) — pass it explicitly, e.g. `selected: role == 'admin'`. |

```twig
{% component 'select' with {name: 'role'} %}
    {% component 'select-item' with {value: 'admin', selected: role == 'admin'} %}Administrator{% endcomponent %}
    {% component 'select-item' with {value: 'editor', selected: role == 'editor'} %}Editor{% endcomponent %}
{% endcomponent %}
```

**Deliberately not included** (would need real JS state/positioning beyond what Alpine's core directives give
you for free): a searchable Combobox/Command palette, a DataTable with client-side sort/filter, a date-picker
Calendar, a Carousel. Each is a meaningfully bigger undertaking than the components above and would pull in
either a positioning library (Floating UI) or a dedicated Alpine plugin.

## Forms (`Components/Form/`)

| Component   | Props                                                                                                              | Notes |
| ------------ | --------------------------------------------------------------------------------------------------------------- | ------|
| `field`       | `name`, `type`, `label`, `required`, `value`, `placeholder`, `help`, `multiline`, `rows`, `options`, `disabled`, `autofocus`, `errorMessages`, `attrs`, `class` | Composite `label` + `input`/`textarea`/`select` + inline error + help text. Wires `old()`/`has_error()`/`errors()` automatically; never prefills a `password`-type field. |
| `form`        | `form` (a `marrow/form-builder` `Form` instance, optional), `action`, `method`, `class`, `slot`                  | A styled `<form>` with CSRF + method-spoofing built in; see below for the form-builder bridge. `form: null` still gives you a plain styled form around whatever you put in `slot`. |

```twig
{{ component('field', {name: 'email', type: 'email', label: 'Email', required: true}) }}
{{ component('field', {name: 'role', label: 'Role', options: {admin: 'Administrator', editor: 'Editor'}}) }}
```

### The `marrow/form-builder` bridge

Hand `form` a real `marrow/form-builder` `Form` instance and every declared field renders automatically —
no manual loop over `$form->fields()`:

```php
$form = RegisterForm::fromRequest($request); // marrow/form-builder
```

```twig
{% set submit %}{{ component('button', {type: 'submit', slot: 'Create account'}) }}{% endset %}
{{ component('form', {form: form, action: '/register', slot: submit}) }}
```

Each form-builder `Field` subclass maps to the matching `ui` component: `CharField`/`EmailField`/
`IntegerField` → `field` (text/email/number), `TextareaField` → `field` with `multiline: true`,
`ChoiceField` → `field` with `options`, `PasswordField` → `field` type=password (**never** prefilled, same
rule form-builder's own `PasswordField::render()` already follows), `BooleanField` → `checkbox`,
`HiddenField` → a bare hidden `input`, no label/wrapper. Validation errors come from the `Field` object's own
`$errors` (populated by `$form->isValid()`), via `field`'s `errorMessages` override — not from the framework's
session-flash `has_error()`/`errors()`, which form-builder doesn't use.

This package has **no hard dependency** on `marrow/form-builder` (only a composer `suggest`) — `form` without
a `form` prop, and every other component here, works with or without it installed.

## Toasts (Sonner-style)

Render the toaster once, anywhere in your layout (typically right before `</body>`):

```twig
{{ component('toaster') }}
```

From then on, three things can produce a toast, with **no further setup**:

**1. Your own JS**, any time:

```js
window.toast.success('Saved!');
window.toast.error('Something went wrong.');
window.toast.info('Heads up.', 6000); // optional duration in ms, default 4000
```

**2. Session flash messages** — the exact convention this package's views and `marrow/warden`'s controllers
already use:

```php
return $this->redirectToRoute('home')->with('status', 'Profile updated.');     // -> success toast
return $this->back()->withErrors(['email' => ['Invalid link.']]);              // -> shown inline by error()/has_error(), not toasted
```

Only the `status` and `error` flash keys are bridged to a toast (`success`/`error` respectively) — everything
else (validation errors) stays as the inline `errors()`/`has_error()` rendering this package's form components
already support.

**3. `Marrow\Notifications\NotificationManager`'s `database` channel**, for the current authenticated user —
the link to the framework's own notification system:

```php
class InvoicePaid extends Notification
{
    public function via(object $notifiable): array { return ['database']; }

    public function toDatabase(object $notifiable): array
    {
        return ['message' => 'Your invoice was paid.', 'type' => 'success', 'invoice_id' => $this->invoice->id];
    }
}

$notifications->send($user, new InvoicePaid($invoice));
```

The `toaster` component queries the user's unread rows on every page load
(`Notifications\ToastNotificationSource`) and marks only the ones it actually toasted as read. **Opt in with
a `message` key** — a notification whose `toDatabase()` payload has no `message` (e.g. a purely structured
`{invoice_id: 4}`) is left alone entirely: not toasted, not marked read, since this bridge has no way to guess
how your app wants a structured payload worded. Build a proper notification-center UI on the same table if
you need one.

Not filtered by `notifiable_type` by default: `AuthManager::user()` returns a plain `stdClass` (both guards),
never the real model class a controller likely passed to `NotificationManager::send()` — so comparing class
names would reliably filter out every row. If more than one notifiable type shares the same id space in your
app, register your own `ToastNotificationSource` instance with an explicit `$notifiableType` instead of
relying on the default.

## Overriding a component

No fork needed — register your own class under the same name from your own module's `boot()` (whichever
module boots last wins):

```php
use Marrow\Template\ComponentRegistry;

$registry->registerAs('button', \Modules\Ui\MyButtonComponent::class);
```

## Requirements

- `marrow/framework` ^2.2
- Alpine.js — only `alert[dismissible]`, `modal`, `dropdown`, `tabs`, and `accordion` need it at runtime;
  `button`, `card`, `badge`, `switch`, `tooltip`, and the rest render fine without it.

## License

MIT — see [LICENSE](LICENSE).
