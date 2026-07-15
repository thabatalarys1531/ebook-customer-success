<?php
declare(strict_types=1);
// Espera que a página que inclui este arquivo já tenha chamado Auth::requireLogin()
// e definido $pageTitle e $activeNav antes do include.
require_once __DIR__ . '/icons.php';

$navSections = [
    [
        'items' => [
            ['key' => 'dashboard', 'label' => 'Minha Central', 'href' => 'dashboard.php', 'icon' => 'home'],
            ['key' => 'dashboard2', 'label' => 'Dashboard', 'href' => 'em-breve.php?modulo=dashboard2&titulo=Dashboard', 'icon' => 'grid'],
            ['key' => 'clientes', 'label' => 'Clientes', 'href' => 'em-breve.php?modulo=clientes&titulo=Clientes', 'icon' => 'users'],
            ['key' => 'atividades', 'label' => 'Atividades', 'href' => 'em-breve.php?modulo=atividades&titulo=Atividades', 'icon' => 'clipboard'],
            ['key' => 'agenda', 'label' => 'Agenda', 'href' => 'em-breve.php?modulo=agenda&titulo=Agenda', 'icon' => 'calendar'],
            ['key' => 'contratos', 'label' => 'Contratos', 'href' => 'em-breve.php?modulo=contratos&titulo=Contratos', 'icon' => 'file'],
            ['key' => 'financeiro', 'label' => 'Financeiro', 'href' => 'em-breve.php?modulo=financeiro&titulo=Financeiro', 'icon' => 'currency'],
            ['key' => 'analytics', 'label' => 'Analytics', 'href' => 'em-breve.php?modulo=analytics&titulo=Analytics', 'icon' => 'bar-chart'],
            ['key' => 'relatorios', 'label' => 'Relatórios', 'href' => 'em-breve.php?modulo=relatorios&titulo=Relat%C3%B3rios', 'icon' => 'report'],
            ['key' => 'premium4', 'label' => 'Premium4 IA', 'href' => 'dashboard.php#premium4-ia', 'icon' => 'sparkles'],
        ],
    ],
    [
        'title' => 'CONFIGURAÇÕES',
        'items' => [
            ['key' => 'configuracoes', 'label' => 'Configurações', 'href' => 'em-breve.php?modulo=configuracoes&titulo=Configura%C3%A7%C3%B5es', 'icon' => 'cog'],
            ['key' => 'integracoes', 'label' => 'Integrações', 'href' => 'em-breve.php?modulo=integracoes&titulo=Integra%C3%A7%C3%B5es', 'icon' => 'plug'],
            ['key' => 'usuarios', 'label' => 'Usuários', 'href' => 'usuarios.php', 'icon' => 'users'],
            ['key' => 'permissoes', 'label' => 'Permissões', 'href' => 'permissoes.php', 'icon' => 'shield'],
        ],
    ],
];

$userId = Auth::id();
$canSee = function (string $key) use ($userId): bool {
    if (in_array($key, ['dashboard', 'dashboard2', 'premium4'], true)) {
        return true;
    }
    return Permissions::userCan($userId, $key);
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? 'CS Control') ?> — CS Control</title>
<link rel="stylesheet" href="assets/css/tailwind.css">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg-slate-50 text-slate-800">
<div class="flex min-h-screen">

    <aside id="sidebar" class="w-64 shrink-0 bg-slate-900 text-slate-300 flex flex-col fixed lg:static inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0 transition-transform">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-800">
            <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white font-bold text-sm">CS</div>
            <span class="text-white font-semibold text-lg">CS Control</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
            <?php foreach ($navSections as $section): ?>
                <div>
                    <?php if (!empty($section['title'])): ?>
                        <div class="px-3 pb-2 text-xs font-semibold tracking-wider text-slate-500"><?= h($section['title']) ?></div>
                    <?php endif; ?>
                    <div class="space-y-1">
                        <?php foreach ($section['items'] as $item):
                            if (!$canSee($item['key'])) continue;
                            $isActive = ($activeNav ?? '') === $item['key'];
                        ?>
                            <a href="<?= h($item['href']) ?>"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= $isActive ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
                                <?= nav_icon($item['icon']) ?>
                                <span><?= h($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="p-3 border-t border-slate-800">
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-slate-800 hover:text-white transition">
                <?= nav_icon('help') ?>
                <span>Central de Ajuda</span>
            </a>
            <a href="logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-slate-800 hover:text-white transition">
                <?= nav_icon('logout') ?>
                <span>Sair</span>
            </a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center gap-4 px-4 lg:px-6 sticky top-0 z-20">
            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="flex-1 min-w-0">
                <?= $topbarLeft ?? '' ?>
            </div>

            <div class="hidden md:flex items-center flex-1 max-w-md">
                <div class="relative w-full">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><?= nav_icon('search', 'w-4 h-4') ?></span>
                    <input type="text" placeholder="Buscar clientes, contratos, atividades..." disabled
                           title="Busca chega em uma próxima etapa"
                           class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-400 placeholder-slate-400 cursor-not-allowed">
                </div>
            </div>

            <a href="#alertas" class="relative p-2 rounded-lg hover:bg-slate-100 text-slate-500" title="Alertas em aberto">
                <?= nav_icon('bell') ?>
                <?php $openAlerts = Alerts::countOpen(); if ($openAlerts > 0): ?>
                    <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] leading-none rounded-full w-4 h-4 flex items-center justify-center"><?= min($openAlerts, 9) ?><?= $openAlerts > 9 ? '+' : '' ?></span>
                <?php endif; ?>
            </a>

            <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold text-sm">
                    <?= h(mb_strtoupper(mb_substr(Auth::name(), 0, 1))) ?>
                </div>
                <div class="hidden sm:block leading-tight">
                    <div class="text-sm font-semibold text-slate-800"><?= h(Auth::name()) ?></div>
                    <div class="text-xs text-slate-500">Customer Success</div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
