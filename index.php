<?php

require_once('lib/limonade.php');
require_once('config.inc.php');

function configure()
{
  option('env', ENV_DEVELOPMENT);
}

function before() {
    layout('layouts/default.php');
}

dispatch('/', function() { return redirect_to('streams'); });

dispatch('/streams', function() {         
    //Filter for enabled VOD streams
    $streams = getEnabledVODStreams(getAllMedia());
    set('streams', $streams);
    return render('templates/streams.html.php');
});

dispatch('/streams/search/*', function() {         
    header('Content-Type: text/json');
    //Filter for enabled VOD streams matching the search term
    $streams = getEnabledVODStreams(getAllMedia(), params(0));
    set('streams', $streams);
    layout(null);
    return json_encode(array('streams' => $streams));
});

dispatch('/streams/add/:action/:path', function () {
    if((params('action') != 'use') || (strpos(realpath(params('path')), MEDIADIR) === 0)) {
        if(strpos(realpath(params('path')), MEDIADIR) != 0) set('path',''); //Invalid path traversal!
        else set_or_default('path', trim(params('path'),'/'), '');
        
        set('files', array_slice(scandir(MEDIADIR  . '/' . params('path')),2));
    }
    else {
        set('addFile', params('path'));
    }
    
    return render('templates/add.html.php');
});

dispatch_post('/streams/add/use/*', function() {
    shell_exec('scripts/add_vod.expect ' . escapeshellcmd($_POST['name']) . ' ' . escapeshellcmd(realpath(MEDIADIR . '/'.$_POST['file'])) );
    return redirect_to('streams');
});

dispatch('/streams/remove/:name', function() {
    shell_exec('scripts/remove_vod.expect ' . escapeshellcmd(params('name')));    
    return redirect_to('streams');
});

run();

//Auxiliary functions

function getEnabledVODStreams($media, $searchTerm = '') {
    $result = array();
    foreach($media as $sName => $sParams) {
        if($sParams['type'] == 'vod' && $sParams['enabled'] == 'yes') {
            if($searchTerm == '' || strpos($sName, $searchTerm) !== false)
            $result[] = $sName;
        }
    }
    return $result;
}

function getAllMedia() {
    $output = shell_exec("scripts/get_vodlist.expect");
    $lines = explode("\r\n", $output);
    $allMedia = array();
    $currentKey = "";
    //Assemble media information
    foreach($lines as $l) {
        if(preg_match('#^\s{8}(\w+)$#i',$l, $tokens)) {
            $currentKey = $tokens[1];
            $allMedia[$currentKey] = array();
        }
        else if(preg_match('#^\s{12}(\w+)\s\:\s(\w+)$#i',$l, $tokens)) $allMedia[$currentKey][$tokens[1]] = $tokens[2]; 
    }
    return $allMedia;
}
