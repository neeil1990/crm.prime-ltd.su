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

    telegram_write_log($role_map);
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
function build_changes_text($serialized_changes)
{
    if (empty($serialized_changes)) return '';

    $changes = @unserialize($serialized_changes);
    if (!is_array($changes)) return '';

    $field_names = [
        "start_date"  => "Дата начала",
        "deadline"    => "Дата окончания",
        "status"      => "Статус",
        "assigned_to" => "Ответственный",
        "status_id"   => "Изменение статуса задачи"
    ];

    $statuses = [
        1 => 'Планирование',
        2 => 'В работе',
        3 => 'Выполнено',
        4 => 'На уточнении у клиента',
        5 => 'Отложенная',
        6 => 'На проверку постановщику',
    ];

    $text = "<b>Изменения:</b>";

    foreach ($changes as $field => $values) {

        $info = isset($field_names[$field]) ? $field_names[$field] : $field;
        $from = $values['from'] ? $values['from'] : '';
        $to   = $values['to'] ? $values['to'] : '';

        if ($field === 'status_id') {
            $from = $statuses[(int)$from] ? $statuses[(int)$from] : $from;
            $to   = $statuses[(int)$to] ? $statuses[(int)$to] : $to;
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
   MAIN FUNCTION
---------------------------------------------------------------------------*/
if (!function_exists('send_telegram_notification')) {

    function send_telegram_notification($event, $user_id = 0, $notification_id = 0)
    {
        $bot_token = get_telegram_notification_setting("bot_token");
        $chat_id   = get_telegram_notification_setting("chat_id");

        if (!$bot_token || !$chat_id) return false;

        $ci = new App_Controller();

        $message = app_lang("notification_" . $event);
        $notification_description = "";
        $url = "";

        if ($notification_id) {

            $notification_info = $ci->Notifications_model->get_email_notification($notification_id);

            if (!$notification_info) return false;

            $roles_data = get_task_roles_and_participants(
                $ci,
                $notification_info->task_id ? $notification_info->task_id : 0,
                $user_id
            );

            if($roles_data['Ваша роль'] == 'Вы не учавствуете') {
                telegram_write_log('Не участвуем - прерываем скрипт');
                return true;
            }

            $changes_text = build_changes_text($notification_info->activity_log_changes ? $notification_info->activity_log_changes : '');

            $url_attributes_array = get_notification_url_attributes($notification_info);
            $url = get_array_value($url_attributes_array, "url");

            $notification_description = view(
                "Telegram_Notification\Views\\notifications\\notification_description_for_telegram",
                ["notification" => $notification_info]
            );
        }

        // $user_info = $ci->Users_model->get_one($user_id);

        $telegram_message = build_telegram_message(
            $message,
            $url,
            $roles_data,
            $notification_description ? $notification_description : '',
            $changes_text ? $changes_text : ''
        );

        return telegram_send($bot_token, $chat_id, $telegram_message);
    }
}