<?php
echo '<pre>';
print_r($_ENV);
echo '</pre>';

echo getenv('PATH');

echo 'User: ' . get_current_user() . PHP_EOL;
?>