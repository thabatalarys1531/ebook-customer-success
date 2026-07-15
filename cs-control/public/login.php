<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

if (!Auth::hasUsers()) {
    redirect('setup.php');
}
if (Auth::check()) {
    redirect('dashboard.php');
}

$error = null;
$justCreated = isset($_GET['criado']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Sessão expirada, atualize a página e tente novamente.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($email, $password)) {
            redirect('dashboard.php');
        }
        $error = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CS Control — Entrar</title>
<link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center gap-2 mb-1">
            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">CS</div>
            <span class="text-xl font-bold text-slate-800">CS Control</span>
        </div>
        <p class="text-slate-500 mb-6">Painel pessoal de Customer Success.</p>

        <?php if ($justCreated): ?>
            <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 text-sm px-4 py-2">
                Conta criada! Faça login abaixo.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-4 rounded-lg bg-red-50 text-red-700 text-sm px-4 py-2"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Senha</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5 transition">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
