<?php
/**
 * Ethan Assistant - Funcoes Helper Globais
 */

if (!function_exists('statusBadgeClass')) {
    /**
     * Converte o nome de status de qualquer entidade (OS, Chamado, Pendencia)
     * para a classe CSS padronizada do badge.
     */
    function statusBadgeClass(?string $status): string
    {
        if (empty($status)) {
            return 'badge-status-aberta';
        }

        $normalized = strtolower(trim($status));
        // Mapeamento simples e sem acentos
        $normalized = str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç', ' '],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c', ''],
            $normalized
        );

        return match ($normalized) {
            'emandamento', 'andamento' => 'badge-status-andamento',
            'ematendimento', 'atendimento' => 'badge-status-atendimento',
            'emdiagnostico', 'diagnostico' => 'badge-status-diagnostico',
            'aguardandoaprovacao', 'aguardando' => 'badge-status-aguardando',
            'concluida', 'concluido' => 'badge-status-concluida',
            'cancelada', 'cancelado' => 'badge-status-cancelada',
            'pendente' => 'badge-status-pendente',
            'aberta', 'aberto' => 'badge-status-aberta',
            default => 'badge-status-' . $normalized
        };
    }
}

if (!function_exists('renderStatusBadge')) {
    /**
     * Renderiza o elemento HTML completo do badge de status.
     */
    function renderStatusBadge(?string $status): string
    {
        $statusStr = $status ?? 'Aberta';
        $class = statusBadgeClass($statusStr);
        return '<span class="badge ' . htmlspecialchars($class) . '">' . htmlspecialchars($statusStr) . '</span>';
    }
}
