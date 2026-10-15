<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Services/EthanAiService.php';

$responses = [
    [
        'output' => [[
            'content' => [[
                'type' => 'output_text',
                'text' => json_encode([
                    'categoria' => 'rede',
                    'prioridade' => 'alta',
                    'possivel_causa' => 'Falha de conectividade',
                    'diagnostico_sugerido' => 'Validar enlace e configuração IP.',
                    'proximos_passos' => ['Testar cabo', 'Validar gateway'],
                    'perguntas_ao_usuario' => ['Outros equipamentos foram afetados?'],
                    'alertas' => [],
                ], JSON_UNESCAPED_UNICODE),
            ]],
        ]],
    ],
    ['output' => [['content' => [['type' => 'output_text', 'text' => 'Relatório técnico de teste.']]]]],
    ['output' => [['content' => [['type' => 'output_text', 'text' => 'Resposta de suporte de teste.']]]]],
];
$captured = [];
$transport = static function (array $payload) use (&$responses, &$captured): array {
    $captured[] = $payload;
    return array_shift($responses);
};

$service = new EthanAiService(['api_key' => 'test-key', 'model' => 'modelo-teste'], $transport);
assert($service->isConfigured());
$analysis = $service->analyzeTicket('Todos os computadores perderam acesso à internet após a troca do roteador.');
assert($analysis['categoria'] === 'rede');
assert($analysis['prioridade'] === 'alta');
assert(($captured[0]['store'] ?? true) === false);
assert(($captured[0]['text']['format']['type'] ?? '') === 'json_schema');

$report = $service->generateClosureReport('Computador não iniciava.', 'Fonte substituída e inicialização validada.', 'Equipamento iniciou normalmente.');
assert($report === 'Relatório técnico de teste.');

$answer = $service->answerSupportQuestion('Como validar a conectividade?', []);
assert($answer === 'Resposta de suporte de teste.');
echo "Ethan IA: OK\n";
