<?php
require_once __DIR__ . '/database.php';

alumni_require_post();
$values = alumni_form_values($_POST, array(
    'nm' => 120,
    'mail' => 254,
    'msg' => 5000,
));

if ($values === false || filter_var($values['mail'], FILTER_VALIDATE_EMAIL) === false) {
    alumni_response(400, 'Message not submitted', 'Enter your name, a valid email address, and a message, then try again.');
}

try {
    $connection = alumni_db();
    $statement = $connection->prepare('INSERT INTO contact (nm, mail, msg) VALUES (?, ?, ?)');
    $statement->bind_param('sss', $values['nm'], $values['mail'], $values['msg']);
    $statement->execute();
    $statement->close();
    $connection->close();
} catch (Throwable $error) {
    error_log('Alumni contact save failed: ' . $error->getMessage());
    alumni_response(503, 'Message unavailable', 'We could not save your message. Please try again later.');
}

alumni_response(200, 'Message received', 'Your message was saved.');