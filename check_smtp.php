<?php
require 'H:/XAMPP/htdocs/alphaspacepro.online/vendor/autoload.php';
$app = require_once 'H:/XAMPP/htdocs/alphaspacepro.online/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$accounts = App\Models\EmailAccount::where('status','active')->select('id','email','smtp_host','smtp_port','smtp_encryption','smtp_username','sync_enabled')->get();
foreach ($accounts as $a) {
    echo $a->id . ' | ' . $a->email . ' | SMTP: ' . $a->smtp_host . ':' . $a->smtp_port . '/' . $a->smtp_encryption . ' | User: ' . ($a->smtp_username ?: 'none') . ' | sync: ' . ($a->sync_enabled ? 'yes' : 'no') . PHP_EOL;
}
