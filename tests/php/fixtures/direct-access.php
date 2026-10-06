<?php
require dirname(__DIR__, 3) . '/' . json_decode($argv[1], true)['file'];
echo 'unexpected continuation';
exit(1);
