<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 401);
}
if (!Permissions::userCan(Auth::id(), 'usuarios')) {
    json_response(['success' => false, 'message' => 'Você não tem permissão para alterar usuários.'], 403);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 419);
}

$id = (int) ($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($id === Auth::id()) {
    json_response(['success' => false, 'message' => 'Você não pode desativar sua própria conta.']);
}
if (!in_array($status, ['ativo', 'inativo'], true)) {
    json_response(['success' => false, 'message' => 'Status inválido.']);
}
if (!Users::find($id)) {
    json_response(['success' => false, 'message' => 'Usuário não encontrado.']);
}

Users::setStatus($id, $status);
json_response(['success' => true]);
