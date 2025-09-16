<?php

namespace App\Libraries;

class NotificationGrouper
{
    private array $notifications = [];

    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    public function group_unread_by_task(): void
    {
        $first_task_in_group = null;

        foreach ($this->notifications as $key => $notification) {

            $notification->task_count_in_group = 0;

            if ($this->is_unread_task($notification) && $first_task_in_group == null) {
                $first_task_in_group = $notification;
            } elseif ($this->is_unread_task($notification) && $first_task_in_group->task_id == $notification->task_id) {
                $first_task_in_group->task_count_in_group += 1;
                unset($this->notifications[$key]);
            } else {
                $first_task_in_group = null;
            }
        }
    }

    public function get(): array
    {
        return $this->notifications;
    }

    private function is_unread_task($notification)
    {
        return ($notification->task_id && $notification->is_read == "0");
    }
}
