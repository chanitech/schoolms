<?php
$directories = ['app', 'routes'];

foreach ($directories as $dir) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $lines = file($file->getRealPath());
            foreach ($lines as $num => $line) {
                // Match function type-hints with lowercase 'request'
                if (preg_match('/\b(public function .*?\()request\b/', $line, $matches)) {
                    $fixedLine = str_replace('request', 'Request', $line);
                    echo "Found in {$file->getRealPath()} on line " . ($num+1) . ":\n";
                    echo "Original: " . $line;
                    echo "Suggested fix: " . $fixedLine . "\n\n";
                }

                // Match closures in routes
                if (preg_match('/function\s*\(request\b/', $line)) {
                    $fixedLine = str_replace('request', 'Request', $line);
                    echo "Found closure in {$file->getRealPath()} on line " . ($num+1) . ":\n";
                    echo "Original: " . $line;
                    echo "Suggested fix: " . $fixedLine . "\n\n";
                }
            }
        }
    }
}
