<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
Auth::requireLogin();
Permissions::requireAccess('usuarios');

$pageTitle = 'Usuários';
$activeNav = 'usuarios';
$topbarLeft = '<h1 class="text-lg font-semibold text-slate-800">Usuários</h1>';

require __DIR__ . '/partials/header.php';

$users = Users::all();
?>

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500 max-w-xl">
        Como o CS Control é uma ferramenta pessoal, normalmente só existe um usuário (você).
        Esta tela existe caso você queira dar acesso a mais alguém no futuro — o acesso de cada
        pessoa é ajustado na tela de Permissões.
    </p>
    <button data-modal-open="modal-novo-usuario"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition shrink-0">
        <?= nav_icon('plus', 'w-4 h-4') ?> Novo usuário
    </button>
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
            <tr>
                <th class="text-left px-4 py-3">Nome</th>
                <th class="text-left px-4 py-3">E-mail</th>
                <th class="text-left px-4 py-3">Papel</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-right px-4 py-3">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($users as $u): ?>
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800"><?= h($u['name']) ?></td>
                    <td class="px-4 py-3 text-slate-600"><?= h($u['email']) ?></td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $u['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' ?>">
                            <?= $u['role'] === 'admin' ? 'Administrador' : 'Membro' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $u['status'] === 'ativo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                            <?= $u['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if ($u['id'] !== Auth::id()): ?>
                            <form action="api/users_status.php" method="post" class="js-ajax-form inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <input type="hidden" name="status" value="<?= $u['status'] === 'ativo' ? 'inativo' : 'ativo' ?>">
                                <button type="submit" class="text-xs text-indigo-600 hover:underline">
                                    <?= $u['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-xs text-slate-300">você</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-novo-usuario" x-modal class="fixed inset-0 z-50 items-center justify-center p-4 modal-backdrop">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Novo usuário</h3>
            <button data-modal-close class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <form action="api/users_create.php" method="post" class="js-ajax-form space-y-3">
            <?= csrf_field() ?>
            <div class="js-form-error hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nome</label>
                <input type="text" name="name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">E-mail</label>
                <input type="email" name="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Senha</label>
                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Papel</label>
                <select name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="membro">Membro (acesso liberado por módulo)</option>
                    <option value="admin">Administrador (acesso total)</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Criar usuário</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
