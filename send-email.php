<?php
// Встановлюємо заголовки перед будь-яким виводом
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обробка preflight запиту
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Вимкнути виведення помилок для чистого JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Функція для безпечного виводу JSON
function sendJsonResponse($success, $message, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Перевірка методу запиту
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Дозволений тільки POST метод', 405);
}

// Отримуємо дані
$input = file_get_contents('php://input');

if (empty($input)) {
    sendJsonResponse(false, 'Порожній запит', 400);
}

$data = json_decode($input, true);

// Перевірка наявності даних
if ($data === null || !is_array($data)) {
    sendJsonResponse(false, 'Невірний формат даних', 400);
}

if (!isset($data['name']) || !isset($data['email']) || !isset($data['question'])) {
    sendJsonResponse(false, 'Всі поля обов\'язкові', 400);
}

$name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$question = htmlspecialchars(trim($data['question']), ENT_QUOTES, 'UTF-8');

// Перевірка на порожні значення
if (empty($name) || empty($email) || empty($question)) {
    sendJsonResponse(false, 'Всі поля обов\'язкові', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'Невірний формат email', 400);
}

$to = 'braunvlad981@gmail.com';
$subject = '=?UTF-8?B?' . base64_encode('Зворотний зв\'язок з сайту') . '?=';
$message = "Ім'я: $name\n";
$message .= "Email: $email\n\n";
$message .= "Повідомлення:\n$question";

// Заголовки для коректної відправки
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "From: =?UTF-8?B?" . base64_encode($name) . "?= <$email>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Спробувати відправити email
try {
    $result = @mail($to, $subject, $message, $headers);
    
    if ($result) {
        sendJsonResponse(true, 'Повідомлення успішно відправлено', 200);
    } else {
        // Отримуємо останню помилку PHP
        $error = error_get_last();
        $errorMsg = 'Помилка відправки повідомлення';
        
        if ($error && strpos($error['message'], 'mail') !== false) {
            $errorMsg .= '. Перевірте налаштування mail() на сервері.';
        }
        
        sendJsonResponse(false, $errorMsg, 500);
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'Помилка: ' . $e->getMessage(), 500);
}
?>

