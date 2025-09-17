<?php

namespace App\Libraries;

class NotificationGrouper
{
    private array $notifications = [];

    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    public function get_grouped_unread_by_task(): array
    {
        $current_group_leader = null;

        foreach ($this->notifications as $key => $notification) {

            $this->initialize_task_count($notification);

            if ($this->is_unread_task($notification) && $current_group_leader === null) {
                $current_group_leader = $notification;
            } elseif ($this->is_unread_task($notification) && $current_group_leader->task_id === $notification->task_id) {
                $current_group_leader->task_count_in_group += 1;
                $this->delete_notification($key);
            } else {
                $current_group_leader = null;
            }
        }

        return $this->notifications;
    }

    private function initialize_task_count(object $notification): void
    {
        $notification->task_count_in_group = 0;
    }

    private function is_unread_task($notification): bool
    {
        return !empty($notification->task_id) && $notification->is_read === "0";
    }

    private function delete_notification(int $index): void
    {
        unset($this->notifications[$index]);
    }
}
