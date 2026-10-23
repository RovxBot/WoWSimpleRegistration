<?php

$root = dirname(__DIR__);
$directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
$filter = new RecursiveCallbackFilterIterator($directory, static function ($file) {
    return !in_array($file->getFilename(), ['.git', 'vendor', '.agents', '.codex', '.aws'], true);
});
$count = 0;
foreach (new RecursiveIteratorIterator($filter) as $file) {
    if ($file->isFile() && preg_match('/\.php(?:\.sample)?$/', $file->getFilename())) {
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $status);
        if ($status !== 0) {
            throw new RuntimeException(implode("\n", $output));
        }
        $count++;
        $output = [];
    }
}
echo "Syntax checked $count PHP files on PHP " . PHP_VERSION . ".\n";
