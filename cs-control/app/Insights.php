<?php
declare(strict_types=1);

// Motor de insights do painel "Premium4 IA". Importante: isto NÃO chama nenhuma
// IA externa. É um conjunto de regras fixas que lê os dados reais do banco e
// monta frases prontas — sem custo, sem chave de API, sem dependência externa.
class Insights
{
    public static function greeting(): string
    {
        $hour = (int) date('G');
        if ($hour < 12) {
            return 'Bom dia';
        }
        if ($hour < 18) {
            return 'Boa tarde';
        }
        return 'Boa noite';
    }

    public static function generate(): array
    {
        $db = Database::get();
        $messages = [];

        $alertCount = Alerts::countOpen();
        if ($alertCount > 0) {
            $plural = $alertCount === 1 ? 'ponto de atenção' : 'pontos de atenção';
            $messages[] = "Analisei sua carteira e encontrei {$alertCount} {$plural} hoje.";
        } else {
            $messages[] = 'Analisei sua carteira: nenhum alerta em aberto agora. Bom trabalho!';
        }

        // Destaca o alerta mais urgente (vermelho primeiro)
        $topAlert = $db->query("SELECT * FROM alerts WHERE status = 'aberto'
                                 ORDER BY FIELD(level, 'vermelho', 'amarelo', 'azul'), created_at DESC
                                 LIMIT 1")->fetch();
        if ($topAlert) {
            if ($topAlert['type'] === 'cancelamento') {
                $messages[] = "O cliente {$topAlert['client']} está com cancelamento em andamento. Sugiro contato hoje.";
            } elseif ($topAlert['type'] === 'sem_retorno') {
                $messages[] = trim($topAlert['message']) . '. Vale um follow-up.';
            } else {
                $messages[] = trim($topAlert['message']) . '.';
            }
        }

        // Contratos pendentes e ARR associado
        $contratos = $db->query("SELECT COUNT(*) AS total, COALESCE(SUM(arr_impact), 0) AS arr
                                  FROM activities
                                  WHERE type = 'contrato' AND status != 'Concluído'")->fetch();
        if ((int) $contratos['total'] > 0) {
            $plural = (int) $contratos['total'] === 1 ? 'contrato aguardando assinatura' : 'contratos aguardando assinatura';
            $arrTexto = $contratos['arr'] > 0 ? ' que impactam ' . money_brl((float) $contratos['arr']) . ' de ARR' : '';
            $messages[] = "Há {$contratos['total']} {$plural}{$arrTexto}.";
        }

        return $messages;
    }
}
