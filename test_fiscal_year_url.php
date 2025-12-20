<?php
// Test direct access to fiscal year URL
echo "<h1>Testing Fiscal Year URL Access</h1>";

// Test if we can access the fiscal year URL directly
$url = "http://" . $_SERVER['HTTP_HOST'] . "/tapstemco/en/setting/fiscal_year_list";

echo "<p>Testing URL: <a href='$url' target='_blank'>$url</a></p>";

// Try to make a curl request to test the URL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']); // Pass current session

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Response Code: $httpCode</p>";

if ($httpCode == 200) {
    echo "<p style='color: green;'>✓ URL is accessible (HTTP 200)</p>";
    if (strpos($response, 'Fiscal Year Management') !== false) {
        echo "<p style='color: green;'>✓ Fiscal year page loaded successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Page loaded but fiscal year content not found</p>";
        echo "<p>First 500 characters of response:</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
    }
} else {
    echo "<p style='color: red;'>✗ URL not accessible (HTTP $httpCode)</p>";
    echo "<p>Error response:</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
}

echo "<h2>Alternative Test</h2>";
echo "<p>If the above doesn't work, try accessing the fiscal year page directly by clicking the link above, or:</p>";
echo "<ol>";
echo "<li>Go to your application</li>";
echo "<li>Make sure you're logged in as admin</li>";
echo "<li>Look for 'Settings' in the left menu</li>";
echo "<li>Click on 'Settings' to expand it</li>";
echo "<li>Look for 'Fiscal Year Management' in the submenu</li>";
echo "</ol>";

echo "<p><a href='" . "http://" . $_SERVER['HTTP_HOST'] . "/tapstemco'>← Back to Application</a></p>";
?>
