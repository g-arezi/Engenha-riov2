<?php
header('Content-Type: application/json');

error_log('=== TEST UPLOAD DEBUG ===');
error_log('METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('FILES: ' . json_encode($_FILES));
error_log('POST: ' . json_encode($_POST));
error_log('Headers: ' . json_encode(getallheaders()));

echo json_encode([
    'success' => true,
    'method' => $_SERVER['REQUEST_METHOD'],
    'files' => $_FILES,
    'post' => $_POST,
    'headers' => getallheaders()
]);
?>
