<div id="page-content" class="page-wrapper clearfix">
    <div class="card clearfix">
        <div class="page-title clearfix">
            <h1> <?php echo app_lang('subscriptions'); ?></h1>
            <div class="title-button-group">
                <?php if ($can_edit_subscriptions) { ?>
                    <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => app_lang('manage_labels'), "data-post-type" => "subscription")); ?>
                    <?php echo modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_subscription'), array("class" => "btn btn-default mb0", "title" => app_lang('add_subscription'))); ?>
                <?php } ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="subscriptions-table" class="display" cellspacing="0" width="100%">   
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo $can_edit_subscriptions ?>") {
            optionVisibility = true;
        }

        var currency_dropdown = <?php echo $currencies_dropdown; ?>;
        var filterDropdowns = [];
        if (currency_dropdown.length > 1) {
            filterDropdowns.push({name: "currency", class: "w150", options: currency_dropdown});
        }

        filterDropdowns.push({name: "repeat_type", class: "w200", options: <?php echo $repeat_types_dropdown; ?>});
        filterDropdowns.push(<?php echo $custom_field_filters; ?>);

        $("#subscriptions-table").appTable({
            source: '<?php echo_uri("subscriptions/list_data") ?>',
            order: [[0, "desc"]],
            rangeDatepicker: [{startDate: {name: "next_billing_start_date", value: ""}, endDate: {name: "next_billing_end_date", value: ""}, showClearButton: true, label: "<?php echo app_lang('next_billing_date'); ?>", ranges: ['tomorrow', 'next_7_days', 'this_month', 'next_month', 'this_year', 'next_year']}],
            filterDropdown: filterDropdowns,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("subscription_id") ?>", "class": "w10p", "iDataSort": 0},
                {title: "<?php echo app_lang("title") ?> ", "class": "w20p"},
                {title: "<?php echo app_lang("type") ?> ", "class": "w100"},
                {title: "<?php echo app_lang("client") ?>", "class": "w10p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("first_billing_date") ?>", "iDataSort": 5, "class": "w10p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("next_billing_date") ?>", "iDataSort": 7, "class": "w10p"},
                {title: "<?php echo app_lang("repeat_every") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("cycles") ?>", "class": "w100 text-center"},
                {title: "<?php echo app_lang("status") ?>", "class": "w100 text-center"},
                {title: "<?php echo app_lang("amount") ?>", "class": "w100 text-right"}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w100", visible: optionVisibility}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 8, 9, 10, 11, 12], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 8, 9, 10, 11, 12], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 12, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol, conversionRate: <?php echo $conversion_rate; ?>}]

        });
    }
    );
</script>