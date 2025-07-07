<?php
/**
 * Debug version to test plugin activation
 */

// Test 1: Basic PHP
echo "PHP is working\n";

// Test 2: WordPress functions
if (function_exists('add_action')) {
    echo "WordPress functions available\n";
}

// Test 3: Include files
$test_files = array(
    'includes/class-ai-share-buttons.php',
    'includes/class-ai-share-buttons-installer.php',
    'includes/class-ai-share-buttons-admin.php',
    'includes/class-ai-share-buttons-frontend.php',
    'includes/class-ai-share-buttons-analytics.php',
    'includes/functions.php'
);

foreach ($test_files as $file) {
    $full_path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($full_path)) {
        echo "File exists: $file\n";
        require_once $full_path;
        echo "File loaded: $file\n";
    } else {
        echo "File missing: $file\n";
    }
}

// Test 4: Class existence
$classes = array(
    'AI_Share_Buttons',
    'AI_Share_Buttons_Installer',
    'AI_Share_Buttons_Admin',
    'AI_Share_Buttons_Frontend',
    'AI_Share_Buttons_Analytics'
);

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "Class exists: $class\n";
    } else {
        echo "Class missing: $class\n";
    }
}

echo "Debug complete\n";