<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

// Esta página só funciona uma vez: assim que o primeiro usuário é criado,
// ela se desativa sozinha e manda todo mundo para o login.
if (Auth::hasUsers()) {
    redirect('login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sessão expirada, atualize a página e tente novamente.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            $errors[] = 'Preencha nome, e-mail e senha.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'A senha precisa ter pelo menos 8 caracteres.';
        } elseif ($password !== $password2) {
            $errors[] = 'As senhas não conferem.';
        }

        if (empty($errors)) {
            Auth::createUser($name, $email, $password, 'admin');
            redirect('login.php?criado=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CS Control — Primeiro acesso</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center gap-2 mb-1">
            <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">CS</div>
            <span class="text-xl font-bold text-slate-800">CS Control</span>
        </div>
        <p class="text-slate-500 mb-6">Vamos criar sua conta de acesso. Isso só aparece uma vez.</p>

        <?php foreach ($errors as $error): ?>
            <div class="mb-4 rounded-lg bg-red-50 text-red-700 text-sm px-4 py-2"><?= h($error) ?></div>
        <?php endforeach; ?>

        <form method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Seu nome</label>
                <input type="text" name="name" value="<?= h($_POST['name'] ?? 'Thábata Silva') ?>" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail de login</label>
                <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Senha (mínimo 8 caracteres)</label>
                <input type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar senha</label>
                <input type="password" name="password2" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5 transition">
                Criar minha conta
            </button>
        </form>
    </div>
</body>
</html>
