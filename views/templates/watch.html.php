<?php if($stream != false): ?>
<embed type="application/x-vlc-plugin" 
    pluginspage="http://www.videolan.org" 
    width="100%" 
    height="100%"
    target="rtsp://<?php echo $_SERVER['HTTP_HOST']; ?>:554/<?php echo $stream; ?>" />
<?php else: ?>
    <div class="alert alert-error">
        <a class="close" data-dismiss="alert" href="#">×</a>
        <h4 class="alert-heading">Whoops!</h4>    
        <p>Unknown stream</p>
    </div>
<?php endif; ?>