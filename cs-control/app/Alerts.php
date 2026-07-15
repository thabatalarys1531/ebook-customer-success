<?php
declare(strict_types=1);

class Alerts
{
    // Cliente sem nenhuma atividade registrada há mais de X dias entra em alerta.
    private const DIAS_SEM_RETORNO = 7;

    // Roda antes de qualquer leitura de alertas: cria os alertas automáticos que
    // ainda não existem, sem duplicar os que já estão abertos.
    public static function sync(): void
    {
        $db = Database::get();

        // Regra 1: prioridade Alta + status diferente de Concluído
        $rows = $db->query("
            SELECT a.id, a.client, a.type
            FROM activities a
            WHERE a.priority = 'Alta' AND a.status != 'Concluído'
              AND NOT EXISTS (
                  SELECT 1 FROM alerts al
                  WHERE al.activity_id = a.id AND al.status = 'aberto'
              )
        ")->fetchAll();

        foreach ($rows as $row) {
            $isCancelamento = $row['type'] === 'cancelamento';
            $type    = $isCancelamento ? 'cancelamento' : 'prioridade_alta';
            $level   = $isCancelamento ? 'vermelho' : 'amarelo';
            $message = $isCancelamento
                ? "Cancelamento em andamento: {$row['client']}"
                : "Atividade de prioridade alta pendente: {$row['client']}";

            $insert = $db->prepare("INSERT INTO alerts (activity_id, client, type, message, level, status)
                                     VALUES (?, ?, ?, ?, ?, 'aberto')");
            $insert->execute([$row['id'], $row['client'], $type, $message, $level]);
        }

        // Regra 2: cliente sem retorno há mais de X dias
        $stmt = $db->prepare("
            SELECT client, MAX(created_at) AS last_activity
            FROM activities
            GROUP BY client
            HAVING last_activity < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([self::DIAS_SEM_RETORNO]);

        foreach ($stmt->fetchAll() as $row) {
            $exists = $db->prepare("SELECT COUNT(*) FROM alerts
                                     WHERE client = ? AND type = 'sem_retorno' AND status = 'aberto'");
            $exists->execute([$row['client']]);

            if ((int) $exists->fetchColumn() === 0) {
                $dias = (int) floor((time() - strtotime($row['last_activity'])) / 86400);
                $message = "Sem retorno de {$row['client']} há {$dias} dias";

                $insert = $db->prepare("INSERT INTO alerts (client, type, message, level, status)
                                         VALUES (?, 'sem_retorno', ?, 'amarelo', 'aberto')");
                $insert->execute([$row['client'], $message]);
            }
        }
    }

    public static function openAlerts(int $limit = 10): array
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM alerts WHERE status = 'aberto'
                               ORDER BY FIELD(level, 'vermelho', 'amarelo', 'azul'), created_at DESC
                               LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countOpen(): int
    {
        $db = Database::get();
        return (int) $db->query("SELECT COUNT(*) FROM alerts WHERE status = 'aberto'")->fetchColumn();
    }

    public static function resolve(int $id): void
    {
        $db = Database::get();
        $stmt = $db->prepare("UPDATE alerts SET status = 'resolvido', resolved_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
}
