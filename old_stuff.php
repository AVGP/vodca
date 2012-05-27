<!DOCTYPE html>
<html>
    <head>
        <title>VODoo - Video on demand center</title>
    </head>
    <body>
    <ul>
<?php
    $output = shell_exec("./mk_vod.getexpect");
    $lines = explode("\r\n", $output);
    $streams = array();
    $currentKey = "";
    foreach($lines as $l) {
        if(preg_match('#^\s{8}(\w+)$#i',$l, $tokens)) {
            $currentKey = $tokens[1];
            $streams[$currentKey] = array();
        }
        else if(preg_match('#^\s{12}(\w+)\s\:\s(\w+)$#i',$l, $tokens)) $streams[$currentKey][$tokens[1]] = $tokens[2]; 
    }
    
    foreach($streams as $sName => $sParams) {
        if($sParams['type'] == 'vod' && $sParams['enabled'] == 'yes') 
            echo '<li><a href="rtsp://v220120583698239.yourvserver.net:544/' . $sName . '" target="_blank">' . $sName . '</a></li>';
    }
?>
    </ul>
</body>
</html>