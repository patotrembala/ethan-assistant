<?php

declare(strict_types=1);

function isValidCnpj(string $value): bool
{
    $cnpj = preg_replace('/\D/', '', $value);
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    foreach ([12, 13] as $length) {
        $sum = 0;
        $weight = $length - 7;
        for ($i = 0; $i < $length; $i++) {
            $sum += (int)$cnpj[$i] * $weight--;
            if ($weight < 2) {
                $weight = 9;
            }
        }
        $digit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        if ((int)$cnpj[$length] !== $digit) {
            return false;
        }
    }

    return true;
}

function validateCliente(array $input): array
{
    $data = [
        'razao_social' => trim((string)($input['razao_social'] ?? '')),
        'cnpj' => preg_replace('/\D/', '', (string)($input['cnpj'] ?? '')),
        'endereco' => trim((string)($input['endereco'] ?? '')),
        'email' => strtolower(trim((string)($input['email'] ?? ''))),
        'telefone' => trim((string)($input['telefone'] ?? '')),
        'ativo' => isset($input['ativo']) ? 1 : 0
    ];
    $errors = [];

    if (mb_strlen($data['razao_social']) < 3) {
        $errors['razao_social'] = 'Informe uma razão social válida.';
    }
    if (!isValidCnpj($data['cnpj'])) {
        $errors['cnpj'] = 'Informe um CNPJ válido.';
    }
    if (mb_strlen($data['endereco']) < 8) {
        $errors['endereco'] = 'Informe o endereço completo.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Informe um e-mail válido.';
    }
    if (strlen(preg_replace('/\D/', '', $data['telefone'])) < 10) {
        $errors['telefone'] = 'Informe um telefone válido com DDD.';
    }

    return [$data, $errors];
}
