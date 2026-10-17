<?php

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

if (getenv('ALUMNI_TEST_MODE') !== '1' || getenv('ALUMNI_DB_NAME') !== 'alumni_test') {
    throw new RuntimeException('Integration tests require the explicitly named alumni_test database.');
}

$database = new mysqli(
    getenv('ALUMNI_DB_HOST'),
    getenv('ALUMNI_DB_USER'),
    getenv('ALUMNI_DB_PASSWORD'),
    getenv('ALUMNI_DB_NAME'),
    (int) getenv('ALUMNI_DB_PORT')
);
$database->set_charset('utf8mb4');
$database->query('DROP TABLE IF EXISTS contact');
$database->query('DROP TABLE IF EXISTS student');
$schema = file_get_contents(__DIR__ . '/../JUITAlumni-master/database/schema.sql');
foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
    $database->query($statement);
}
$database->close();

$root = realpath(__DIR__ . '/../JUITAlumni-master');
$port = 19000 + (getmypid() % 1000);
$command = array(PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $root);
$pipes = array();
$server = proc_open($command, array(
    0 => array('pipe', 'r'),
    1 => array('pipe', 'w'),
    2 => array('pipe', 'w'),
), $pipes, $root);

if (!is_resource($server)) {
    throw new RuntimeException('Could not start the PHP test server.');
}

$baseUrl = 'http://127.0.0.1:' . $port;
$request = function ($path, $method, $fields) use ($baseUrl) {
    $handle = curl_init($baseUrl . $path);
    curl_setopt_array($handle, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_TIMEOUT => 5,
    ));
    $body = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $error = curl_error($handle);
    curl_close($handle);
    if ($body === false) {
        throw new RuntimeException('HTTP request failed: ' . $error);
    }
    return array($status, $body);
};

try {
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $handle = curl_init($baseUrl . '/index.php');
        curl_setopt_array($handle, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1));
        curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        if ($status > 0) {
            $ready = true;
            break;
        }
        usleep(100000);
    }
    check($ready, 'PHP test server did not start.');

    $registration = array(
        'roll' => 'R-2026-01',
        'name' => "O'Reilly'); DROP TABLE student; --",
        'email' => 'alumni@example.test',
        'mob' => '+91 1234567890',
        'address' => 'Test address',
    );
    list($status, $body) = $request('/two.php', 'POST', $registration);
    check($status === 200 && strpos($body, 'Registration received') !== false, 'Valid registration was not accepted.');

    $database = new mysqli(
        getenv('ALUMNI_DB_HOST'),
        getenv('ALUMNI_DB_USER'),
        getenv('ALUMNI_DB_PASSWORD'),
        getenv('ALUMNI_DB_NAME'),
        (int) getenv('ALUMNI_DB_PORT')
    );
    $database->set_charset('utf8mb4');
    $savedName = $database->query('SELECT name FROM student')->fetch_row();
    check($savedName && $savedName[0] === $registration['name'], 'Registration input was not stored literally.');
    check((int) $database->query('SELECT COUNT(*) FROM student')->fetch_row()[0] === 1, 'Registration did not save exactly one row.');

    list($status) = $request('/two.php', 'POST', array_merge($registration, array('email' => 'not-an-email')));
    check($status === 400, 'Invalid registration email was not rejected.');
    list($status) = $request('/two.php', 'POST', array_merge($registration, array('name' => array('unexpected'))));
    check($status === 400, 'Non-string registration input was not rejected.');
    list($status) = $request('/two.php', 'GET', array());
    check($status === 405, 'Non-POST registration request was not rejected.');

    $message = "Hello'); DROP TABLE contact; --";
    list($status, $body) = $request('/contactdata.php', 'POST', array(
        'nm' => 'Test Alum',
        'mail' => 'contact@example.test',
        'msg' => $message,
    ));
    check($status === 200 && strpos($body, 'Message received') !== false, 'Valid contact message was not accepted.');
    $savedMessage = $database->query('SELECT msg FROM contact')->fetch_row();
    check($savedMessage && $savedMessage[0] === $message, 'Contact input was not stored literally.');
    check((int) $database->query('SELECT COUNT(*) FROM contact')->fetch_row()[0] === 1, 'Contact form did not save exactly one row.');

    list($status) = $request('/contactdata.php', 'POST', array(
        'nm' => 'Test Alum',
        'mail' => 'contact@example.test',
        'msg' => str_repeat('x', 5001),
    ));
    check($status === 400, 'Overlong contact message was not rejected.');
    list($status) = $request('/contactdata.php', 'POST', array(
        'nm' => 'Test Alum',
        'mail' => 'not-an-email',
        'msg' => 'Invalid email check',
    ));
    check($status === 400, 'Invalid contact email was not rejected.');

    $password = 'do-not-reflect-this-password';
    list($status, $body) = $request('/second.php', 'POST', array('user' => 'test', 'pass' => $password));
    check($status === 410 && strpos($body, $password) === false, 'Disabled demo endpoint exposed submitted password data.');

    $database->close();
    echo "Integration checks passed: validation, prepared writes, SQL-injection payloads, and disabled password demo.\n";
} finally {
    proc_terminate($server);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    proc_close($server);
}
