<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
Auth::requireLogin();
Permissions::requireAccess('permissoes');

$pageTitle = 'Permissões';
$activeNav = 'permissoes';
$topbarLeft = '<h1 class="text-lg font-semibold text-slate-800">Permissões</h1>';

require __DIR__ . '/partials/header.php';

$users = Users::all();
?>

<p class="text-sm text-slate-500 max-w-xl mb-5">
    Administradores sempre têm acesso a tudo. Para usuários do tipo "Membro", escolha
    quais telas cada um pode ver.
</p>

<div class="space-y-4">
    <?php foreach ($users as $u): ?>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="font-medium text-slate-800"><?= h($u['name']) ?></div>
                    <div class="text-xs text-slate-500"><?= h($u['email']) ?></div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full <?= $u['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' ?>">
                    <?= $u['role'] === 'admin' ? 'Administrador' : 'Membro' ?>
                </span>
            </div>

            <?php if ($u['role'] === 'admin'): ?>
                <p class="text-sm text-slate-400">Acesso total — administradores enxergam todas as telas.</p>
            <?php else: ?>
                <?php $perms = Permissions::forUser((int) $u['id']); ?>
                <form action="api/permissions_update.php" method="post" class="js-ajax-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <?php foreach ($perms as $perm): ?>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="permissions[]" value="<?= h($perm['key_name']) ?>" <?= $perm['allowed'] ? 'checked' : '' ?>
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <?= h($perm['label']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="js-form-error hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-3"></div>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Salvar permissões</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
