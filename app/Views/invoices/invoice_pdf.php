<div style=" margin: auto;">
    <?php
    $colspan = 3;
    $show_taxable = false;
    if (get_setting('taxable_column') == "always_show") {
        $show_taxable = true;
        $colspan = 4;
    } else if (get_setting('taxable_column') == "never_show") {
        $show_taxable = false;
    } else {
        $taxable_fields = array();
        foreach ($invoice_items as $item) {
            $taxable_fields[] = $item->taxable;
        }
        if (count(array_unique($taxable_fields)) == 2) {
            $show_taxable = true;
            $colspan = 4;
        }
    }


    $color = get_setting("invoice_color");
    if (!$color) {
        $color = "#2AA384";
    }
    $invoice_style = get_setting("invoice_style");
    $data = array(
        "client_info" => $client_info,
        "color" => $color,
        "invoice_info" => $invoice_info
    );

    if ($invoice_style === "style_3") {
        echo view('invoices/invoice_parts/header_style_3.php', $data);
    } else if ($invoice_style === "style_2") {
        echo view('invoices/invoice_parts/header_style_2.php', $data);
    } else {
        echo view('invoices/invoice_parts/header_style_1.php', $data);
    }

    $item_background = get_setting("invoice_item_list_background");

    $discount_row = '<tr>
                        <td colspan="' . $colspan . '" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: ' . $item_background . ';">' . to_currency($invoice_total_summary->discount_total, $invoice_total_summary->currency_symbol) . '</td>
                    </tr>';

    $total_after_discount_row = '<tr>
                                    <td colspan="' . $colspan . '" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: ' . $item_background . ';">' . to_currency($invoice_total_summary->invoice_subtotal - $invoice_total_summary->discount_total, $invoice_total_summary->currency_symbol) . '</td>
                                </tr>';
    ?>
</div>

<br />

<table class="table-responsive" style="width: 100%;">            
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
        <th style="width: 45%; border-right: 1px solid #eee;"> <?php echo app_lang("item"); ?> </th>
        <th style="text-align: center;  width: <?php echo $show_taxable ? '12%' : '15%'; ?>; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
        <th style="text-align: right;  width:<?php echo $show_taxable ? '12%' : '20%'; ?>; border-right: 1px solid #eee;"> <?php echo app_lang("rate"); ?></th>
        <?php if ($show_taxable) { ?>
            <th style="text-align: center; width: 12%;  border-right: 1px solid #eee; "> <?php echo app_lang("taxable"); ?></th>
        <?php } ?>
        <th style="text-align: right;  width: <?php echo $show_taxable ? '19%' : '20%'; ?>; "> <?php echo app_lang("total"); ?></th>
    </tr>
    <?php
    foreach ($invoice_items as $item) { ?>

        <tr style="background-color: <?php echo $item_background; ?>;">
            <td style="width: 45%; border: 1px solid #fff; padding: 10px;"><?php echo $item->title; ?>
                <br />
                <span style="color: #888; font-size: 90%;"><?php echo nl2br($item->description ? $item->description : ""); ?></span>
            </td>
            <td style="text-align: center; width: <?php echo $show_taxable ? '12%' : '15%'; ?>; border: 1px solid #fff;"> <?php echo $item->quantity . " " . $item->unit_type; ?></td>
            <td style="text-align: right; width: <?php echo $show_taxable ? '12%' : '20%'; ?>; border: 1px solid #fff;"> <?php echo to_currency($item->rate, $item->currency_symbol); ?></td>
            <?php if ($show_taxable) { ?>
                <td style="text-align: center; width: 12%; border: 1px solid #fff; "> <?php echo $item->taxable ? app_lang("yes") : app_lang("no"); ?></td>
            <?php } ?>
            <td style="text-align: right; width: <?php echo $show_taxable ? '19%' : '20%'; ?>; border: 1px solid #fff;"> <?php echo to_currency($item->total, $item->currency_symbol); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo app_lang("sub_total"); ?></td>
        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: <?php echo $item_background; ?>;">
            <?php echo to_currency($invoice_total_summary->invoice_subtotal, $invoice_total_summary->currency_symbol); ?>
        </td>
    </tr>
    <?php
    if ($invoice_total_summary->discount_total && $invoice_total_summary->discount_type == "before_tax") {
        echo $discount_row . $total_after_discount_row;
    }
    ?>    
    <?php if ($invoice_total_summary->tax) { ?>
        <tr>
            <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo $invoice_total_summary->tax_name; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: <?php echo $item_background; ?>;">
                <?php echo to_currency($invoice_total_summary->tax, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($invoice_total_summary->tax2) { ?>
        <tr>
            <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo $invoice_total_summary->tax_name2; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: <?php echo $item_background; ?>;">
                <?php echo to_currency($invoice_total_summary->tax2, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($invoice_total_summary->tax3) { ?>
        <tr>
            <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo $invoice_total_summary->tax_name3; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: <?php echo $item_background; ?>;">
                <?php echo to_currency($invoice_total_summary->tax3, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php
    if ($invoice_total_summary->discount_total && $invoice_total_summary->discount_type == "after_tax") {
        echo $discount_row;
    }
    ?> 
    <?php if ($invoice_total_summary->total_paid) { ?>     
        <tr>
            <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo app_lang("paid"); ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: <?php echo $item_background; ?>;">
                <?php echo to_currency($invoice_total_summary->total_paid, $invoice_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo app_lang("balance_due"); ?></td>
        <td style="text-align: right; width: 20%; background-color: <?php echo $color; ?>; color: #fff;">
            <?php echo to_currency($invoice_total_summary->balance_due, $invoice_total_summary->currency_symbol); ?>
        </td>
    </tr>
</table>
<?php if ($invoice_info->note) { ?>
    <br />
    <br />
    <div style="border-top: 1px solid #f2f4f6; color:#444; padding:0 0 20px 0;"><br /><?php echo nl2br(process_images_from_content($invoice_info->note)); ?></div>
<?php } else { ?> <!-- use table to avoid extra spaces -->
    <br /><br /><table class="invoice-pdf-hidden-table" style="border-top: 1px solid #f2f4f6; margin: 0; padding: 0; display: block; width: 100%; height: 10px;"></table>
<?php } ?>
<span style="color:#444; line-height: 14px;">
    <?php echo get_setting("invoice_footer"); ?>
</span>

