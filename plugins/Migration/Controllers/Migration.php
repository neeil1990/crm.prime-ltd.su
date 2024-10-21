<?php
namespace Migration\Controllers;

class Migration extends \App\Controllers\Security_Controller
{
    public function index()
    {
        include PLUGINPATH . "Migration/install/do_install.php";

        echo $this->template->view('Migration\Views\update\index');
    }
}
