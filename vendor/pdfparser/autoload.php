<?php
/**
 * Simple autoloader for PDFParser library
 */

spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $class = str_replace('\\', '/', $class);
    
    // Check if it's a PDFParser class
    if (strpos($class, 'Smalot/PdfParser') === 0) {
        $file = __DIR__ . '/src/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
