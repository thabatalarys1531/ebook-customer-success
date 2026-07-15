<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 401);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada.'], 419);
}

$fields = ['clientes_self', 'migrados', 'pendentes', 'arr_total', 'retencao_pct', 'churn_pct', 'clientes_ativos'];
$values = [];

foreach ($fields as $field) {
    $raw = trim($_POST[$field] ?? '0');
    if ($raw === '' || !is_numeric($raw)) {
        json_response(['success' => false, 'message' => "Valor inválido para {$field}."]);
    }
    $values[$field] = $raw;
}

$db = Database::get();
$stmt = $db->prepare("UPDATE stats_manual SET
    clientes_self = ?, migrados = ?, pendentes = ?, arr_total = ?,
    retencao_pct = ?, churn_pct = ?, clientes_ativos = ?
    WHERE id = 1");
$stmt->execute(array_values($values));

json_response(['success' => true]);
