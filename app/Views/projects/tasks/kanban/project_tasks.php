<div class="card mb0 mt10">
    <div class="card-header title-tab clearfix">
        <h4 class="float-start"><?php echo app_lang('tasks') . " " . app_lang('kanban'); ?><span class="ms-4 clickable project-title-section-hide-button"><i data-feather='arrow-up' class='icon-16'></i></span></h4>
        <div class="title-button-group">
            <?php
            if ($login_user->user_type == "staff" && $can_edit_tasks) {
                echo modal_anchor("", "<i data-feather='edit' class='icon-16'></i> " . app_lang('batch_update'), array("class" => "btn btn-info text-white hide batch-update-btn", "title" => app_lang('batch_update'), "data-post-project_id" => $project_id));
                echo js_anchor("<i data-feather='check-square' class='icon-16'></i> " . app_lang("cancel_selection"), array("class" => "hide btn btn-default batch-cancel-btn"));
            }
            if ($can_create_tasks) {
                echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_multiple_tasks'), array("class" => "btn btn-default", "title" => app_lang('add_multiple_tasks'), "data-post-project_id" => $project_id, "data-post-add_type" => "multiple"));
                echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default", "title" => app_lang('add_task'), "data-post-project_id" => $project_id));
            }
            ?>
        </div>
    </div>
    <div class="bg-white">
        <div id="kanban-filters"></div>
    </div>
</div>
<div id="load-kanban"></div>

<script type="text/javascript">

    $(document).ready(function () {
        var filterDropdown = [];

        if ("<?php echo $login_user->user_type ?>" == "staff") {
            filterDropdown = [
                {name: "quick_filter", class: "w200", showHtml: true, options: <?php echo view("tasks/quick_filters_dropdown"); ?>},
                {name: "milestone_id", class: "w200", options: <?php echo $milestone_dropdown; ?>}
                , <?php echo $custom_field_filters; ?>
            ];
        } else {
<?php if ($show_milestone_info) { ?>
                filterDropdown = [
                    {name: "milestone_id", class: "w200", options: <?php echo $milestone_dropdown; ?>}
                    , <?php echo $custom_field_filters; ?>
                ];
<?php } else { ?>
                filterDropdown = [<?php echo $custom_field_filters; ?>];
<?php } ?>
        }

        var smartFilter = "project_tasks_kanban"; //a to z and _ only. should be unique to avoid conflicts
        if ("<?php echo $login_user->user_type ?>" == "client") {
            smartFilter = false;
        }

        var scrollLeft = 0;
        $("#kanban-filters").appFilters({
            source: '<?php echo_uri("tasks/project_tasks_kanban_data/" . $project_id) ?>',
            targetSelector: '#load-kanban',
            reloadSelector: "#reload-kanban-button",
            smartFilterIdentity: smartFilter,
            contextMeta: {contextId: "<?php echo $project_id; ?>", dependencies: ["milestone_id"]}, //useful to seperate instance related filters. Ex. Milestones are different for each projects.
            search: {name: "search"},
            filterDropdown: filterDropdown,
            multiSelect: [
                {
                    class: "w200",
                    name: "responsible_user_id",
                    text: "<?php echo app_lang('team_responsible'); ?>",
                    options: <?php echo $assigned_to_dropdown; ?>
                },
                {
                    class: "w200",
                    name: "executors_user_id",
                    text: "<?php echo app_lang('executors'); ?>",
                    options: <?php echo $members_to_dropdown; ?>
                },
                {
                    class: "w200",
                    name: "member_user_id",
                    text: "<?php echo app_lang('team_member'); ?>",
                    options: <?php echo $members_to_dropdown; ?>
                },
                {
                    class: "w200",
                    name: "priority_id",
                    text: "<?php echo app_lang('priority'); ?>",
                    options: <?php echo $priorities_dropdown; ?>
                },
                {
                    class: "w200",
                    name: "label_id",
                    text: "<?php echo app_lang('label'); ?>",
                    options: <?php echo $labels_dropdown; ?>
                },
            ],
            singleDatepicker: [{name: "deadline", defaultText: "<?php echo app_lang('deadline') ?>", class: "w200",
                    options: [
                        {value: "expired", text: "<?php echo app_lang('expired') ?>"},
                        {value: moment().format("YYYY-MM-DD"), text: "<?php echo app_lang('today') ?>"},
                        {value: moment().add(1, 'days').format("YYYY-MM-DD"), text: "<?php echo app_lang('tomorrow') ?>"},
                        {value: moment().add(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 7); ?>"},
                        {value: moment().add(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_number_of_days'), 15); ?>"}
                    ]}],
            beforeRelaodCallback: function () {
                scrollLeft = $("#kanban-wrapper").scrollLeft();
            },
            afterRelaodCallback: function () {
                setTimeout(function () {
                    $("#kanban-wrapper").animate({scrollLeft: scrollLeft}, 'slow');
                }, 500);
                hideBatchTasksBtn();
            }
        });

        $('body').on('click', '.project-title-section-hide-button', function (e) {
            $(".project-title-section").addClass("hide");
            $(this).addClass("project-title-section-show-button");
            $(this).removeClass("project-title-section-hide-button");

            $(this).html("<?php echo "<i data-feather='arrow-down' class='icon-16'></i> "; ?>");
            feather.replace();

            adjustViewHeightWidth();
        });

        $('body').on('click', '.project-title-section-show-button', function (e) {
            $(".project-title-section").removeClass("hide");
            $(this).addClass("project-title-section-hide-button");
            $(this).removeClass("project-title-section-show-button");

            $(this).html("<?php echo "<i data-feather='arrow-up' class='icon-16'></i> "; ?>");
            feather.replace();
            adjustViewHeightWidth();
        });

    });

</script>

<?php echo view("tasks/quick_filters_helper_js"); ?>
