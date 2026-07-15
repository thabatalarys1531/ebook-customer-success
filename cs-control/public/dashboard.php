<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
Auth::requireLogin();

$db = Database::get();
Alerts::sync();

// ---------------------------------------------------------------------------
// Cards do dia (contagens de hoje x ontem)
// ---------------------------------------------------------------------------
function count_type_on_date(PDO $db, string $type, string $date): int
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM activities WHERE type = ? AND DATE(created_at) = ?");
    $stmt->execute([$type, $date]);
    return (int) $stmt->fetchColumn();
}

$hoje = date('Y-m-d');
$ontem = date('Y-m-d', strtotime('-1 day'));

$reunioesHoje  = count_type_on_date($db, 'reuniao', $hoje);
$reunioesOntem = count_type_on_date($db, 'reuniao', $ontem);

$atividadesHoje  = (int) $db->query("SELECT COUNT(*) FROM activities WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$atividadesOntem = (int) $db->query("SELECT COUNT(*) FROM activities WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();

// Contratos pendentes: total em aberto agora + quantos novos entraram hoje/ontem
$contratosPendentesTotal = (int) $db->query("SELECT COUNT(*) FROM activities WHERE type = 'contrato' AND status != 'Concluído'")->fetchColumn();
$contratosNovosHoje  = (int) $db->query("SELECT COUNT(*) FROM activities WHERE type = 'contrato' AND status != 'Concluído' AND DATE(created_at) = CURDATE()")->fetchColumn();
$contratosNovosOntem = (int) $db->query("SELECT COUNT(*) FROM activities WHERE type = 'contrato' AND status != 'Concluído' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();

// Clientes em risco: nº de clientes distintos com alerta aberto agora + quantos são novos hoje/ontem
$clientesEmRisco = (int) $db->query("SELECT COUNT(DISTINCT client) FROM alerts WHERE status = 'aberto' AND client IS NOT NULL")->fetchColumn();
$riscoNovosHoje  = (int) $db->query("SELECT COUNT(DISTINCT client) FROM alerts WHERE DATE(created_at) = CURDATE() AND client IS NOT NULL")->fetchColumn();
$riscoNovosOntem = (int) $db->query("SELECT COUNT(DISTINCT client) FROM alerts WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND client IS NOT NULL")->fetchColumn();

// ARR em risco: soma do impacto das atividades ligadas a alertas abertos agora
$arrEmRisco = (float) $db->query("
    SELECT COALESCE(SUM(a.arr_impact), 0)
    FROM activities a
    JOIN alerts al ON al.activity_id = a.id
    WHERE al.status = 'aberto'
")->fetchColumn();
$arrRiscoNovoHoje = (float) $db->query("
    SELECT COALESCE(SUM(a.arr_impact), 0)
    FROM activities a
    JOIN alerts al ON al.activity_id = a.id
    WHERE al.status = 'aberto' AND DATE(al.created_at) = CURDATE()
")->fetchColumn();
$arrRiscoBaseOntem = $arrEmRisco - $arrRiscoNovoHoje;

$cards = [
    [
        'label' => 'Clientes em risco', 'value' => (string) $clientesEmRisco,
        'diff'  => $riscoNovosHoje - $riscoNovosOntem, 'suffix' => '',
        'icon' => 'users', 'color' => 'rose',
    ],
    [
        'label' => 'Contratos pendentes', 'value' => (string) $contratosPendentesTotal,
        'diff'  => $contratosNovosHoje - $contratosNovosOntem, 'suffix' => '',
        'icon' => 'file', 'color' => 'amber',
    ],
    [
        'label' => 'Reuniões hoje', 'value' => (string) $reunioesHoje,
        'diff'  => $reunioesHoje - $reunioesOntem, 'suffix' => '',
        'icon' => 'calendar', 'color' => 'emerald',
    ],
    [
        'label' => 'ARR em risco', 'value' => money_brl($arrEmRisco),
        'diff'  => (int) round(percent_change((int) $arrEmRisco, (int) $arrRiscoBaseOntem) ?? 0), 'suffix' => '%',
        'icon' => 'currency', 'color' => 'indigo',
    ],
    [
        'label' => 'Atividades hoje', 'value' => (string) $atividadesHoje,
        'diff'  => $atividadesHoje - $atividadesOntem, 'suffix' => '',
        'icon' => 'check', 'color' => 'sky',
    ],
];

// ---------------------------------------------------------------------------
// Widgets
// ---------------------------------------------------------------------------
$alertasAbertos = Alerts::openAlerts(5);

$agendaHoje = $db->query("SELECT * FROM agenda_events WHERE event_date = CURDATE() ORDER BY time ASC")->fetchAll();

$timeline = $db->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 8")->fetchAll();

$stats = $db->query("SELECT * FROM stats_manual WHERE id = 1")->fetch() ?: [
    'clientes_self' => 0, 'migrados' => 0, 'pendentes' => 0, 'arr_total' => 0,
    'retencao_pct' => 0, 'churn_pct' => 0, 'clientes_ativos' => 0,
];

$clientesExistentes = Clients::all();
$insights = Insights::generate();

$colorClasses = [
    'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
    'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
    'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
    'sky'     => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
];

$pageTitle = 'Minha Central';
$activeNav = 'dashboard';
$topbarLeft = '<h1 class="text-lg font-semibold text-slate-800">' . h(Insights::greeting()) . ', ' . h(first_name(Auth::name())) . '! 👋</h1>'
            . '<p class="text-xs text-slate-500">' . h(pt_br_full_date()) . '</p>';

require __DIR__ . '/partials/header.php';
?>

<div class="flex items-start justify-between gap-4 mb-5">
    <div class="lg:hidden"><!-- título já aparece na topbar em telas maiores --></div>
    <div class="flex-1"></div>
    <button data-modal-open="modal-nova-atividade"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition shrink-0">
        <?= nav_icon('plus', 'w-4 h-4') ?> Nova atividade
    </button>
</div>

<!-- Cards do dia -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    <?php foreach ($cards as $card):
        $colors = $colorClasses[$card['color']];
        $diff = $card['diff'];
        $sign = $diff > 0 ? '+' : ($diff < 0 ? '' : '');
        $diffText = $diff === 0 ? 'sem mudança em relação a ontem' : "{$sign}{$diff}{$card['suffix']} em relação a ontem";
    ?>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <div class="w-9 h-9 rounded-full <?= $colors['bg'] ?> <?= $colors['text'] ?> flex items-center justify-center mb-3">
                <?= nav_icon($card['icon'], 'w-5 h-5') ?>
            </div>
            <div class="text-xs text-slate-500 mb-1"><?= h($card['label']) ?></div>
            <div class="text-xl font-bold text-slate-800"><?= h($card['value']) ?></div>
            <div class="text-xs <?= $colors['text'] ?> mt-1"><?= h($diffText) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Alertas / Agenda / Registrar rápido -->
<div id="alertas" class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-4">O que precisa da sua atenção</h2>
        <?php if (empty($alertasAbertos)): ?>
            <p class="text-sm text-slate-400">Nenhum alerta em aberto. Tudo em dia!</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($alertasAbertos as $alerta):
                    $badgeMap = [
                        'cancelamento'    => ['label' => 'EM RISCO', 'bg' => 'bg-red-100', 'text' => 'text-red-700', 'avatar' => 'bg-red-500'],
                        'prioridade_alta' => ['label' => 'PRIORIDADE ALTA', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'avatar' => 'bg-amber-500'],
                        'sem_retorno'     => ['label' => 'SEM RETORNO', 'bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'avatar' => 'bg-slate-400'],
                        'manual'          => ['label' => 'ALERTA', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'avatar' => 'bg-sky-500'],
                    ];
                    $badge = $badgeMap[$alerta['type']] ?? $badgeMap['manual'];
                    $dataAlerta = date('Y-m-d', strtotime($alerta['created_at']));
                    $quando = $dataAlerta === $hoje ? 'Hoje' : ($dataAlerta === $ontem ? 'Ontem' : date('d/m', strtotime($alerta['created_at'])));
                    $inicial = $alerta['client'] ? mb_strtoupper(mb_substr($alerta['client'], 0, 1)) : '!';
                ?>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full <?= $badge['avatar'] ?> text-white flex items-center justify-center font-semibold text-sm shrink-0"><?= h($inicial) ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-slate-800 text-sm"><?= h($alerta['client'] ?: 'Alerta manual') ?></span>
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded <?= $badge['bg'] ?> <?= $badge['text'] ?>"><?= h($badge['label']) ?></span>
                            </div>
                            <p class="text-xs text-slate-500 truncate"><?= h($alerta['message']) ?></p>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-slate-400 mb-1"><?= h($quando) ?></div>
                            <button class="js-resolve-alert text-[11px] text-indigo-600 hover:underline" data-alert-id="<?= (int) $alerta['id'] ?>" data-csrf="<?= h(csrf_token()) ?>">Resolver</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="em-breve.php?modulo=configuracoes&titulo=Alertas" class="inline-block mt-4 text-sm text-indigo-600 hover:underline">Ver todos os alertas →</a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Agenda de hoje</h2>
            <a href="em-breve.php?modulo=agenda&titulo=Agenda" class="text-xs text-indigo-600 hover:underline">Ver agenda completa</a>
        </div>
        <?php if (empty($agendaHoje)): ?>
            <p class="text-sm text-slate-400">Nenhum evento para hoje ainda.</p>
            <p class="text-xs text-slate-400 mt-1">Conecte o Google Calendar em Integrações para preencher automaticamente.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($agendaHoje as $evento): ?>
                    <div class="flex items-start gap-3">
                        <div class="text-sm font-semibold text-slate-700 w-12 shrink-0"><?= h(substr((string) $evento['time'], 0, 5)) ?></div>
                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-800"><?= h($evento['title']) ?></div>
                            <div class="text-xs text-slate-500"><?= h($evento['note'] ?: ($evento['client'] ?? '')) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-4">Registrar atividade rápida</h2>
        <form action="api/activities_create.php" method="post" class="js-ajax-form space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="Concluído">
            <input type="hidden" name="priority" value="Média">

            <div class="js-form-error hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Cliente</label>
                <input type="text" name="client" list="lista-clientes" required placeholder="Selecione ou digite um cliente"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <datalist id="lista-clientes">
                    <?php foreach ($clientesExistentes as $c): ?>
                        <option value="<?= h($c['name']) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de atividade</label>
                <div class="grid grid-cols-5 gap-2">
                    <?php foreach (ActivityTypes::QUICK_TYPES as $i => $type): ?>
                        <label class="flex flex-col items-center gap-1 border rounded-lg py-2 cursor-pointer text-slate-500 has-[:checked]:border-indigo-500 has-[:checked]:text-indigo-600 has-[:checked]:bg-indigo-50 border-slate-200">
                            <input type="radio" name="type" value="<?= h($type) ?>" <?= $i === 0 ? 'checked' : '' ?> class="sr-only">
                            <?= nav_icon(ActivityTypes::ICONS[$type], 'w-4 h-4') ?>
                            <span class="text-[10px]"><?= h(ActivityTypes::label($type)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Resumo</label>
                <textarea name="summary" rows="3" placeholder="Descreva sua atividade..."
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg py-2.5 transition">
                Salvar atividade
            </button>
        </form>
    </div>
</div>

<!-- Timeline + Premium4 IA -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Timeline de atividades</h2>
            <a href="em-breve.php?modulo=atividades&titulo=Atividades" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
        </div>
        <?php if (empty($timeline)): ?>
            <p class="text-sm text-slate-400">Nenhuma atividade registrada ainda. Use o painel ao lado para começar.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($timeline as $item): ?>
                    <div class="flex items-start gap-3">
                        <div class="text-xs text-slate-400 w-12 shrink-0 pt-0.5"><?= h(date('H:i', strtotime($item['created_at']))) ?></div>
                        <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <?= nav_icon(ActivityTypes::ICONS[$item['type']] ?? 'dots', 'w-3.5 h-3.5') ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-800"><?= h(ActivityTypes::label($item['type'])) ?> — <?= h($item['client']) ?></div>
                            <p class="text-xs text-slate-500 truncate"><?= h($item['summary'] ?: 'Sem resumo') ?></p>
                        </div>
                        <span class="text-[11px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full shrink-0"><?= h($item['client']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="premium4-ia" class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl shadow-sm p-5 text-white">
        <div class="flex items-center gap-2 mb-4">
            <?= nav_icon('sparkles', 'w-5 h-5') ?>
            <h2 class="font-semibold">Premium4 IA</h2>
        </div>
        <div class="space-y-3 mb-4">
            <?php foreach ($insights as $msg): ?>
                <div class="bg-white/15 rounded-lg px-3 py-2 text-sm leading-snug"><?= h($msg) ?></div>
            <?php endforeach; ?>
        </div>
        <input type="text" disabled placeholder="Pergunte algo para a Premium4..."
               title="Perguntas livres exigem ligar uma IA real (com custo). Por ora, os insights acima são calculados por regras direto dos seus dados."
               class="w-full rounded-lg bg-white/10 border border-white/20 px-3 py-2 text-sm placeholder-white/60 cursor-not-allowed">
    </div>
</div>

<!-- Rodapé de indicadores -->
<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 flex flex-wrap items-center gap-6 justify-between">
    <div class="flex flex-wrap gap-8">
        <div>
            <div class="text-xs text-slate-500">ARR total</div>
            <div class="text-lg font-bold text-slate-800"><?= h(money_brl((float) $stats['arr_total'])) ?></div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Retenção</div>
            <div class="text-lg font-bold text-emerald-600"><?= h(number_format((float) $stats['retencao_pct'], 1, ',', '.')) ?>%</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Churn</div>
            <div class="text-lg font-bold text-rose-600"><?= h(number_format((float) $stats['churn_pct'], 1, ',', '.')) ?>%</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Clientes ativos</div>
            <div class="text-lg font-bold text-slate-800"><?= (int) $stats['clientes_ativos'] ?></div>
        </div>
    </div>
    <button data-modal-open="modal-indicadores" class="text-sm text-indigo-600 hover:underline shrink-0">Editar indicadores</button>
</div>

<!-- Modal: Nova atividade (completo) -->
<div id="modal-nova-atividade" x-modal class="fixed inset-0 z-50 items-center justify-center p-4 modal-backdrop">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Registrar atividade</h3>
            <button data-modal-close class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form action="api/activities_create.php" method="post" class="js-ajax-form space-y-4">
            <?= csrf_field() ?>
            <div class="js-form-error hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Cliente</label>
                <input type="text" name="client" list="lista-clientes" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
                    <select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach (ActivityTypes::LABELS as $key => $label): ?>
                            <option value="<?= h($key) ?>"><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach (ActivityTypes::STATUSES as $status): ?>
                            <option value="<?= h($status) ?>"><?= h($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prioridade</label>
                    <select name="priority" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach (ActivityTypes::PRIORITIES as $priority): ?>
                            <option value="<?= h($priority) ?>" <?= $priority === 'Média' ? 'selected' : '' ?>><?= h($priority) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Impacto no ARR (opcional)</label>
                    <input type="number" step="0.01" name="arr_impact" placeholder="0,00"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Resumo</label>
                <textarea name="summary" rows="3"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Salvar atividade</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar indicadores -->
<div id="modal-indicadores" x-modal class="fixed inset-0 z-50 items-center justify-center p-4 modal-backdrop">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Editar indicadores</h3>
            <button data-modal-close class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Esses números são editados manualmente até as integrações estarem completas.</p>
        <form action="api/stats_update.php" method="post" class="js-ajax-form space-y-3">
            <?= csrf_field() ?>
            <div class="js-form-error hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">ARR total (R$)</label>
                    <input type="number" step="0.01" name="arr_total" value="<?= h((string) $stats['arr_total']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Clientes ativos</label>
                    <input type="number" name="clientes_ativos" value="<?= h((string) $stats['clientes_ativos']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Retenção (%)</label>
                    <input type="number" step="0.01" name="retencao_pct" value="<?= h((string) $stats['retencao_pct']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Churn (%)</label>
                    <input type="number" step="0.01" name="churn_pct" value="<?= h((string) $stats['churn_pct']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Clientes Self</label>
                    <input type="number" name="clientes_self" value="<?= h((string) $stats['clientes_self']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Migrados</label>
                    <input type="number" name="migrados" value="<?= h((string) $stats['migrados']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pendentes (migração)</label>
                    <input type="number" name="pendentes" value="<?= h((string) $stats['pendentes']) ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
