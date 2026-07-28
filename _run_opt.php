<?php $output=null;$ret=null;exec("C:\php\php.exe C:\roundcube\artisan optimize 2>&1",$output,$ret);echo implode("\n",$output)."\nExit code: $ret";
