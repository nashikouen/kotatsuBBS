<?php
$command = 'LD_LIBRARY_PATH=/usr/local/lib:/usr/X11R6/lib /usr/local/bin/ffmpeg -version';

// Execute the command
exec($command . ' 2>&1', $output, $return_var);

// Output the results
echo "<pre>";
print_r($output);
echo "</pre>";

if ($return_var !== 0) {
    echo "FFmpeg failed to execute with error code: $return_var";
}
?>
