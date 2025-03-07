<?php

namespace App\Models;

class Invoice_payments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'invoice_payments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $payment_methods_table = $this->db->prefixTable('payment_methods');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $invoice_payments_table.id=$id";
        }

        $invoice_id = $this->_get_clean_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $invoice_payments_table.invoice_id=$invoice_id";
        }

        $order_id = $this->_get_clean_value($options, "order_id");
        if ($order_id) {
            $where .= " AND $invoice_payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.order_id=$order_id)";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $invoices_table.client_id=$client_id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $invoices_table.project_id=$project_id";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $invoice_payments_table.payment_method_id=$payment_method_id";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($invoice_payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $currency = $this->_get_clean_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        $sql = "SELECT $invoice_payments_table.*, $invoices_table.client_id, $invoices_table.display_id, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id limit 1) AS currency_symbol, $payment_methods_table.title AS payment_method_title
        FROM $invoice_payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$invoice_payments_table.invoice_id
        LEFT JOIN $payment_methods_table ON $payment_methods_table.id = $invoice_payments_table.payment_method_id
        WHERE $invoice_payments_table.deleted=0 AND $invoices_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_yearly_payments_chart($year, $currency = "", $project_id = 0) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        if ($currency) {
            $where = $this->_get_clients_of_currency_query($currency, $invoices_table, $clients_table);
        }

        if ($project_id) {
            $where .= " AND $payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id=$project_id)";
        }

        $payments = "SELECT SUM($payments_table.amount) AS total, MONTH($payments_table.payment_date) AS month,
            (SELECT $clients_table.currency FROM $clients_table WHERE $clients_table.id=(
                SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id
                )
            ) AS currency
            FROM $payments_table
            LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
            WHERE $payments_table.deleted=0 AND YEAR($payments_table.payment_date)= $year AND $invoices_table.deleted=0 $where
            GROUP BY MONTH($payments_table.payment_date), currency";

        return $this->db->query($payments)->getResult();
    }

    function get_used_projects($type) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $projects_table = $this->db->prefixTable('projects');
        $expenses_table = $this->db->prefixTable('expenses');

        $payments_where = "SELECT $invoices_table.project_id FROM $invoices_table WHERE $invoices_table.deleted=0 AND $invoices_table.project_id!=0 AND $invoices_table.id IN(SELECT $payments_table.invoice_id FROM $payments_table WHERE $payments_table.deleted=0 GROUP BY $payments_table.invoice_id) GROUP BY $invoices_table.project_id";
        $expenses_where = "SELECT $expenses_table.project_id FROM $expenses_table WHERE $expenses_table.deleted=0 AND $expenses_table.project_id!=0 GROUP BY $expenses_table.project_id";

        $where = "";
        if ($type == "all") {
            $where = " AND $projects_table.id IN($payments_where) OR $projects_table.id IN($expenses_where)";
        } else if ($type == "payments") {
            $where = " AND $projects_table.id IN($payments_where)";
        } else if ($type == "expenses") {
            $where = " AND $projects_table.id IN($expenses_where)";
        }

        $sql = "SELECT $projects_table.id, $projects_table.title 
            FROM $projects_table 
            WHERE $projects_table.deleted=0 $where
            GROUP BY $projects_table.id";

        return $this->db->query($sql);
    }

    function get_yearly_summary_details($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $payments_table.payment_method_id=$payment_method_id";
        }

        $selected_currency = get_array_value($options, "currency");
        $default_currency = get_setting("default_currency");
        $currency = $selected_currency ? $selected_currency : get_setting("default_currency");
        $currency = $this->db->escapeString($currency);

        $where .= ($currency == $default_currency) ? " AND ($clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL)" : " AND $clients_table.currency='$currency'";

        $sql = "SELECT COUNT($payments_table.id) AS payment_count, SUM($payments_table.amount) AS amount, MONTH($payments_table.payment_date) AS month, $clients_table.currency, $clients_table.currency_symbol
        FROM $payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
        LEFT JOIN $clients_table ON $clients_table.id=(SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id LIMIT 1)
        WHERE $payments_table.deleted=0 $where
        GROUP BY MONTH($payments_table.payment_date)";

        return $this->db->query($sql);
    }

    function get_clients_summary_details($options = array()) {
        $payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($payments_table.payment_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $payment_method_id = $this->_get_clean_value($options, "payment_method_id");
        if ($payment_method_id) {
            $where .= " AND $payments_table.payment_method_id=$payment_method_id";
        }

        $selected_currency = get_array_value($options, "currency");
        $default_currency = get_setting("default_currency");
        $currency = $selected_currency ? $selected_currency : get_setting("default_currency");
        $currency = $this->db->escapeString($currency);

        $where .= ($currency == $default_currency) ? " AND ($clients_table.currency='$default_currency' OR $clients_table.currency='' OR $clients_table.currency IS NULL)" : " AND $clients_table.currency='$currency'";

        $sql = "SELECT COUNT($payments_table.id) AS payment_count, SUM($payments_table.amount) AS amount, $invoices_table.client_id, $clients_table.company_name AS client_name, $clients_table.currency, $clients_table.currency_symbol
        FROM $payments_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$payments_table.invoice_id
        LEFT JOIN $clients_table ON $clients_table.id=(SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$payments_table.invoice_id LIMIT 1)
        WHERE $payments_table.deleted=0 $where
        GROUP BY $invoices_table.client_id";

        return $this->db->query($sql);
    }

}
