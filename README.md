# Marrow UI

A ready-to-use Tailwind + [Alpine.js](https://alpinejs.dev) component library for [Marrow](https://github.com/marrow-framework/core) —
Button, Alert, Card, Badge, Modal, Dropdown — built entirely on the framework's own
`Marrow\Template\Component` / `ComponentRegistry` primitives (already shipped by core, previously unused by
anything).

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
{{ component('button', {href: '/posts', variant: 'ghost', slot: 'Cancel'}) }}

{{ component('alert', {type: 'success', title: 'Saved', slot: 'Your changes were saved.'}) }}
{{ component('alert', {type: 'danger', dismissible: true, slot: errors('email')[0]}) }}

{{ component('badge', {variant: 'success', slot: 'Active'}) }}
```

### A single slot: `{% component %}...{% endcomponent %}`

For a component with one content prop (`Card`, `Alert`, `Badge`, `Tooltip`'s `slot`, ...), this tag is sugar
over the function call above — its body is captured and passed as `slot`, so there's no `{% set %}` to write
by hand:

```twig
{% component 'card' with {title: 'Account'} %}
    <p>Manage your account settings below.</p>
{% endcomponent %}

{% component 'alert' with {type: 'danger', dismissible: true} %}
    {{ errors('email')[0] }}
{% endcomponent %}

{% component 'badge' with {variant: 'success'} %}Active{% endcomponent %}
```

`with {...}` is optional, and the tag nests freely (a `{% component %}` body can itself contain another
`{% component %}`). It compiles down to essentially the same thing as the hand-written form:

```twig
{% set body %}<p>Manage your account settings below.</p>{% endset %}
{{ component('card', {title: 'Account', slot: body}) }}
```

### Two slots (`trigger` + a body): Twig's native `{% embed %}`

`Modal` and `Dropdown` take two pieces of pre-rendered content, not one — `{% component %}` above only
captures a single default slot, so it doesn't fit them. Rather than inventing a second, more elaborate tag
with nested named blocks, both components' templates declare real Twig blocks (`{% block trigger %}` /
`{% block body %}`), which Twig's own `{% embed %}` tag can already override — no custom parser needed:

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

Note the full filename in `{% embed %}` (`modal.html.twig`, not just `modal`) — unlike `component()`/`view()`,
Twig's own tags don't go through this package's `.html.twig` auto-suffixing. Any prop that affects markup
(`maxWidth`, `align`, ...) is resolved to its Tailwind class **inside the template itself** (a plain Twig dict
lookup), not computed in the PHP `Component` class — `{% embed %}` never instantiates that class, so a
value computed only in PHP would silently be missing. The plain function-call form (`{{ component('modal',
{trigger: ..., slot: ...}) }}`, with `{% set %}` for each) still works exactly as before; `{% embed %}` is an
alternative, not a replacement.

`slot`/`trigger`/`footer` props — and a `{% component %}` tag's captured body — are rendered with `|raw` in
every template here; they're pre-built HTML, not auto-escaped. Compose them from trusted template fragments
(as above); never feed raw user input into one directly.

## Components

| Component    | Props                                                                                                                | Notes                                                                                                                                   |
| ------------ | -------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `button`     | `variant` (primary/secondary/danger/ghost/outline), `size` (sm/md/lg), `type`, `href`, `disabled`, `loading`, `slot` | Renders `<a>` instead of `<button>` when `href` is set.                                                                                 |
| `alert`      | `type` (info/success/warning/danger), `title`, `dismissible`, `slot`                                                 | `dismissible` needs Alpine (`x-show`/`x-cloak`).                                                                                        |
| `card`       | `title`, `footer`, `slot`                                                                                            | Plain container, no JS.                                                                                                                 |
| `badge`      | `variant` (default/success/warning/danger/info), `slot`                                                              | Plain, no JS.                                                                                                                           |
| `modal`      | `title`, `trigger`, `slot`, `maxWidth` (sm/md/lg/xl)                                                                 | Self-contained Alpine `open` state per instance — safe to use more than once per page.                                                  |
| `dropdown`   | `trigger`, `slot`, `align` (left/right)                                                                              | Closes on outside click (`@click.outside`).                                                                                             |
| `input`      | `name`, `type`, `value`, `placeholder`, `disabled`, `error`                                                          | Plain styled `<input>`, no JS.                                                                                                          |
| `textarea`   | `name`, `value`, `placeholder`, `rows`, `disabled`, `error`                                                          | Plain, no JS.                                                                                                                           |
| `label`      | `for`, `required`, `slot`                                                                                            | Plain, no JS.                                                                                                                           |
| `checkbox`   | `name`, `value`, `checked`, `disabled`, `label`                                                                      | Native checkbox, browser-styled accent color.                                                                                           |
| `radio`      | `name`, `value`, `checked`, `disabled`, `label`                                                                      | Native radio.                                                                                                                           |
| `switch`     | `name`, `value`, `checked`, `disabled`, `label`                                                                      | Pure CSS (`peer`/`peer-checked`) toggle — **no Alpine needed**.                                                                         |
| `select`     | `name`, `options` (dict), `value`, `placeholder`, `disabled`, `error`, `slot`                                        | Styled **native** `<select>` — not a custom searchable listbox; `slot` is a raw escape hatch for `<optgroup>`/custom `<option>` markup. |
| `separator`  | `orientation` (horizontal/vertical)                                                                                  | Plain divider, no JS.                                                                                                                   |
| `avatar`     | `src`, `alt`, `initials`, `size` (sm/md/lg)                                                                          | Falls back to `initials` when `src` is empty.                                                                                           |
| `spinner`    | `size` (sm/md/lg)                                                                                                    | The same spinner `button[loading]` uses internally, usable standalone.                                                                  |
| `progress`   | `value`, `max`                                                                                                       | Plain, no JS.                                                                                                                           |
| `skeleton`   | `class`                                                                                                              | Sizing is entirely via `class` (e.g. `h-4 w-32`).                                                                                       |
| `tooltip`    | `trigger`, `slot`, `side` (top/bottom/left/right)                                                                    | Pure CSS (`group-hover`) — fixed side, no Floating-UI-style auto-flip near viewport edges.                                              |
| `tabs`       | `tabs` (array of `{id, label, content}`)                                                                             | `content` is pre-rendered HTML per tab, passed as a single structured array (no compound `<Tabs.Trigger>`/`<Tabs.Content>` API).        |
| `accordion`  | `items` (array of `{title, content}`)                                                                                | Single-open (classic accordion), not multi-open.                                                                                        |
| `breadcrumb` | `items` (array of `{label, href?}`)                                                                                  | The last item (or any item without `href`) renders as plain text.                                                                       |
| `pagination` | `currentPage`, `lastPage`, `urlPattern` (must contain `{page}`), `siblings`                                          | Windowed with ellipses; builds URLs from a pattern string, not a Router — no Router dependency in a presentational class.               |
| `table`      | `headers` (strings), `rows` (array of string arrays)                                                                 | Cell values **are auto-escaped** (the one component here with no `raw` filter) — plain tabular data is the common case.                 |
| `toaster`    | _(none — render it once)_                                                                                            | Sonner-style toast stack. See [Toasts](#toasts-sonner-style) below.                                                                     |

**Deliberately not included** (would need real JS state/positioning beyond what Alpine's core directives give you for free — Command palette/searchable Combobox, a DataTable with client-side sort/filter, a date-picker Calendar, Carousel): each is a meaningfully bigger undertaking than the components above and would pull in either a positioning library (Floating UI) or a dedicated Alpine plugin.

## Toasts (Sonner-style)

Render the toaster once, anywhere in your layout (typically right before `</body>`):

```twig
{{ component('toaster') }}
```

From then on, three things can produce a toast, with **no further setup**:

1. **Your own JS**, any time:

   ```js
   window.toast.success('Saved!');
   window.toast.error('Something went wrong.');
   window.toast.info('Heads up.', 6000); // optional duration in ms, default 4000
   ```

2. **Session flash messages** — the exact convention this package's views and `marrow/warden`'s controllers
   already use:

   ```php
   return $this->redirectToRoute('home')->with('status', 'Profile updated.');     // → success toast
   return $this->back()->withErrors(['email' => ['Invalid link.']]);              // → shown inline by error()/has_error(), not toasted
   ```

   Only the `status` and `error` flash keys are bridged to a toast (`success`/`error` respectively) — everything else (validation errors) stays as the inline `errors()`/`has_error()` rendering this package's form components already support.

3. **`Marrow\Notifications\NotificationManager`'s `database` channel**, for the current authenticated
   user — the link to the framework's own notification system:

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

   ```txt
   The `toaster` component queries the user's unread rows on every page load (`Notifications\ToastNotificationSource`) and marks only the ones it actually toasted as read. **Opt in with a `message` key** — a notification whose `toDatabase()` payload has no `message` (e.g. pure `{invoice_id: 4}`) is left alone entirely (not toasted, not marked read), since this bridge has no way to guess how your app wants a purely structured payload worded; build a proper notification-center UI for those on the same table if you need one.

   Not filtered by `notifiable_type` by default: `AuthManager::user()` returns a plain `stdClass` (both
   guards), never the real model class a controller likely passed to `NotificationManager::send()` — so
   comparing class names would reliably filter out every row. If more than one notifiable type shares the
   same id space in your app, register your own `ToastNotificationSource` instance with an explicit
   `$notifiableType` instead of relying on the default.
   ```

## Overriding a component

No fork needed — register your own class under the same name from your own module's `boot()` (whichever
module boots last wins):

```php
use Marrow\Template\ComponentRegistry;

$registry->registerAs('button', \Modules\Ui\MyButtonComponent::class);
```

## Requirements

- `marrow/framework` ^2.2
- Alpine.js (only `alert[dismissible]`, `modal`, and `dropdown` need it at runtime — `button`, `card`, and
  `badge` render fine without it).
