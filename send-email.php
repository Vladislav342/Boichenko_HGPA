<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['question'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Всі поля обов\'язкові']);
    exit;
}

$name = htmlspecialchars(trim($data['name']));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$question = htmlspecialchars(trim($data['question']));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Невірний формат email']);
    exit;
}

$to = 'braunvlad981@gmail.com';
$subject = 'Зворотний зв\'язок з сайту';
$message = "Ім'я: $name\n";
$message .= "Email: $email\n\n";
$message .= "Повідомлення:\n$question";

$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Повідомлення успішно відправлено']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка відправки повідомлення']);
}
?>

