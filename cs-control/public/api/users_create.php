<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 401);
}
if (!Permissions::userCan(Auth::id(), 'usuarios')) {
    json_response(['success' => false, 'message' => 'Você não tem permissão para criar usuários.'], 403);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 419);
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = trim($_POST['role'] ?? 'membro');

if ($name === '' || $email === '' || $password === '') {
    json_response(['success' => false, 'message' => 'Preencha nome, e-mail e senha.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Informe um e-mail válido.']);
}
if (strlen($password) < 8) {
    json_response(['success' => false, 'message' => 'A senha precisa ter pelo menos 8 caracteres.']);
}
if (!in_array($role, ['admin', 'membro'], true)) {
    json_response(['success' => false, 'message' => 'Papel inválido.']);
}
if (Users::emailExists($email)) {
    json_response(['success' => false, 'message' => 'Já existe um usuário com esse e-mail.']);
}

Auth::createUser($name, $email, $password, $role);
json_response(['success' => true]);
