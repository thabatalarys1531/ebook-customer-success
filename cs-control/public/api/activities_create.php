<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/ActivityTypes.php';

if (!Auth::check()) {
    json_response(['success' => false, 'message' => 'Sessão expirada. Atualize a página e faça login novamente.'], 401);
}
if (!csrf_check($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Sessão expirada. Atualize a página e tente novamente.'], 419);
}

$type    = trim($_POST['type'] ?? '');
$client  = trim($_POST['client'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$status  = trim($_POST['status'] ?? 'Concluído');
$priority = trim($_POST['priority'] ?? 'Média');
$arrRaw  = trim($_POST['arr_impact'] ?? '');

if ($client === '') {
    json_response(['success' => false, 'message' => 'Informe o nome do cliente.']);
}
if (!ActivityTypes::isValid($type)) {
    json_response(['success' => false, 'message' => 'Tipo de atividade inválido.']);
}
if (!in_array($status, ActivityTypes::STATUSES, true)) {
    json_response(['success' => false, 'message' => 'Status inválido.']);
}
if (!in_array($priority, ActivityTypes::PRIORITIES, true)) {
    json_response(['success' => false, 'message' => 'Prioridade inválida.']);
}

$arrImpact = null;
if ($arrRaw !== '') {
    if (!is_numeric($arrRaw)) {
        json_response(['success' => false, 'message' => 'Impacto no ARR precisa ser um número.']);
    }
    $arrImpact = round((float) $arrRaw, 2);
}

$db = Database::get();
$stmt = $db->prepare("INSERT INTO activities (type, client, summary, status, priority, arr_impact, source)
                       VALUES (?, ?, ?, ?, ?, ?, 'manual')");
$stmt->execute([$type, $client, $summary ?: null, $status, $priority, $arrImpact]);

Clients::ensure($client);
Alerts::sync();

json_response(['success' => true]);
