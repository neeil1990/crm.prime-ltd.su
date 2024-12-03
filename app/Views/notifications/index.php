<div id="page-content" class="page-wrapper clearfix">
    <div class="card dashed-row">

        <div class="page-title clearfix">
            <h4> <?php echo app_lang('notifications'); ?></h4>
        </div>

        <div class="form-group mt-4">
            <div class="row">
                <div class="col-6 ps-4">
                    <?php
                    echo form_input(array(
                        "id" => "notification_event_filter",
                        "name" => "notification_event_filter",
                        "value" => $notification_event_filter_value,
                        "class" => "form-control",
                        "placeholder" => app_lang('notification_filter')
                    ));
                    ?>
                </div>
                <div class="col-3">
                    <?php
                    echo form_input(array(
                        "id" => "notification_is_read_filter",
                        "name" => "notification_is_read_filter",
                        "value" => $notification_is_read_filter_value,
                        "class" => "form-control",
                        "placeholder" => app_lang('status')
                    ));
                    ?>
                </div>
                <div class="col-3">
                    <a href="javascript:window.location.reload();" class="btn btn-default">Обновить</a>
                </div>
            </div>
        </div>

        <div>
            <?php
            $view_data["notifications"] = $notifications;

            echo view("notifications/list_data", $view_data);
            ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        let $notification_event_filter = $('#notification_event_filter');

        $notification_event_filter.select2({multiple: true, data: <?php echo $filter_options; ?>});

        $notification_event_filter.on('change.select2', function (e) {
            $.ajax({
                url: '<?php echo_uri("notifications/save_event_filter_options") ?>',
                type: "POST",
                data: {
                    notification_event_filter: e.val,
                }
            });
        });

        let $notification_is_read_filter = $('#notification_is_read_filter');

        $notification_is_read_filter.select2({data: [
                {id: "", text: "По умолчанию"},
                {id: "0", text: "Непрочитанные"},
                {id: "1", text: "Прочитанные"},
            ]});

        $notification_is_read_filter.on('change.select2', function (e) {
            $.ajax({
                url: '<?php echo_uri("notifications/save_is_read_filter_options") ?>',
                type: "POST",
                data: {
                    notification_is_read_filter: e.val,
                }
            });
        });
    });
</script>
