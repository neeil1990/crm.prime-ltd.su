<?php
if (count($notifications)) {

    foreach ($notifications as $notification) {
        //get url attributes
        $url_attributes_array = get_notification_url_attributes($notification);
        $url_attributes = get_array_value($url_attributes_array, "url_attributes");
        $url = get_array_value($url_attributes_array, "url");

        //check read/unread class
        $notification_class = "";
        if (!$notification->is_read) {
            $notification_class = "unread-notification";
        }

        if ((!$url || $url == "#") && $url_attributes == "href='$url'") {
            $notification_class .= " not-clickable";
        } else {
            $notification_class .= " clickable";
        }

        $avatar = get_avatar("system_bot");
        $title = get_setting("app_title");
        if ($notification->user_id) {
            if ($notification->user_id == "999999998") {
                //check if it's bitbucket commit notification
                $avatar = get_avatar("bitbucket");
                $title = "Bitbucket";
            } else if ($notification->user_id == "999999997") {
                //check if it's github commit notification
                $avatar = get_avatar("github");
                $title = "GitHub";
            } else if ($notification->user_id == "999999996") {
                //check if it's public notification of contract/estimate/proposal
                $signer_info = $notification->contract_meta_data;
                if ($notification->estimate_id) {
                    $signer_info = $notification->estimate_meta_data;
                } else if ($notification->proposal_id) {
                    $signer_info = $notification->proposal_meta_data;
                }

                $signer_info = @unserialize($signer_info);
                if (!($signer_info && is_array($signer_info))) {
                    $signer_info = array();
                }

                $signer_name = get_array_value($signer_info, "name");
                if ($signer_name) {
                    $title = $signer_name;
                } else {
                    $title = app_lang("unknown_user");
                }

                $avatar = get_avatar(); //show default user image
            } else {
                $avatar = get_avatar($notification->user_image);
                $title = $notification->user_id ? $notification->user_name : get_setting("app_title");
            }
        }

        //for custom field changes, we've to check if the field has any restrictions
        //like 'visible to admins only' or 'hide from clients'
        $changes_array = array();
        if ($notification->activity_log_changes !== "") {
            if ($notification->event === "bitbucket_push_received" || $notification->event === "github_push_received") {
                $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, $notification->event, true);
            } else {
                $changes_array = get_change_logs_array($notification->activity_log_changes, $notification->activity_log_type, "all");
            }
        }

        if ($notification->activity_log_changes == "" || ($notification->activity_log_changes !== "" && count($changes_array))) {
            ?>

            <a class="list-group-item border-bottom dropdown-item <?php echo $notification_class; ?>" data-notification-id="<?php echo $notification->id; ?>" <?php echo $url_attributes; ?> >
                <div class="d-flex text-wrap">
                    <div class="flex-shrink-0 me-2">
                        <span class="avatar avatar-xs">
                            <img src="<?php echo $avatar; ?>" alt="..." />
                            <!--  if user name is not present then -->
                        </span>
                    </div>
                    <div class="w100p">
                        <div class="mb5">
                            <strong><?php echo $title; ?></strong>
                            <span class="text-off float-end">
                                <small><?php echo format_to_relative_time($notification->created_at); ?></small>
                            </span>
                        </div>
                        <div class="m0 text-break">
                            <?php
                            echo sprintf(app_lang("notification_" . $notification->event), "<strong>" . $notification->to_user_name . "</strong>");

                            //replace anchor tags with text to fix tagging error
                            echo preg_replace('#<a.*?>(.*?)</a>#i', '\1', view("notifications/notification_description", array("notification" => $notification, "changes_array" => $changes_array)));
                            ?>
                        </div>
                        <? if ($notification->is_read): ?>
                            <small class="read-notify badge rounded-pill bg-info" style="font-size: 80%;">Прочитанное</small>
                        <? else: ?>
                            <small class="unread-notify badge rounded-pill bg-primary" style="font-size: 80%;">Непрочитанное</small>
                        <? endif; ?>
                    </div>
                </div>
            </a>
            <?php
        }
    }

    if ($result_remaining) {
        $next_container_id = "load" . $next_page_offset;
        ?>
        <div id="<?php echo $next_container_id; ?>">

        </div>

        <div id="loader-<?php echo $next_container_id; ?>" >
            <div class="text-center p20 clearfix margin-top-5">
                <?php
                echo ajax_anchor(get_uri("notifications/load_more/" . $next_page_offset), app_lang("load_more"), array("class" => "btn btn-default load-more mt15 p10 spinning-btn pr0", "data-remove-on-success" => "#loader-" . $next_container_id, "title" => app_lang("load_more"), "data-inline-loader" => "1", "data-real-target" => "#" . $next_container_id));
                ?>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <span class="list-group-item"><?php echo app_lang("no_new_notifications"); ?></span>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        let $body = $("body");
        let readNotifyClass = "read-notify";
        let unreadNotifyClass = "unread-notify";
        let bgInfoClass = "bg-info";
        let bgPrimaryClass = "bg-primary";

        $(".unread-notification").click(function (e) {
            notificationStatusAsRead( $(this) );
        });

        $body.on("click", "." + readNotifyClass, function () {
            let self = $(this);

            notificationStatusAsUnRead(self.closest(".list-group-item"));

            self.removeClass([readNotifyClass, bgInfoClass]);
            self.addClass([unreadNotifyClass, bgPrimaryClass]);
            self.text("Непрочитанное");

            return false;
        });

        $body.on("click", "." + unreadNotifyClass, function () {
            let self = $(this);

            notificationStatusAsRead(self.closest(".list-group-item"));

            self.removeClass([unreadNotifyClass, bgPrimaryClass]);
            self.addClass([readNotifyClass, bgInfoClass]);
            self.text("Прочитанное");

            return false;
        });

        function notificationStatusAsRead(el)
        {
            $.ajax({
                url: '<?php echo get_uri("notifications/set_notification_status_as_read") ?>/' + el.attr("data-notification-id")
            });

            el.removeClass("unread-notification");

            el.blur();
        }

        function notificationStatusAsUnRead(el)
        {
            $.ajax({
                url: '<?php echo get_uri("notifications/set_notification_status_as_unread") ?>/' + el.attr("data-notification-id")
            });

            el.addClass("unread-notification");

            el.blur();
        }
    });
</script>
