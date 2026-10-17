<?php
require_once __DIR__ . '/database.php';

alumni_require_post();
$values = alumni_form_values($_POST, array(
    'roll' => 32,
    'name' => 120,
    'email' => 254,
    'mob' => 32,
    'address' => 1000,
));

if ($values === false || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
    alumni_response(400, 'Registration not submitted', 'Enter all fields using a valid email address and try again.');
}

try {
    $connection = alumni_db();
    $statement = $connection->prepare(
        'INSERT INTO student (roll, name, email, mob, address) VALUES (?, ?, ?, ?, ?)'
    );
    $statement->bind_param('sssss', $values['roll'], $values['name'], $values['email'], $values['mob'], $values['address']);
    $statement->execute();
    $statement->close();
    $connection->close();
} catch (Throwable $error) {
    error_log('Alumni registration save failed: ' . $error->getMessage());
    alumni_response(503, 'Registration unavailable', 'We could not save your request. Please try again later.');
}

alumni_response(200, 'Registration received', 'Your registration details were saved.');