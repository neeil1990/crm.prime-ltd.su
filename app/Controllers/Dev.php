<?php


namespace App\Controllers;


use App\Libraries\Imap;

class Dev extends App_Controller
{
    public function index() {
        $imap = new Imap();
        $imap->run_imap();
    }
}
