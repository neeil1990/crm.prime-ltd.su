<?php

use App\Controllers\App_Controller;

/* --------------------------------------------------------------------------
   SETTINGS
---------------------------------------------------------------------------*/
if (!function_exists('get_telegram_notification_setting')) {
    function get_telegram_notification_setting($key = "") {
        $config = new Telegram_Notification\Config\Telegram_Notification(); 
        $setting_value = get_array_value($config->app_settings_array, $key);
        
        if ($setting_value !== NULL) { 
            return $setting_value; 
        } else {
            return ""; 
        } 
    } 
}

/* --------------------------------------------------------------------------
   TASK ROLES AND CHECK NOTIFICATIONS
---------------------------------------------------------------------------*/
function get_task_roles_and_participants($ci, $task_id, $user_id)
{
    $result = [
        'Ваша роль' => 'Вы не учавствуете',
        'Создатель задачи' => 'Не указан',
        'Исполнители' => 'Нет',
        'Участники' => 'Нет',
    ];

    if (!$task_id) return $result;

    $task = $ci->Tasks_model->get_one($task_id);
    if (!$task) return $result;

    $db = \Config\Database::connect();

    // ------------------------------
    // Создатель задачи
    // ------------------------------
    $creator_name = '';
    $log = $db->table('activity_logs')
        ->where('log_type', 'task')
        ->where('log_type_id', $task_id)
        ->orderBy('id', 'ASC')
        ->get()
        ->getRow();

    if ($log) {
        $user = $ci->Users_model->get_one($log->created_by);
        if ($user && !$user->deleted) {
            $creator_name = make_profile_link($ci, $user);
        }
    }
    $result['Создатель задачи'] = $creator_name ?: 'Не указан';

    // ------------------------------
    // Исполнители
    // ------------------------------
    $executors_names = [];
    $executor_ids = [];
    if (!empty($task->executors)) {
        $executors = array_map('trim', explode(',', $task->executors));
        foreach ($executors as $uid) {
            $user = $ci->Users_model->get_one($uid);
            if ($user && !$user->deleted) {
                $executors_names[] = make_profile_link($ci, $user);
                $executor_ids[] = $uid;
            }
        }
    }
    $result['Исполнители'] = $executors_names ? implode(', ', $executors_names) : 'Нет';

    // ------------------------------
    // Участники
    // ------------------------------
    $participants_names = [];
    $participant_ids = [];
    if (!empty($task->collaborators)) {
        $collaborators = array_map('trim', explode(',', $task->collaborators));
        foreach ($collaborators as $uid) {
            $user = $ci->Users_model->get_one($uid);
            if ($user && !$user->deleted) {
                $participants_names[] = make_profile_link($ci, $user);
                $participant_ids[] = $uid;
            }
        }
    }
    $result['Участники'] = $participants_names ? implode(', ', $participants_names) : 'Нет';

    // ------------------------------
    // Ваша роль
    // ------------------------------
    $your_roles = [];
    $role_map = []; // для удобства проверки в настройках

    if ((int)$task->assigned_to === (int)$user_id) {
        $your_roles[] = 'Главный ответственный';
        $role_map['Главный ответственный'] = true;
    }
    if (in_array((string)$user_id, $executor_ids)) {
        $your_roles[] = 'Исполнитель';
        $role_map['Исполнитель'] = true;
    }
    if (in_array((string)$user_id, $participant_ids)) {
        $your_roles[] = 'Участник';
        $role_map['Участник'] = true;
    }

    if (empty($your_roles)) $your_roles[] = 'Вы не учавствуете';
    $result['Ваша роль'] = implode(', ', array_unique($your_roles));

    // ------------------------------
    // Проверка уведомлений по проекту
    // ------------------------------

    $send = [];
    foreach ($role_map as $role => $_) {
        $setting = $db->table('telegram_project_role_settings')
            ->where('user_id', $user_id)
            ->where('project_id', $task->project_id)
            ->where('role', $role)
            ->where('enabled', 1)
            ->get()
            ->getRow();

        if ($setting) {
            $send[] = true;
        }
    }

    if(in_array(true, $send)) {
        return $result;
    }
}

/* --------------------------------------------------------------------------
   CHANGES FORMATTER
---------------------------------------------------------------------------*/
function build_changes_text($serialized_changes, $statuses)
{
    if (empty($serialized_changes)) return '';

    $changes = @unserialize($serialized_changes);
    if (!is_array($changes)) return '';

    $field_names = [
        "start_date"  => "Дата начала",
        "deadline"    => "Дата окончания",
        "status"      => "Статус",
        "assigned_to" => "Ответственный",
        "status_id"   => "Изменение статуса задачи",
        'project_task_assigned' => "Изменён ответственный",
        'collaborators' => "Изменения в составе задачи"
    ];

    $text = "<b>Изменения:</b>";

    foreach ($changes as $field => $values) {

        $info = isset($field_names[$field]) ? $field_names[$field] : $field;
        $from = $values['from'] ? $values['from'] : '';
        $to   = $values['to'] ? $values['to'] : '';

        if ($field === 'status_id') {
            // $from = $statuses[(int)$from] ? $statuses[(int)$from] : $from;
            $to   = $statuses[(int)$to] ? $statuses[(int)$to] : $to;

            return '<b>Новый статус задачи: </b>' . $to;
        }

        if($field === 'collaborators') {
            $ci = new App_Controller();
            $fromNames = '';
            $toNames = '';

            $from_ids = array_filter(explode(',', $from));
            $to_ids   = array_filter(explode(',', $to));

            $removed_ids = array_diff($from_ids, $to_ids);
            $added_ids   = array_diff($to_ids, $from_ids);

            $text = '';

            if (!empty($removed_ids)) {
                $names = [];
                foreach ($removed_ids as $id) {
                    $user = $ci->Users_model->get_one($id);
                    $names[] = $user->first_name . ' ' . $user->last_name;
                }
                $text .= "Был исключён из задачи: " . implode(', ', $names);
            }

            if (!empty($added_ids)) {
                if ($text) $text .= "\n";
                $names = [];
                foreach ($added_ids as $id) {
                    $user = $ci->Users_model->get_one($id);
                    $names[] = $user->first_name . ' ' . $user->last_name;
                }
                $text .= "Был добавлен в задачу: " . implode(', ', $names);
            }

            return $text;
        }

        if($field === 'executors') {
            $ci = new App_Controller();
            $fromNames = '';
            $toNames = '';

            $from_ids = array_filter(explode(',', $from));
            $to_ids   = array_filter(explode(',', $to));

            $removed_ids = array_diff($from_ids, $to_ids);
            $added_ids   = array_diff($to_ids, $from_ids);

            $text = '';

            if (!empty($removed_ids)) {
                $names = [];
                foreach ($removed_ids as $id) {
                    $user = $ci->Users_model->get_one($id);
                    $names[] = $user->first_name . ' ' . $user->last_name;
                }
                $text .= "Удалён исполнитель: " . implode(', ', $names);
            }

            if (!empty($added_ids)) {
                if ($text) $text .= "\n";
                $names = [];
                foreach ($added_ids as $id) {
                    $user = $ci->Users_model->get_one($id);
                    $names[] = $user->first_name . ' ' . $user->last_name;
                }
                $text .= "Добавлен исполнитель: " . implode(', ', $names);
            }

            return $text;
        }

        if ($field == 'start_date' || $field == 'deadline') {

            $fromTime = $from ? strtotime($from) : null;
            $toTime   = $to ? strtotime($to) : null;

            if ($fromTime === $toTime) {
                continue;
            }

            if ($fromTime) {
                $from = date('Y-m-d H:i', $fromTime);
            }

            if ($toTime) {
                $to = date('Y-m-d H:i', $toTime);
            }
        }

        $text .= "\n• <b>{$info}</b>: {$from} → {$to}";
    }

    return $text;
}

/* --------------------------------------------------------------------------
   MESSAGE BUILDER
---------------------------------------------------------------------------*/
function build_telegram_message($message, $url, $roles, $description, $changes)
{
    $message = trim($message);
    $message = $url ? "<a href='{$url}'>{$message}</a>" : $message;

    $text = "$message $description";

    foreach($roles as $key => $role) {
        $text .= "<b>$key</b>" . ': ' . $role . "\n";
    }

    $text .= "\n";

    if ($changes) {
        $text .= $changes;
    }      

    $text = preg_replace('/<!--(.|\s)*?-->/', '', $text);
    return strip_tags($text, '<b><strong><i><em><u><a><code><pre>');
}

/* --------------------------------------------------------------------------
   GET LINK TO PROFILE
---------------------------------------------------------------------------*/
function make_profile_link($ci, $user) {
    $name = trim($user->first_name . ' ' . $user->last_name);
    $url = base_url("index.php/team_members/view/" . $user->id);
    return '<a href="' . $url . '" target="_blank">' . htmlspecialchars($name) . '</a>';
}

/* --------------------------------------------------------------------------
   TELEGRAM SENDER
---------------------------------------------------------------------------*/
function telegram_send($bot_token, $chat_id, $text)
{
    $data = [
        "chat_id" => $chat_id,
        "parse_mode" => "HTML",
        "text" => $text,
        "disable_web_page_preview" => true
    ];

    $ch = curl_init("https://api.telegram.org/bot{$bot_token}/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) return false;

    $decoded = json_decode($result);
    return isset($decoded->ok) && $decoded->ok;
}

/* --------------------------------------------------------------------------
   CURRENT EVENT TRACKER
---------------------------------------------------------------------------*/
$current_telegram_event = null;

function detect_event_from_changes($serialized_changes)
{
    if($serialized_changes == 'project_task_updated') {
        return 'notify_task_status_changed';
    }

    if($serialized_changes == 'project_task_assigned' || $serialized_changes == 'project_task_created') {
        return 'notify_task_assignees_changed';
    }

    if($serialized_changes == 'project_task_commented') {
        return 'notify_task_comment_added';
    }

    $changes = @unserialize($serialized_changes);
    if (!is_array($changes)) return '';

    $field_names = [
        "start_date"  => "notify_task_date_changed",
        "deadline"    => "notify_task_date_changed",
        "status_id"   => "notify_task_status_changed",
        'project_task_assigned' => "notify_task_assignees_changed",
        'executors' => 'notify_task_assignees_changed',
        'project_task_commented' => 'notify_task_comment_added',
    ];

    if(array_key_exists('collaborators', $changes)) {
        return 'notify_task_assignees_changed';
    }

    foreach ($changes as $field => $values) {
        if(isset($field_names[$field])) {
            return $field_names[$field];
        }
    }

    return 'undefined status';
}

function telegram_write_log($data)
{
    $log_file = '/var/www/crm_prime_lt_usr/data/www/crm2.prime-ltd.su/mylog.txt';

    $date = date('Y-m-d H:i:s');

    if (is_array($data) || is_object($data)) {
        $data = print_r($data, true);
    }

    $message = "[$date] " . $data . PHP_EOL;

    file_put_contents($log_file, $message, FILE_APPEND);
}

/* --------------------------------------------------------------------------
   MAIN FUNCTION
---------------------------------------------------------------------------*/
if (!function_exists('send_telegram_notification')) {
    function send_telegram_notification($event, $task_id = 0, $notification_id = 0)
    {
        $statuses = [
            1 => 'Планирование',
            2 => 'В работе',
            3 => 'Выполнено',
            4 => 'На уточнении у клиента',
            5 => 'Отложенная',
            6 => 'На проверку постановщику',
        ];

        try {
            $ci = new App_Controller();
            
            $UserNotificationSettingsModel = new \App\Models\User_notification_settings_model();
            $message = str_replace(["%s", '.'], '', app_lang("notification_" . $event));

            if($message == 'Задача поручена ') {
                $message = 'Изменён главный ответственный';
            }

            $notification_description = "";
            $url = "";
            $changes_text = "";

            $notification_info = $ci->Notifications_model->get_email_notification($notification_id);
            $task = $ci->Tasks_model->get_one($notification_info->task_id ?? 0);
            $actionType = detect_event_from_changes(
                $notification_info->activity_log_changes ?? 
                $notification_info->event ??
                $event
            );
            telegram_write_log('CURRENT TYPE');
            telegram_write_log($notification_info);
            telegram_write_log($actionType);

            if(isset($notification_info->activity_log_changes)) {
                $changes_text = build_changes_text($notification_info->activity_log_changes, $statuses);
            } else if($message == 'Задача обновлена') {
                $changes_text = '<b>Новый статус задачи: </b>' . $statuses[$task->status_id];
            }

            $url_attributes_array = get_notification_url_attributes($notification_info);
            $url = get_array_value($url_attributes_array, "url");

            $notification_description = view(
                "Telegram_Notification\Views\\notifications\\notification_description_for_telegram",
                ["notification" => $notification_info]
            );

            if (!$task) return false;

            $db = \Config\Database::connect();

            // ------------------------------
            // Собираем всех участников проекта
            // ------------------------------
            $recipients = [];

            $creator_id = $task->assigned_to ?? null;
            if ($creator_id) {
                $user = $ci->Users_model->get_one($creator_id);
                if ($user && !$user->deleted) $recipients['Ответственный'][] = $user;
            }

            if (!empty($task->executors)) {
                foreach (array_map('trim', explode(',', $task->executors)) as $uid) {
                    $user = $ci->Users_model->get_one($uid);
                    if ($user && !$user->deleted) $recipients['Исполнитель'][] = $user;
                }
            }

            if (!empty($task->collaborators)) {
                foreach (array_map('trim', explode(',', $task->collaborators)) as $uid) {
                    $user = $ci->Users_model->get_one($uid);
                    if ($user && !$user->deleted) $recipients['Участник'][] = $user;
                }
            }

            // ------------------------------
            // Формируем список всех участников задачи
            // ------------------------------
            $all_participants = [];

            foreach ($recipients as $role_name => $users) {

                $names = [];

                foreach ($users as $u) {
                    $names[] = make_profile_link($ci, $u);
                }

                if (!empty($names)) {
                    $all_participants[$role_name] = implode(', ', $names);
                }
            }

            $sended = [];
            foreach ($recipients as $role => $users) {
                if($role == 'Ответственный') {
                    $role = 'Создатель задачи';
                }

                foreach ($users as $user) {
                    if (in_array($user->id, $sended)) {
                        continue;
                    } else {
                        $sended[] = $user->id;
                    }

                    $user_notification_setting = $UserNotificationSettingsModel->get_by_user($user->id);

                    //конфиги юзера
                    telegram_write_log($user_notification_setting);
                    telegram_write_log($actionType);

                    $is_enabled = !empty($user_notification_setting->{$actionType});

                    if (!$is_enabled) {
                        telegram_write_log("Конфиг $actionType не активен");
                        continue;
                    }

                    telegram_write_log("uid: $user->id");
                    telegram_write_log("tid: $task->project_id");
                    telegram_write_log("role: $role");
                    
                    $rolessss = $db->table('telegram_project_role_settings')
                        ->where('user_id', $user->id)
                        ->where('project_id', $task->project_id)
                        ->get()
                        ->getResult();

                    telegram_write_log("Мои роли");
                    telegram_write_log($rolessss);

                    $setting = $db->table('telegram_project_role_settings')
                        ->where('user_id', $user->id)
                        ->where('project_id', $task->project_id)
                        ->where('role', $role)
                        ->where('enabled', 1)
                        ->get()
                        ->getRow();

                    $taskDescription = $notification_description;
                    // if(!empty($notification_info->task_description)) {
                    //     $taskDescription .= "<b>Описание задачи</b>: " . strip_tags($notification_info->task_description);
                    // }

                    if ($setting && !empty($user->telegram_chat_id)) {
                        telegram_write_log("Запись найдена");

                        $telegram_message = build_telegram_message(
                            $message,
                            $url,
                            $all_participants,
                            $taskDescription ?? '',
                            $changes_text ?? ''
                        );

                        telegram_send(get_telegram_notification_setting("bot_token"), $user->telegram_chat_id, $telegram_message);
                    } else {
                        telegram_write_log("Запись найдена");
                    }
                }
            }
        } catch (\Exception $ex) {
            telegram_write_log("Telegram notification error: " . $ex->getMessage());
            telegram_write_log("Telegram notification line: " . $ex->getLine());
            telegram_write_log("Telegram notification file: " . $ex->getFile());
        }

        return true;
    }
}