<?php
/**
 * Simple test runner for CI environments
 * Bypasses Pest CLI issues by running tests directly
 */

require_once 'vendor/autoload.php';

echo "🧪 Running tests with custom runner...\n";

// Set environment
putenv('APP_ENV=testing');

// Basic test validation
$testFiles = glob('tests/**/*Test.php');
$testCount = count($testFiles);

echo "Found {$testCount} test files\n";

if ($testCount === 0) {
    echo "❌ No test files found\n";
    exit(1);
}

// For now, just validate that the test files can be loaded
$success = true;
foreach ($testFiles as $testFile) {
    if (!file_exists($testFile)) {
        echo "❌ Test file not found: {$testFile}\n";
        $success = false;
    }
}

if ($success) {
    echo "✅ Test validation passed\n";
    
    // Try to run Pest with the most basic command
    $command = './vendor/bin/pest tests/ --no-configuration --colors=never 2>&1';
    $output = [];
    $returnVar = 0;
    
    exec($command, $output, $returnVar);
    
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($returnVar === 0) {
        echo "🎉 All tests passed!\n";
        exit(0);
    } else {
        echo "❌ Tests failed with exit code {$returnVar}\n";
        exit(1);
    }
} else {
    echo "❌ Test validation failed\n";
    exit(1);
}
