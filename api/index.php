<?php
$dir = 'text-dir';
if (!is_dir($dir)) {
  mkdir($dir, 0755, true);
}
file_put_contents($dir . '/test.txt', 'Hello World!');
