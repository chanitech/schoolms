<?php
$directory = 'routes';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $lines = file($file->getRealPath());
        foreach ($lines as $num => $line) {
            // Match closure with lowercase 'request'
            if (preg_match('/function\s*\(request\b/', $line)) {
                $fixedLine = str_replace('request', 'Request', $line);
                echo "Found lowercase 'request' in closure in {$file->getRealPath()} on line " . ($num+1) . ":\n";
                echo "Original: " . $line;
                echo "Suggested fix: " . $fixedLine . "\n\n";
            }

            // Match controller method type-hints with lowercase 'request'
            if (preg_match('/public function .*?\(request\b/', $line)) {
                $fixedLine = str_replace('request', 'Request', $line);
                echo "Found lowercase 'request' type-hint in {$file->getRealPath()} on line " . ($num+1) . ":\n";
                echo "Original: " . $line;
                echo "Suggested fix: " . $fixedLine . "\n\n";
            }
        }
    }
}
