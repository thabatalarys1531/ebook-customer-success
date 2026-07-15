<?php
declare(strict_types=1);

// Fonte única da verdade para os tipos de atividade, evitando repetir a mesma
// lista em vários arquivos (formulário rápido, modal completo, timeline, API).
final class ActivityTypes
{
    public const LABELS = [
        'email'        => 'E-mail',
        'reuniao'      => 'Reunião',
        'ligacao'      => 'Ligação',
        'whatsapp'     => 'WhatsApp',
        'migracao'     => 'Migração',
        'cancelamento' => 'Cancelamento',
        'contrato'     => 'Contrato',
        'financeiro'   => 'Financeiro',
        'outro'        => 'Outro',
    ];

    // Ícones usados no painel rápido de "Registrar atividade" (5 atalhos, como no protótipo)
    public const QUICK_TYPES = ['email', 'reuniao', 'ligacao', 'whatsapp', 'outro'];

    public const ICONS = [
        'email'        => 'mail',
        'reuniao'      => 'calendar',
        'ligacao'      => 'phone',
        'whatsapp'     => 'whatsapp',
        'migracao'     => 'bar-chart',
        'cancelamento' => 'x-circle',
        'contrato'     => 'file',
        'financeiro'   => 'currency',
        'outro'        => 'dots',
    ];

    public const STATUSES = ['Concluído', 'Aguardando', 'Em andamento'];

    public const PRIORITIES = ['Alta', 'Média', 'Baixa'];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? ucfirst($type);
    }

    public static function isValid(string $type): bool
    {
        return array_key_exists($type, self::LABELS);
    }
}
