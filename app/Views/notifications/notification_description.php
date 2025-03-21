<?php

if ($notification->task_id && $notification->task_title) {
    echo "<div>" . app_lang("task") . ": #$notification->task_id - " . $notification->task_title . "</div>";
}

if ($notification->activity_log_changes !== "") {
    $final_changes_array = isset($changes_array) ? $changes_array : array();

    if (!count($final_changes_array)) {
        if ($notification->event === "bitbucket_push_received" || $notification->event === "github_push_received") {
            $final_changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, $notification->event, true);
        } else {
            $final_changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, "all");
        }
    }

    if (count($final_changes_array)) {
        if ($notification->event === "bitbucket_push_received" || $notification->event === "github_push_received") {
            echo get_array_value($final_changes_array, 0);
            unset($final_changes_array[0]);
        }

        echo "<ul>";
        foreach ($final_changes_array as $change) {
            //don't show the change log if there is any anchor tag
            if (!strpos($change, "</a>")) {
                echo process_images_from_content($change, false);
            }
        }
        echo "</ul>";
    }
}

if ($notification->payment_invoice_id) {
    echo "<div>" . to_currency($notification->payment_amount, $notification->client_currency_symbol) . "  -  " . $notification->payment_invoice_display_id . "</div>";
}

if ($notification->ticket_id && $notification->ticket_title) {
    echo "<div>" . get_ticket_id($notification->ticket_id) . " - " . $notification->ticket_title . "</div>";
}

if ($notification->leave_id && $notification->leave_start_date) {
    $leave_date = format_to_date($notification->leave_start_date, FALSE);
    if ($notification->leave_start_date != $notification->leave_end_date) {
        $leave_date = sprintf(app_lang('start_date_to_end_date_format'), format_to_date($notification->leave_start_date, FALSE), format_to_date($notification->leave_end_date, FALSE));
    }
    echo "<div>" . app_lang("date") . ": " . $leave_date . "</div>";
}

if ($notification->project_comment_id && $notification->project_comment_title && !strpos($notification->project_comment_title, "</a>")) {
    echo "<div>" . app_lang("comment") . ": " . convert_mentions(convert_comment_link(process_images_from_content($notification->project_comment_title, false), false), false) . "</div>";
}

if ($notification->project_file_id && $notification->project_file_title) {
    echo "<div>" . app_lang("file") . ": " . remove_file_prefix($notification->project_file_title) . "</div>";
}


if ($notification->project_id && $notification->project_title) {
    echo "<div>" . app_lang("project") . ": " . $notification->project_title . "</div>";
}

if ($notification->estimate_id) {
    echo "<div>" . get_estimate_id($notification->estimate_id) . "</div>";
}

if ($notification->contract_id && $notification->contract_title) {
    echo "<div>" . get_contract_id($notification->contract_id) . ": " . $notification->contract_title . "</div>";
}

if ($notification->proposal_id) {
    echo "<div>" . get_proposal_id($notification->proposal_id) . "</div>";
}

if ($notification->order_id) {
    echo "<div>" . get_order_id($notification->order_id) . "</div>";
}

if ($notification->event_title) {
    echo "<div>" . app_lang("event") . ": " . $notification->event_title . "</div>";
}

if ($notification->announcement_title) {
    echo "<div>" . app_lang("title") . ": " . $notification->announcement_title . "</div>";
}

if ($notification->post_id && $notification->posts_title) {
    echo "<div>" . app_lang("comment") . ": " . $notification->posts_title . "</div>";
}

if ($notification->subscription_title) {
    echo "<div>" . app_lang("title") . ": " . $notification->subscription_title . "</div>";
}

//show data from hook
try {
    $notification_descriptions = array();
    $notification_descriptions = app_hooks()->apply_filters('app_filter_notification_description', $notification_descriptions, $notification);
    if ($notification_descriptions && is_array($notification_descriptions)) {
        foreach ($notification_descriptions as $notification_description) {
            echo $notification_description;
        }
    }
} catch (\Exception $ex) {
    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
}

if ($notification->client_id && $notification->company_name) {
    echo "<div>" . app_lang("client") . ": " . $notification->company_name . "</div>";
}

if ($notification->lead_id && $notification->lead_company_name) {
    echo "<div>" . app_lang("lead") . ": " . $notification->lead_company_name . "</div>";
}

if ($notification->expense_id && $notification->expense_title) {
    echo "<div>" . app_lang("expense") . ": " . $notification->expense_title . "</div>";
}

if ($notification->subscription_id) {
    echo "<div>" . get_subscription_id($notification->subscription_id) . "</div>";
}
