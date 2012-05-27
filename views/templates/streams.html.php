<h2>Available Streams</h2>
<div>
    <form style="float:right">
        <label for="search">Search:</label>
        <input id="search" />
        <button type="submit" class="btn btn-primary">Go</button>
    </form>
    <a href="/streams/add" class="btn">Add new stream</a>
</div>
<ul id="streams">
    <?php foreach($streams as $stream): ?>
    <li>
        <a href="rtsp://<?php echo $_SERVER['HTTP_HOST']; ?>:554/<?php echo $stream; ?>" target="_blank"><?php echo $stream; ?></a>&nbsp;&nbsp;
        <a href="/streams/remove/<?php echo $stream; ?>" onclick="if(!window.confirm('Really delete this stream?')) return false;">[Delete]</a>
    </li>
    <?php endforeach; ?>
</ul>
<script type="text/javascript">
$(document).ready(function() {
    $("#search").keyup(function() {
        $.getJSON("/streams/search/" + $("#search").val(), function(result) {
            $("ul#streams").empty();
            for(var i=0; i<result.streams.length;i++) 
                $("ul#streams").append('<li>' 
                    + '<a href="rtsp://<?php echo $_SERVER['HTTP_HOST']; ?>:554/'+result.streams[i]+' target="_blank">'+result.streams[i]+'</a>'
                    + '&nbsp;&nbsp;<a href="/streams/remove/' + result.streams[i] + '" onclick="if(!window.confirm(\"Really delete this stream?\")) return false;">[Delete]</a>'
                    + '</li>');
        });
    });
});
</script>
