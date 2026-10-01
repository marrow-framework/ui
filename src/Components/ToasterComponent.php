<?php

declare(strict_types=1);

namespace Marrow\Ui\Components;

use Marrow\Template\Component;

/**
 * Render ONCE per page, typically in your layout right before </body>:
 *
 *   {{ component('toaster') }}
 *
 * Two sources feed it automatically — no extra wiring beyond that one line:
 *   - Session flash: `status` → success toast, `error` → error toast (the
 *     same `->with('status', ...)`/`->withErrors([...])` convention this
 *     package's own views and marrow/warden's controllers already use).
 *   - Marrow\Notifications\NotificationManager's `database` channel, for
 *     the current authenticated user — see Notifications\ToastNotificationSource.
 *
 * Trigger a toast from your own JS at any time, e.g. from a fetch() handler:
 *   window.toast.success('Saved!')
 *   window.toast.error('Something went wrong.')
 *   window.toast.info('Heads up.', 6000)   // optional duration in ms, default 4000
 */
class ToasterComponent extends Component
{
    public function render(): string
    {
        return '@ui/components/toaster';
    }
}
