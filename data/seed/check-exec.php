<?php
$fns = ['exec', 'shell_exec', 'passthru', 'proc_open', 'popen', 'system'];
foreach ($fns as $fn) {
    echo $fn . ': ' . (function_exists($fn) ? 'available' : 'DISABLED') . "\n";
}
echo "disable_functions ini: " . ini_get('disable_functions') . "\n";