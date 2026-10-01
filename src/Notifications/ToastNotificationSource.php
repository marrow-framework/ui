<?php

declare(strict_types=1);

namespace Marrow\Ui\Notifications;

use Marrow\Auth\AuthManager;
use Marrow\Database\Connection;

/**
 * Bridges Marrow\Notifications\NotificationManager's `database` channel to
 * the `toaster` component: surfaces the current user's unread database
 * notifications as one-time toasts, then marks them read.
 *
 * Opt in per notification by including a `message` key in toDatabase():
 *
 *   public function toDatabase(object $notifiable): array
 *   {
 *       return ['message' => 'Your invoice was paid.', 'type' => 'success', 'invoice_id' => $this->invoice->id];
 *   }
 *
 * A notification with no `message` key is left alone (still delivered to
 * the database channel as normal, just never toasted) — this bridge has no
 * way to guess how a purely structured payload (e.g. {invoice_id: 4}) should
 * read as a sentence, so it doesn't try.
 *
 * Not filtered by `notifiable_type`: AuthManager::user() (both guards)
 * returns `(object) $row` — a plain stdClass, never the real model class a
 * controller likely passed to NotificationManager::send() when the
 * notification was created — so comparing get_class($authUser) against the
 * stored `notifiable_type` would reliably filter out every row. Pass
 * $notifiableType explicitly (e.g. Modules\Account\Models\User::class) if
 * your app has more than one notifiable type sharing the same id space.
 */
class ToastNotificationSource
{
    public function __construct(
        private readonly AuthManager $auth,
        private readonly Connection $db,
        private readonly string $table = 'notifications',
        private readonly ?string $notifiableType = null,
    ) {
    }

    /** @return array<int, array{id: int, type: string, message: string}> */
    public function pull(int $limit = 5): array
    {
        try {
            return $this->fetch($limit);
        } catch (\Throwable) {
            // A missing `notifications` table (marrow/ui used without the
            // notifications feature) or any other misconfiguration must
            // never break page rendering over an optional toast bridge.
            return [];
        }
    }

    /** @return array<int, array{id: int, type: string, message: string}> */
    private function fetch(int $limit): array
    {
        $user = $this->auth->user();
        $userId = $user?->id ?? null;

        if ($userId === null) {
            return [];
        }

        $sql = "SELECT * FROM {$this->table} WHERE notifiable_id = ? AND read_at IS NULL";
        $bindings = [$userId];

        if ($this->notifiableType !== null) {
            $sql .= ' AND notifiable_type = ?';
            $bindings[] = $this->notifiableType;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ?';
        $bindings[] = $limit;

        $rows = $this->db->select($sql, $bindings);

        $toasts = [];
        $readIds = [];

        foreach ($rows as $row) {
            $data = json_decode((string) $row['data'], true);
            if (!is_array($data) || !isset($data['message'])) {
                // No 'message' key — never toasted, so never marked read
                // either: it's still untouched for a notification-center-
                // style UI the app might build on the same table to show it
                // properly, rather than this bridge silently consuming it.
                continue;
            }

            $toasts[] = [
                'id' => (int) $row['id'],
                'type' => (string) ($data['type'] ?? 'info'),
                'message' => (string) $data['message'],
            ];
            $readIds[] = $row['id'];
        }

        if ($readIds !== []) {
            $this->markRead($readIds);
        }

        return $toasts;
    }

    /** @param array<int, mixed> $ids */
    private function markRead(array $ids): void
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->statement(
            "UPDATE {$this->table} SET read_at = ? WHERE id IN ({$placeholders})",
            [time(), ...$ids]
        );
    }
}
