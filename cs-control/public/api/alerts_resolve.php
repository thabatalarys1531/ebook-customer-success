<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 401);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 419);
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    json_response(['success' => false, 'message' => 'Alerta inválido.']);
}

Alerts::resolve($id);
json_response(['success' => true]);
