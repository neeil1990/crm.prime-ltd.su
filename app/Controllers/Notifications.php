<?php

namespace App\Controllers;

class Notifications extends Security_Controller {

    public $notification_event_filter_setting;
    public $notification_is_read_filter_setting;

    function __construct() {
        parent::__construct();

        helper('notifications');

        $user_id = $this->login_user->id;
        $this->notification_event_filter_setting = "user_" . $user_id . "_notification_event_filter";
        $this->notification_is_read_filter_setting = "user_" . $user_id . "_notification_is_read_filter";
    }

    //load notifications view
    function index() {
        $view_data = $this->_prepare_notification_list();
        $view_data["filter_options"] = json_encode($this->event_filter_options());
        $view_data["notification_event_filter_value"] = $this->Settings_model->get_setting($this->notification_event_filter_setting);
        $view_data["notification_is_read_filter_value"] = $this->Settings_model->get_setting($this->notification_is_read_filter_setting);

        return $this->template->rander("notifications/index", $view_data);
    }

    function event_filter_options() {
        $options = [];
        $events = $this->Notifications_model->get_notification_settings_filter();

        foreach ($events as $event) {
            $options[] = ['id' => $event->event, 'text' => app_lang("notification_" . $event->event)];
        }

        return $options;
    }

    function save_event_filter_options() {
        $notification_event_filter = $this->request->getPost("notification_event_filter");

        $value = ($notification_event_filter) ? implode(",", $notification_event_filter) : "";

        $this->Settings_model->save_setting($this->notification_event_filter_setting, $value, "user");
    }

    function save_is_read_filter_options() {
        $value = $this->request->getPost("notification_is_read_filter");

        $this->Settings_model->save_setting($this->notification_is_read_filter_setting, $value, "user");
    }

    function load_more($offset = 0) {
        validate_numeric_value($offset);
        $view_data = $this->_prepare_notification_list($offset);
        return $this->template->view("notifications/list_data", $view_data);
    }

    function count_notifications() {
        $notifiations = $this->Notifications_model->count_notifications($this->login_user->id, $this->login_user->notification_checked_at);
        echo json_encode(array("success" => true, 'total_notifications' => $notifiations));
    }

    function get_notifications() {
        $view_data = $this->_prepare_notification_list();
        $view_data["result_remaining"] = false; //don't show load more option in notification popop
        echo json_encode(array("success" => true, 'notification_list' => $this->template->view("notifications/list", $view_data, true)));
    }

    function update_notification_checking_status() {
        $now = get_current_utc_time();
        $data = array("notification_checked_at" => $now);
        $this->Users_model->ci_save($data, $this->login_user->id);
    }

    function set_notification_status_as_read($notification_id = 0) {
        if ($notification_id) {
            validate_numeric_value($notification_id);
            $this->Notifications_model->set_notification_status_as_read($notification_id, $this->login_user->id);
        } else {
            //mark all notification as read
            $this->Notifications_model->set_notification_status_as_read(0, $this->login_user->id);
            echo json_encode(array("success" => true, 'message' => app_lang('marked_all_notifications_as_read')));
        }
    }

    function set_notification_status_as_unread($notification_id = 0) {
        if ($notification_id) {
            validate_numeric_value($notification_id);
            $this->Notifications_model->set_notification_status_as_unread($notification_id, $this->login_user->id);
        }
    }

    private function _prepare_notification_list($offset = 0) {
        $options = [];
        $options["event"] = $this->Settings_model->get_setting($this->notification_event_filter_setting);
        $options["read"] = $this->Settings_model->get_setting($this->notification_is_read_filter_setting);

        $notifiations = $this->Notifications_model->get_notifications($this->login_user->id, $offset, 100, $options);
        $view_data['notifications'] = $notifiations->result;
        $view_data['found_rows'] = $notifiations->found_rows;
        $next_page_offset = $offset + 100;
        $view_data['next_page_offset'] = $next_page_offset;
        $view_data['result_remaining'] = $notifiations->found_rows > $next_page_offset;
        return $view_data;
    }

}

/* End of file notifications.php */
/* Location: ./app/controllers/Notifications.php */
