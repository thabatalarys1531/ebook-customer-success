<?php
declare(strict_types=1);

// Ícones simples em SVG (sem depender de nenhuma biblioteca externa de ícones).
// São formas geométricas básicas, então funcionam em qualquer navegador sem
// precisar baixar fontes de ícone.
function nav_icon(string $name, string $class = 'w-5 h-5'): string
{
    $common = 'class="' . h($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';

    $shapes = [
        'home'        => '<path d="M3 11l9-7 9 7"/><path d="M5 10v9a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1v-9"/>',
        'grid'        => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.2"/>',
        'users'       => '<circle cx="9" cy="8" r="3"/><path d="M3.5 20c0-3.3 2.5-6 5.5-6s5.5 2.7 5.5 6"/><circle cx="17" cy="9" r="2.4"/><path d="M15.5 14.2c2.6.5 4.5 2.8 4.5 5.8"/>',
        'clipboard'   => '<rect x="5" y="4.5" width="14" height="16" rx="1.5"/><rect x="9" y="3" width="6" height="3" rx="1"/><path d="M8.5 11h7M8.5 14.5h7M8.5 18h4"/>',
        'calendar'    => '<rect x="4" y="5" width="16" height="15" rx="1.5"/><path d="M4 9.5h16M8 3v3.5M16 3v3.5"/><path d="M8 13h2v2H8zM14 13h2v2h-2z"/>',
        'file'        => '<path d="M7 3.5h7l4 4V20a1 1 0 01-1 1H7a1 1 0 01-1-1V4.5a1 1 0 011-1z"/><path d="M14 3.5V8h4"/>',
        'currency'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5v9M14.5 9.7c0-1-1-1.7-2.3-1.7-1.4 0-2.4.8-2.4 1.9 0 2.6 5 1.3 5 3.9 0 1.2-1.2 1.9-2.6 1.9-1.4 0-2.5-.7-2.6-1.8"/>',
        'bar-chart'   => '<path d="M5 20V11M12 20V4M19 20v-7"/><path d="M3 20h18"/>',
        'report'      => '<path d="M6 3.5h9l3 3V20a1 1 0 01-1 1H6a1 1 0 01-1-1V4.5a1 1 0 011-1z"/><path d="M8 11h8M8 14.5h8M8 18h5"/>',
        'sparkles'    => '<path d="M12 4l1.4 3.6L17 9l-3.6 1.4L12 14l-1.4-3.6L7 9l3.6-1.4L12 4z"/><path d="M5 15.5l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8.8-2zM18 14l.6 1.6 1.6.6-1.6.6-.6 1.6-.6-1.6-1.6-.6 1.6-.6.6-1.6z"/>',
        'cog'         => '<circle cx="12" cy="12" r="3"/><path d="M12 3.5v2.3M12 18.2v2.3M20.5 12h-2.3M5.8 12H3.5M17.7 6.3l-1.6 1.6M7.9 16.1l-1.6 1.6M17.7 17.7l-1.6-1.6M7.9 7.9L6.3 6.3"/>',
        'plug'        => '<path d="M9 3v5M15 3v5M6.5 8h11l-.6 5A5 5 0 0112 17.5 5 5 0 016.6 13z"/><path d="M12 17.5V21"/>',
        'shield'      => '<path d="M12 3.5l7 2.6v5.4c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6.1l7-2.6z"/><path d="M9 12l2 2 4-4.2"/>',
        'help'        => '<circle cx="12" cy="12" r="8.5"/><path d="M9.7 9.3a2.3 2.3 0 114 1.5c-.7.6-1.7 1.1-1.7 2.3"/><circle cx="12" cy="16.6" r=".2" fill="currentColor"/>',
        'logout'      => '<path d="M15 16.5V19a1.5 1.5 0 01-1.5 1.5h-8A1.5 1.5 0 014 19V5a1.5 1.5 0 011.5-1.5h8A1.5 1.5 0 0115 5v2.5"/><path d="M9.5 12h11M17 8.5l3.5 3.5-3.5 3.5"/>',
        'search'      => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/>',
        'bell'        => '<path d="M6 9a6 6 0 1112 0c0 4 1.2 5.5 1.5 6H4.5C4.8 14.5 6 13 6 9z"/><path d="M10 19a2 2 0 004 0"/>',
        'plus'        => '<path d="M12 5v14M5 12h14"/>',
        'check'       => '<path d="M4 12.5l5 5L20 6.5"/>',
        'mail'        => '<rect x="3.5" y="5.5" width="17" height="13" rx="1.5"/><path d="M4.5 6.5l7.5 6 7.5-6"/>',
        'phone'       => '<path d="M6 4.5h2.5l1.3 4-2 1.5a12 12 0 006.2 6.2l1.5-2 4 1.3V18a1.5 1.5 0 01-1.6 1.5A15 15 0 014.5 6.1 1.5 1.5 0 016 4.5z"/>',
        'whatsapp'    => '<circle cx="12" cy="12" r="8.5"/><path d="M9 9.5c.2 3 2.5 5.3 5.5 5.5" /><path d="M9 9.5c0-.6.6-1.5 1-1.5s.8 1 .8 1.4-.5.8-.3 1.3c.3.7 1 1.3 1.7 1.6.5.2 1-.3 1.4-.3s1.4.5 1.4.9-1 1.4-1.6 1.4c-1 0-4-1.2-4.4-4.8z"/>',
        'x-circle'    => '<circle cx="12" cy="12" r="8.5"/><path d="M9.5 9.5l5 5M14.5 9.5l-5 5"/>',
        'dots'        => '<circle cx="6" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/><circle cx="18" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
    ];

    $inner = $shapes[$name] ?? $shapes['grid'];
    return "<svg {$common}>{$inner}</svg>";
}
