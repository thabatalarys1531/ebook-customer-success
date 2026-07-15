<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_check(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function money_brl(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function percent_change(int $today, int $yesterday): ?float
{
    if ($yesterday === 0) {
        return $today > 0 ? 100.0 : null;
    }
    return round((($today - $yesterday) / $yesterday) * 100, 1);
}

// Escrito manualmente (sem setlocale/intl) porque hospedagens cPanel nem sempre
// têm o locale pt_BR instalado no servidor.
function pt_br_full_date(?string $date = null): string
{
    $ts = $date ? strtotime($date) : time();

    $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    $meses = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

    $dia = $dias[(int) date('w', $ts)];
    $numero = (int) date('j', $ts);
    $mes = $meses[(int) date('n', $ts)];
    $ano = date('Y', $ts);

    return "{$dia}, {$numero} de {$mes} de {$ano}";
}

function first_name(string $fullName): string
{
    $parts = explode(' ', trim($fullName));
    return $parts[0] ?? $fullName;
}
