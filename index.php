<?php

require_once('lib/limonade.php');

function configure(Security)
{
  option('env', ENV_DEVELOPMENT);
}

function before() {
    layout('layouts/default.php');
}

dispatch('/', function() { return redirect_to('streams'); });

dispatch('/streams', function() {         
    //Filter for enabled VOD streams gpg
    $streams = getEnabledVODStreams(getAllMedia());
    set('streams', $streams);
    return render('templates/streams.html.php');
});

dispatch('/streams/watch/:name', function() {
    set_or_default('stream', params('name'), false);
    return render('templates/watch.html.php');
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
    if((params('action') != 'use') || (strpos(realpath(params('path')), getcwd().'/media') === 0)) {
        if(strpos(realpath(params('path')), getcwd().'/media') != 0) set('path',''); //Invalid path traversal!
        else set_or_default('path', trim(params('path'),'/'), '');
        
        set('files', array_slice(scandir('media/admin/' . params('path')),2));
    }
    else {
        set('addFile', params('path'));
    }
    
    return render('templates/add.html.php');
});

dispatch_post('/streams/add/use/*', function() {
    $name = escapeshellcmd($_POST['name']);
    $source = escapeshellcmd(realpath('media/admin/'.$_POST['file']));
    addStream($name, $source);
    return redirect_to('streams');
});

dispatch('/streams/remove/:name', function() {
    shell_exec('scripts/remove_vod.expect ' . escapeshellcmd(params('name')));    
    $db = new SQlite3('streams.db');
    $db->exec('DELETE FROM streams WHERE name = "'. $db->escapeString(params('name')) . '"');
    $db->close();
    return redirect_to('streams');
});

dispatch('/streams/sync', function() {
    $db = new SQLite3('streams.db');
    $dbStreamsResult = $db->query('SELECT * FROM streams');
    $dbStreams = array();
    while(($row = $dbStreamsResult->fetchArray()) !== false) $dbStreams[$row['name']] = $row;    
    $existingStreams = getEnabledVODStreams(getAllMedia());
    
    //Sync DB -> Streams
    foreach($dbStreams as $stream) {
        if(!in_array($stream['name'], $existingStreams)) addStream($stream['name'], $stream['source']);
    }

    $db->close();
    return redirect_to('streams');
});

run();

//Auxiliary functions

function addStream($name, $source) {
    shell_exec('scripts/add_vod.expect ' . $name . ' ' . $source );
    $db = new SQlite3('streams.db');
    $db->exec('INSERT INTO streams(name, source) VALUES("'. $db->escapeString($name) . '","' . $db->escapeString($source) . '")');
    $db->close();    
}

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
