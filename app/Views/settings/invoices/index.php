<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "invoices";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li class="title-tab">
                        <h4 class="pl15 pt10 pr15"><?php echo app_lang("invoice_settings"); ?></h4>
                    </li>
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#invoice-style-settings-tab"> <?php echo app_lang('style'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("settings/invoice_general"); ?>" data-bs-target="#invoice-general-settings-tab"><?php echo app_lang('general'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("settings/invoice_reminders"); ?>" data-bs-target="#invoice-reminder-settings-tab"><?php echo app_lang('reminders'); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="invoice-style-settings-tab">
                        <?php echo form_open(get_uri("settings/save_invoice_settings"), array("id" => "invoice-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_prefix" class=" col-md-2"><?php echo app_lang('invoice_prefix'); ?></label>
                                    <div class=" col-md-10">
                                        <?php
                                        echo form_input(array(
                                            "id" => "invoice_prefix",
                                            "name" => "invoice_prefix",
                                            "value" => get_setting("invoice_prefix"),
                                            "class" => "form-control",
                                            "placeholder" => strtoupper(app_lang("invoice")) . " #"
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $invoice_number_format_dropdown = array(
                                "1_DIGITS" => "1",
                                "2_DIGITS" => "01",
                                "3_DIGITS" => "001",
                                "4_DIGITS" => "0001",
                                "5_DIGITS" => "00001",
                                "YEAR-1_DIGITS" => "YYYY-1",
                                "YEAR-2_DIGITS" => "YYYY-01",
                                "YEAR-3_DIGITS" => "YYYY-001",
                                "YEAR-4_DIGITS" => "YYYY-0001",
                                "YEAR-5_DIGITS" => "YYYY-00001",
                                "YEAR/1_DIGITS" => "YYYY/1",
                                "YEAR/2_DIGITS" => "YYYY/01",
                                "YEAR/3_DIGITS" => "YYYY/001",
                                "YEAR/4_DIGITS" => "YYYY/0001",
                                "YEAR/5_DIGITS" => "YYYY/00001"
                            );
                            ?>
                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_number_format" class=" col-md-2"><?php echo app_lang('invoice_number_format'); ?></label>

                                    <div class="col-md-3">
                                        <?php
                                        echo form_dropdown(
                                            "invoice_number_format",
                                            $invoice_number_format_dropdown,
                                            get_setting('invoice_number_format'),
                                            "class='select2 mini' id='invoice_number_format'"
                                        );
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group <?php echo strpos(get_setting("invoice_number_format"), "YEAR") !== false ? '' : 'hide'; ?>" id="invoice-number-format-year-section">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="year_based_on" class=" col-md-2"><?php echo app_lang('year_based_on'); ?></label>

                                        <div class="col-md-10">
                                            <?php
                                            echo form_dropdown(
                                                "year_based_on",
                                                array("due_date" => app_lang("due_date"), "bill_date" => app_lang("bill_date")),
                                                get_setting('year_based_on'),
                                                "class='select2 mini'"
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <label for="reset_invoice_number_every_year" class="col-md-2"><?php echo app_lang('reset_invoice_number_every_year'); ?></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_checkbox("reset_invoice_number_every_year", "1", get_setting("reset_invoice_number_every_year") ? true : false, "id='reset_invoice_number_every_year' class='form-check-input'");
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group <?php echo (strpos(get_setting("invoice_number_format"), "YEAR") !== false && get_setting("reset_invoice_number_every_year")) ? "hide" : "" ?>" id="initial-number-of-the-invoice">
                                <input type="hidden" id="last_invoice_id" name="last_invoice_id" value="<?php echo $last_id; ?>" />
                                <div class="row">
                                    <label for="initial_number_of_the_invoice" class="col-md-2"><?php echo app_lang('initial_number_of_the_invoice'); ?></label>
                                    <div class="col-md-3">
                                        <?php
                                        echo form_input(array(
                                            "id" => "initial_number_of_the_invoice",
                                            "name" => "initial_number_of_the_invoice",
                                            "type" => "number",
                                            "value" => (get_setting("initial_number_of_the_invoice") > ($last_id + 1)) ? get_setting("initial_number_of_the_invoice") : ($last_id + 1),
                                            "class" => "form-control mini",
                                            "data-rule-greaterThan" => "#last_invoice_id",
                                            "data-msg-greaterThan" => app_lang("the_invoices_id_must_be_larger_then_last_invoice_id")
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_color" class=" col-md-2"><?php echo app_lang('invoice_color'); ?></label>
                                    <div class=" col-md-10">
                                        <input type="color" id="invoice_color" name="invoice_color" value="<?php echo get_setting("invoice_color"); ?>" />
                                        <span class="ml10"><?php echo anchor("company", app_lang("change_invoice_logo")); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_item_list_background" class="col-md-2"><?php echo app_lang('invoice_item_list_background_color'); ?> </label>
                                    <div class=" col-md-10">
                                        <input type="color" id="invoice_item_list_background" name="invoice_item_list_background" value="<?php echo get_setting("invoice_item_list_background"); ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <label for="enable_background_image_for_invoice_pdf" class="col-md-2"><?php echo app_lang('enable_background_image_for_pdf'); ?> </label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_checkbox("enable_background_image_for_invoice_pdf", "1", get_setting("enable_background_image_for_invoice_pdf") ? true : false, "id='enable_background_image_for_invoice_pdf' class='form-check-input'");
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="related_to_pdf_background_setting form-group <?php echo get_setting("enable_background_image_for_invoice_pdf") ? "" : "hide" ?>">
                                <div class="form-group">
                                    <div class="row">
                                        <label class=" col-md-2"><?php echo app_lang('pdf_background_image'); ?></label>
                                        <div class=" col-md-10">
                                            <?php if (get_setting("invoice_pdf_background_image")) { ?>
                                                <div class="float-start mr15">
                                                    <img id="pdf-background-image-preview" style="max-width: 55px; max-height: 80px;" src="<?php echo get_file_from_setting("invoice_pdf_background_image", false, get_setting("timeline_file_path")); ?>" alt="..." />
                                                </div>
                                            <?php } ?>
                                            <div class="float-start mr15">
                                                <?php echo view("includes/dropzone_preview"); ?>
                                            </div>
                                            <div class="float-start upload-file-button btn btn-default btn-sm">
                                                <span>...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <label for="set_invoice_pdf_background_only_on_first_page" class="col-md-2"><?php echo app_lang('set_background_only_on_first_page'); ?> </label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_checkbox("set_invoice_pdf_background_only_on_first_page", "1", get_setting("set_invoice_pdf_background_only_on_first_page") ? true : false, "id='set_invoice_pdf_background_only_on_first_page' class='form-check-input'");
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_style" class=" col-md-2"><?php echo app_lang('invoice_style'); ?></label>
                                    <div class="col-md-10">
                                        <?php
                                        $invoice_style = get_setting("invoice_style") ? get_setting("invoice_style") : "style_1";
                                        ?>
                                        <input type="hidden" id="invoice_style" name="invoice_style" value="<?php echo $invoice_style; ?>" />

                                        <div class="clearfix invoice-styles">
                                            <div data-value="style_1" class="item <?php echo $invoice_style == 'style_1' ? ' active ' : ''; ?>">
                                                <span class="selected-mark <?php echo $invoice_style == 'style_1' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                                <img src="<?php echo get_file_uri("assets/images/invoice_style_1.png") ?>" alt="style_1" />
                                            </div>
                                            <div data-value="style_2" class="item <?php echo $invoice_style === 'style_2' ? ' active ' : ''; ?>">
                                                <span class="selected-mark <?php echo $invoice_style === 'style_2' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                                <img src="<?php echo get_file_uri("assets/images/invoice_style_2.png") ?>" alt="style_2" />
                                            </div>
                                            <div data-value="style_3" class="item <?php echo $invoice_style === 'style_3' ? ' active ' : ''; ?>">
                                                <span class="selected-mark <?php echo $invoice_style === 'style_3' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                                <img src="<?php echo get_file_uri("assets/images/invoice_style_3.png") ?>" alt="style_3" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <label for="invoice_footer" class=" col-md-2"><?php echo app_lang('invoice_footer'); ?></label>
                                    <div class=" col-md-10">
                                        <?php
                                        echo form_textarea(array(
                                            "id" => "invoice_footer",
                                            "name" => "invoice_footer",
                                            "value" => process_images_from_content(get_setting("invoice_footer"), false),
                                            "class" => "form-control"
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="invoice-general-settings-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="invoice-reminder-settings-tab"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo view("includes/cropbox"); ?>

<?php
load_css(array(
    "assets/js/summernote/summernote.css"
));
load_js(array(
    "assets/js/summernote/summernote.min.js"
));
?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#invoice-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function(data) {
                $.each(data, function(index, obj) {
                    if (obj.name === "invoice_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#invoice_footer"));
                    }
                });
            },
            onSuccess: function(result) {
                if (result.success) {
                    appAlert.success(result.message, {
                        duration: 10000
                    });
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#invoice-settings-form .select2").select2();

        initWYSIWYGEditor("#invoice_footer", {
            height: 100
        });

        $(".cropbox-upload").change(function() {
            showCropBox(this);
        });

        $(".invoice-styles .item").click(function() {
            $(".invoice-styles .item").removeClass("active");
            $(".invoice-styles .item .selected-mark").addClass("hide");
            $(this).addClass("active");
            $(this).find(".selected-mark").removeClass("hide");
            $("#invoice_style").val($(this).attr("data-value"));
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        $("#enable_background_image_for_invoice_pdf").click(function() {
            if ($(this).is(":checked")) {
                $(".related_to_pdf_background_setting").removeClass("hide");
            } else {
                $(".related_to_pdf_background_setting").addClass("hide");
            }
        });

        var uploadUrl = "<?php echo get_uri("uploader/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("uploader/validate_file"); ?>";

        var dropzone = attachDropzoneWithForm("#invoice-settings-form", uploadUrl, validationUrl, {
            maxFiles: 1
        });

        $("#invoice_number_format").select2().on("change", function() {
            var value = $(this).val();

            // Check if the value includes "YEAR" and show/hide the year section accordingly
            if (value.includes("YEAR")) {
                $("#invoice-number-format-year-section").removeClass("hide");
            } else {
                $("#invoice-number-format-year-section").addClass("hide");
            }

            // Check if the value does not include "YEAR" and show/hide the initial number section accordingly
            if (!value.includes("YEAR")) {
                $("#initial-number-of-the-invoice").removeClass("hide");
            } else {
                if ($("#reset_invoice_number_every_year").is(":checked")) {
                    $("#initial-number-of-the-invoice").addClass("hide");
                } else {
                    $("#initial-number-of-the-invoice").removeClass("hide");
                }
            }
        });

        $("#reset_invoice_number_every_year").click(function() {
            if ($("#invoice_number_format").val().includes("YEAR") && $(this).is(":checked")) {
                $("#initial-number-of-the-invoice").addClass("hide");
            } else {
                $("#initial-number-of-the-invoice").removeClass("hide");
            }
        });
    });
</script>