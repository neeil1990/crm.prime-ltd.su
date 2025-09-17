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
        $length = count($this->notifications);

        if ($length < 1) {
            return [];
        }

        $current_group_leader = $this->notifications[0];

        $this->initialize_task_count($current_group_leader);

        for ($i = 1; $i < $length; $i++) {

            $notification = $this->notifications[$i];

            $this->initialize_task_count($notification);

            if ($this->is_unread($notification) && $this->has_task_id($current_group_leader->task_id, $notification)) {
                $current_group_leader->task_count_in_group += 1;
                $this->delete_notification($i);
            } else {
                $current_group_leader = $notification;
            }
        }

        return $this->notifications;
    }

    private function initialize_task_count(object $notification): void
    {
        $notification->task_count_in_group = 0;
    }

    private function is_unread($notification): bool
    {
        return empty($notification->is_read);
    }

    private function has_task_id(int $task_id, object $notification): bool
    {
        return $task_id === intval($notification->task_id);
    }

    private function delete_notification(int $index): void
    {
        unset($this->notifications[$index]);
    }
}
