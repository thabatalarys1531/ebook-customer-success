<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
Auth::requireLogin();

$titulo   = $_GET['titulo'] ?? 'Esta tela';
$activeNav = $_GET['modulo'] ?? '';
$pageTitle = $titulo;
$topbarLeft = '<h1 class="text-lg font-semibold text-slate-800">' . h($titulo) . '</h1>';

require __DIR__ . '/partials/header.php';
?>
<div class="max-w-lg mx-auto text-center bg-white rounded-2xl shadow-sm border border-slate-100 p-10 mt-10">
    <div class="w-14 h-14 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-4">
        <?= nav_icon('cog', 'w-7 h-7') ?>
    </div>
    <h2 class="text-lg font-semibold text-slate-800 mb-2"><?= h($titulo) ?> ainda está a caminho</h2>
    <p class="text-slate-500 text-sm">
        Esta tela faz parte do plano do CS Control, mas ainda não foi construída nesta etapa.
        O Dashboard já está funcionando com dados reais — as demais telas vêm nas próximas etapas.
    </p>
    <a href="dashboard.php" class="inline-block mt-6 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        Voltar para a Central
    </a>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
