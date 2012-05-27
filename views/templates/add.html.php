<h2>Add a new stream:</h2>
<?php if(!isset($addFile)): ?>
<h3>Step 1: Choose a file</h3>
<div class="filebrowse">
    <p>
    <?php
        if($path !== "")
            echo '<a href="/streams/add/browse/'. substr($path,0,strrpos('/',$path)).'">Back to parent folder</a>';
    ?>
    </p>
    <ul>
    <?php foreach($files as $f): ?>
        <li>
        <?php
            if(is_file(getcwd() . '/media/admin/' . $f)) echo '<a href="/streams/add/use/' . $f . '">' . $f . '</a>';
            else echo '<a href="/streams/add/browse/' . $f . '/">&raquo; ' . $f . '</a>';
        ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php else: ?>
    <h3>Step 2: Choose a name</h3>
    <form method="post">
        <input type="hidden" name="file" value="<?php echo $addFile; ?>" />
        <p>
            <label for="name">Name of the stream:</label>
            <input name="name" id="name" />
        </p>
        <button type="submit">Add</button>
    </form>
<?php endif; ?>