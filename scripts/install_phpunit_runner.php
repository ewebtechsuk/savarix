<?php
$targetDir = __DIR__ . '/../vendor/bin';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$target = $targetDir . '/phpunit';
$source = __DIR__ . '/phpunit-runner.php';
copy($source, $target);
chmod($target, 0755);
