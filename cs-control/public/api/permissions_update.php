<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 401);
}
if (!Permissions::userCan(Auth::id(), 'permissoes')) {
    json_response(['success' => false, 'message' => 'Você não tem permissão para alterar permissões.'], 403);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 419);
}

$userId = (int) ($_POST['user_id'] ?? 0);
$allowed = $_POST['permissions'] ?? [];

if (!is_array($allowed)) {
    $allowed = [];
}
if (!Users::find($userId)) {
    json_response(['success' => false, 'message' => 'Usuário não encontrado.']);
}

$validKeys = array_column(Permissions::all(), 'key_name');
$allowed = array_values(array_intersect($allowed, $validKeys));

Permissions::updateForUser($userId, $allowed);
json_response(['success' => true]);
