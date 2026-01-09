#!/usr/bin/env php
<?php
/**
 * Quick Test Validation Script
 * 
 * Displays:
 * - Total test count
 * - Feature vs Unit breakdown
 * - Test files summary
 * - Quick status
 */

echo "\n╔════════════════════════════════════════════════╗\n";
echo "║        TEST SUITE VALIDATION REPORT            ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

$testsDir = __DIR__;

// Count Feature Tests
$featureTests = glob($testsDir . '/Feature/*.php');
$featureCount = 0;
foreach ($featureTests as $file) {
    $content = file_get_contents($file);
    preg_match_all('/public function test_/', $content, $matches);
    $featureCount += count($matches[0]);
}

// Count Unit Tests
$unitTests = glob($testsDir . '/Unit/*.php');
$unitCount = 0;
foreach ($unitTests as $file) {
    $content = file_get_contents($file);
    preg_match_all('/public function test_/', $content, $matches);
    $unitCount += count($matches[0]);
}

$totalTests = $featureCount + $unitCount;

// Display results
echo "📊 TEST COUNT BREAKDOWN:\n";
echo "   Feature Tests: " . count($featureTests) . " files, $featureCount tests\n";
echo "   Unit Tests:    " . count($unitTests) . " files, $unitCount tests\n";
echo "   ────────────────────────────\n";
echo "   Total:         " . (count($featureTests) + count($unitTests)) . " files, $totalTests tests\n\n";

// List Feature tests
echo "📋 FEATURE TEST FILES:\n";
foreach ($featureTests as $file) {
    $basename = basename($file);
    $content = file_get_contents($file);
    preg_match_all('/public function test_/', $content, $matches);
    $count = count($matches[0]);
    echo "   ✓ $basename ($count tests)\n";
}

echo "\n📋 UNIT TEST FILES:\n";
foreach ($unitTests as $file) {
    $basename = basename($file);
    $content = file_get_contents($file);
    preg_match_all('/public function test_/', $content, $matches);
    $count = count($matches[0]);
    echo "   ✓ $basename ($count tests)\n";
}

echo "\n✅ STATUS: Ready to run!\n";
echo "🚀 Command: ./vendor/bin/phpunit\n\n";

?>
