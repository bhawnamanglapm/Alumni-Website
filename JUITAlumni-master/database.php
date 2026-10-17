<?php

function alumni_response($status, $title, $message)
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . $safeTitle . '</title><main><h1>' . $safeTitle . '</h1><p>' . $safeMessage . '</p>';
    echo '<a href="index.php">Back to the alumni site</a></main></html>';
    exit;
}

function alumni_require_post()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        alumni_response(405, 'Method not allowed', 'Submit this form using the page form.');
    }
}

function alumni_form_values($input, $limits)
{
    $values = array();
    foreach ($limits as $field => $maxBytes) {
        if (!isset($input[$field]) || !is_string($input[$field])) {
            return false;
        }
        $value = trim($input[$field]);
        if ($value === '' || strlen($value) > $maxBytes) {
            return false;
        }
        $values[$field] = $value;
    }
    return $values;
}

function alumni_db()
{
    $host = getenv('ALUMNI_DB_HOST');
    $user = getenv('ALUMNI_DB_USER');
    $password = getenv('ALUMNI_DB_PASSWORD');
    $database = getenv('ALUMNI_DB_NAME');
    $port = getenv('ALUMNI_DB_PORT');

    if ($host === false || $host === '' || $user === false || $user === '' ||
        $password === false || $database === false || $database === '') {
        throw new RuntimeException('Database configuration is incomplete.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($host, $user, $password, $database, $port === false || $port === '' ? 3306 : (int) $port);
    $connection->set_charset('utf8mb4');
    return $connection;
}
