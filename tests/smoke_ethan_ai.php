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

$oauthFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ethan-oauth-test-' . bin2hex(random_bytes(6)) . '.json';
file_put_contents($oauthFile, json_encode([
    'client_id' => 'oaiapp_teste',
    'access_token' => 'token-de-teste',
    'refresh_token' => 'refresh-de-teste',
    'expires_at' => time() + 3600,
    'scopes' => ['chatgpt.tokens.use.direct'],
    'model' => 'modelo-chatgpt-teste',
], JSON_THROW_ON_ERROR));
try {
    $oauthService = new EthanAiService([], null, $oauthFile);
    assert($oauthService->isConfigured());
    assert($oauthService->usesChatGptPlan());

    $parseStream = Closure::bind(
        fn(string $raw, int $status): array => $this->parseStreamingResponse($raw, $status),
        $oauthService,
        EthanAiService::class
    );
    $stream = "data: {\"type\":\"response.output_text.delta\",\"delta\":\"Resposta via plano\"}\n\n"
        . "data: {\"type\":\"response.completed\",\"response\":{}}\n\n";
    $streamResponse = $parseStream($stream, 200);
    assert($streamResponse['output'][0]['content'][0]['text'] === 'Resposta via plano');

    $incompleteStream = "data: {\"type\":\"response.output_text.delta\",\"delta\":\"Resposta parcial\"}\n\n"
        . "data: {\"type\":\"response.incomplete\",\"response\":{\"incomplete_details\":{\"reason\":\"server_error\"}}}\n\n";
    try {
        $parseStream($incompleteStream, 200);
        assert(false, 'Um stream incompleto deve solicitar uma nova tentativa controlada.');
    } catch (EthanAiException $exception) {
        assert(str_contains($exception->getMessage(), 'oscilou'));
    }

    $usageLimitStream = "data: {\"type\":\"error\",\"error\":{\"code\":\"subscription_sharing_usage_limit_exceeded\"}}\n\n";
    try {
        $parseStream($usageLimitStream, 200);
        assert(false, 'O limite do plano deve ser informado sem nova tentativa.');
    } catch (EthanAiException $exception) {
        assert(str_contains($exception->getMessage(), 'limite temporário'));
    }
} finally {
    @unlink($oauthFile);
}
echo "Ethan IA: OK\n";
