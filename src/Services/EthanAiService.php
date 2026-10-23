<?php

declare(strict_types=1);

final class EthanAiException extends RuntimeException
{
}

final class EthanAiService
{
    private const TOKEN_ENDPOINT = 'https://auth.openai.com/api/accounts/oauth/token';
    private const API_RESOURCE = 'https://api.openai.com/v1';

    private const BASE_INSTRUCTIONS = <<<'PROMPT'
Você é o Ethan, assistente interno de uma equipe brasileira de suporte de TI.
Responda em português do Brasil, com linguagem técnica clara, objetiva e profissional.
Nunca afirme que um diagnóstico é definitivo sem testes. Diferencie evidência, hipótese e recomendação.
Priorize segurança, preservação de dados, backup e procedimentos reversíveis.
Não solicite nem repita senhas, tokens, chaves, códigos de autenticação ou dados pessoais desnecessários.
Se houver risco de perda de dados, choque elétrico, incêndio, violação de segurança ou indisponibilidade crítica, destaque o risco antes dos demais passos.
PROMPT;

    /** @var null|callable */
    private $transport;

    public function __construct(
        private array $config,
        ?callable $transport = null,
        private ?string $oauthCredentialFile = null
    )
    {
        $this->transport = $transport;
    }

    public function isConfigured(): bool
    {
        return $this->usesChatGptPlan() || trim((string)($this->config['api_key'] ?? '')) !== '';
    }

    public function usesChatGptPlan(): bool
    {
        $credentials = $this->readOAuthCredentials();
        return !empty($credentials['access_token'])
            && !empty($credentials['refresh_token'])
            && !empty($credentials['client_id'])
            && in_array('chatgpt.tokens.use.direct', (array)($credentials['scopes'] ?? []), true);
    }

    public function analyzeTicket(string $description): array
    {
        $description = $this->cleanInput($description, 5000);
        if (mb_strlen($description) < 15) {
            throw new EthanAiException('Descreva o chamado com pelo menos 15 caracteres.');
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'categoria' => ['type' => 'string', 'enum' => ['hardware', 'software', 'rede', 'impressora', 'acesso', 'seguranca', 'backup', 'outro']],
                'prioridade' => ['type' => 'string', 'enum' => ['baixa', 'media', 'alta', 'critica']],
                'possivel_causa' => ['type' => 'string'],
                'diagnostico_sugerido' => ['type' => 'string'],
                'proximos_passos' => ['type' => 'array', 'items' => ['type' => 'string']],
                'perguntas_ao_usuario' => ['type' => 'array', 'items' => ['type' => 'string']],
                'alertas' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['categoria', 'prioridade', 'possivel_causa', 'diagnostico_sugerido', 'proximos_passos', 'perguntas_ao_usuario', 'alertas'],
            'additionalProperties' => false,
        ];

        $response = $this->request([
            'model' => $this->model(),
            'store' => false,
            'instructions' => self::BASE_INSTRUCTIONS . "\nClassifique e analise o chamado. A prioridade deve considerar impacto, urgência e quantidade de usuários afetados.",
            'input' => "Descrição do chamado:\n" . $description,
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'analise_chamado_ethan',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
            'max_output_tokens' => 1200,
        ]);

        $decoded = json_decode($this->extractOutputText($response), true);
        if (!is_array($decoded)) {
            throw new EthanAiException('A IA retornou uma análise em formato inválido. Tente novamente.');
        }

        return $decoded;
    }

    public function generateClosureReport(string $problem, string $actions, string $result = ''): string
    {
        $problem = $this->cleanInput($problem, 4000);
        $actions = $this->cleanInput($actions, 5000);
        $result = $this->cleanInput($result, 2000);
        if (mb_strlen($problem) < 10 || mb_strlen($actions) < 10) {
            throw new EthanAiException('Informe o problema e os procedimentos realizados com pelo menos 10 caracteres cada.');
        }

        $input = "PROBLEMA RELATADO:\n{$problem}\n\nPROCEDIMENTOS REALIZADOS:\n{$actions}";
        if ($result !== '') {
            $input .= "\n\nRESULTADO DOS TESTES:\n{$result}";
        }

        $response = $this->request([
            'model' => $this->model(),
            'store' => false,
            'instructions' => self::BASE_INSTRUCTIONS . <<<'PROMPT'

Redija um relatório técnico de encerramento pronto para anexar ao chamado.
Use exatamente as seções: Resumo do atendimento, Diagnóstico, Procedimentos realizados, Validação e Recomendações.
Não invente peças, testes, datas, resultados ou garantias que não estejam na entrada. Quando faltar algo, declare “não informado”.
PROMPT,
            'input' => $input,
            'max_output_tokens' => 1400,
        ]);

        return $this->extractOutputText($response);
    }

    public function answerSupportQuestion(string $question, array $history = []): string
    {
        $question = $this->cleanInput($question, 3000);
        if (mb_strlen($question) < 5) {
            throw new EthanAiException('Escreva uma pergunta com pelo menos 5 caracteres.');
        }

        $historyLines = [];
        foreach (array_slice($history, -6) as $message) {
            $role = ($message['role'] ?? '') === 'assistant' ? 'Ethan' : 'Técnico';
            $content = $this->cleanInput((string)($message['content'] ?? ''), 1500);
            if ($content !== '') {
                $historyLines[] = $role . ': ' . $content;
            }
        }
        $context = $historyLines ? "CONTEXTO RECENTE:\n" . implode("\n", $historyLines) . "\n\n" : '';
        $isOpeningMessage = count($historyLines) === 0;

        $response = $this->request([
            'model' => $this->model(),
            'store' => false,
            'instructions' => self::BASE_INSTRUCTIONS . <<<'PROMPT'

Você está conduzindo um chamado técnico em formato de conversa.
Responda como um colega experiente de suporte, sem soar como formulário ou laudo automático.
Na primeira mensagem, acolha o relato, indique uma hipótese inicial e faça no máximo três perguntas objetivas para avançar o diagnóstico.
Durante a conversa, considere o histórico, não repita perguntas já respondidas e proponha um teste por vez quando isso tornar o atendimento mais seguro.
Quando houver evidências suficientes, informe de forma natural: categoria, prioridade sugerida, possível causa, diagnóstico e próximos passos.
Apresente passos numerados quando houver procedimento. Inclua comandos somente quando forem seguros e explique o efeito deles.
Quando o técnico confirmar que resolveu, resuma o problema, a solução aplicada e a validação final, pronto para ser usado no encerramento do chamado.
PROMPT,
            'input' => $context . ($isOpeningMessage ? "ABERTURA DO CHAMADO PELO TÉCNICO:\n" : "NOVA MENSAGEM DO TÉCNICO:\n") . $question,
            'max_output_tokens' => 1200,
        ]);

        return $this->extractOutputText($response);
    }

    private function request(array $payload): array
    {
        if (!$this->isConfigured()) {
            throw new EthanAiException('A IA ainda não foi configurada pelo administrador.');
        }

        if ($this->transport !== null) {
            $result = ($this->transport)($payload, $this->config);
            if (!is_array($result)) {
                throw new EthanAiException('Resposta inválida do serviço de IA.');
            }
            return $result;
        }

        if (!function_exists('curl_init')) {
            throw new EthanAiException('A extensão cURL do PHP não está disponível no servidor.');
        }

        $usesChatGptPlan = $this->usesChatGptPlan();
        $accessToken = $usesChatGptPlan
            ? $this->oauthAccessToken()
            : trim((string)($this->config['api_key'] ?? ''));
        if ($usesChatGptPlan) {
            // Obrigatório no fluxo oficial de uso do plano ChatGPT.
            $payload['store'] = false;
            $payload['stream'] = true;
            if (is_string($payload['input'] ?? null)) {
                $payload['input'] = [[
                    'role' => 'user',
                    'content' => $payload['input'],
                ]];
            }
            // Estes campos não são aceitos na prévia de uso do plano.
            unset($payload['max_output_tokens']);
        }

        $result = $this->performRequest($payload, $accessToken, $usesChatGptPlan);
        if ($usesChatGptPlan && $result['status'] === 401) {
            $accessToken = $this->refreshOAuthCredentials(true);
            $result = $this->performRequest($payload, $accessToken, true);
        }

        $raw = $result['raw'];
        $status = $result['status'];
        $curlError = $result['error'];
        if ($raw === false || $curlError !== '') {
            throw new EthanAiException('Não foi possível conectar ao serviço de IA. Tente novamente.');
        }

        if ($usesChatGptPlan) {
            return $this->parseStreamingResponse((string)$raw, $status);
        }

        $data = json_decode((string)$raw, true);
        if (!is_array($data)) {
            throw new EthanAiException('O serviço de IA retornou uma resposta inválida.');
        }
        if ($status < 200 || $status >= 300) {
            error_log('Falha na API de IA. HTTP ' . $status . '.');
            $message = $status === 429
                ? 'O Ethan está temporariamente sem créditos de IA. O administrador precisa conferir o faturamento da API.'
                : 'O serviço de IA não conseguiu concluir a solicitação.';
            throw new EthanAiException($message);
        }

        return $data;
    }

    /** @return array{raw:string|false,status:int,error:string} */
    private function performRequest(array $payload, string $accessToken, bool $stream): array
    {
        $endpoint = (string)($this->config['endpoint'] ?? 'https://api.openai.com/v1/responses');
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
                'Accept: ' . ($stream ? 'text/event-stream' : 'application/json'),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            CURLOPT_CONNECTTIMEOUT => 10,
            // Hospedagens compartilhadas encerram processos PHP longos e podem bloquear o site.
            CURLOPT_TIMEOUT => max(15, min(30, (int)($this->config['timeout_seconds'] ?? 25))),
        ]);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return ['raw' => $raw, 'status' => $status, 'error' => $curlError];
    }

    private function parseStreamingResponse(string $raw, int $status): array
    {
        if ($status < 200 || $status >= 300) {
            error_log('Falha na inferência pelo plano ChatGPT. HTTP ' . $status . '.');
            throw new EthanAiException($status === 429
                ? 'O limite temporário do seu plano ChatGPT foi atingido. Tente novamente mais tarde.'
                : 'O ChatGPT não conseguiu concluir a solicitação.');
        }

        $outputText = '';
        $completedResponse = null;
        $completed = false;
        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            if (!str_starts_with($line, 'data:')) {
                continue;
            }
            $json = trim(substr($line, 5));
            if ($json === '' || $json === '[DONE]') {
                continue;
            }
            $event = json_decode($json, true);
            if (!is_array($event)) {
                continue;
            }
            $type = (string)($event['type'] ?? '');
            if ($type === 'response.output_text.delta') {
                $outputText .= (string)($event['delta'] ?? '');
            } elseif ($type === 'response.completed') {
                $completed = true;
                $completedResponse = is_array($event['response'] ?? null) ? $event['response'] : null;
            } elseif ($type === 'response.failed') {
                $code = (string)($event['response']['error']['code'] ?? '');
                error_log('Falha no stream do ChatGPT. Código: ' . ($code !== '' ? $code : 'não informado') . '.');
                $message = in_array($code, ['subscription_sharing_usage_limit_exceeded', 'subscription_sharing_usage_unavailable'], true)
                    ? 'O limite temporário do seu plano ChatGPT foi atingido. Tente novamente mais tarde.'
                    : 'O ChatGPT não conseguiu concluir a solicitação.';
                throw new EthanAiException($message);
            } elseif ($type === 'response.incomplete') {
                $reason = (string)($event['response']['incomplete_details']['reason'] ?? 'não informado');
                error_log('Stream incompleto do ChatGPT. Motivo: ' . $reason . '.');
                throw new EthanAiException('A conexão com o ChatGPT oscilou. Tente enviar a mensagem novamente.');
            } elseif ($type === 'error') {
                $code = (string)($event['error']['code'] ?? $event['code'] ?? '');
                error_log('Erro no stream do ChatGPT. Código: ' . ($code !== '' ? $code : 'não informado') . '.');
                if (in_array($code, ['subscription_sharing_usage_limit_exceeded', 'subscription_sharing_usage_unavailable'], true)) {
                    throw new EthanAiException('O limite temporário do seu plano ChatGPT foi atingido. Tente novamente mais tarde.');
                }
                throw new EthanAiException('A conexão com o ChatGPT oscilou. Tente enviar a mensagem novamente.');
            }
        }

        if (!$completed) {
            error_log('O stream do ChatGPT terminou sem o evento de conclusão.');
            throw new EthanAiException('A conexão com o ChatGPT oscilou. Tente enviar a mensagem novamente.');
        }
        if ($completedResponse !== null) {
            try {
                $this->extractOutputText($completedResponse);
                return $completedResponse;
            } catch (EthanAiException) {
                // Alguns streams entregam o texto apenas nos eventos delta.
            }
        }
        if (trim($outputText) === '') {
            throw new EthanAiException('A IA não retornou conteúdo. Reformule a solicitação.');
        }
        return ['output' => [['content' => [['type' => 'output_text', 'text' => trim($outputText)]]]]];
    }

    private function oauthAccessToken(): string
    {
        $credentials = $this->readOAuthCredentials();
        $now = time();
        $expiresAt = (int)($credentials['expires_at'] ?? 0);
        $earliestRefresh = $this->refreshTimestamp($credentials['earliest_refresh_at'] ?? null);
        if ($expiresAt <= $now + 120 && !($expiresAt > $now && $earliestRefresh > $now)) {
            return $this->refreshOAuthCredentials();
        }
        return (string)$credentials['access_token'];
    }

    private function refreshOAuthCredentials(bool $force = false): string
    {
        $credentials = $this->readOAuthCredentials();
        if (!$force && (int)($credentials['expires_at'] ?? 0) > time() + 120) {
            return (string)$credentials['access_token'];
        }
        if (!function_exists('curl_init') || !$this->oauthCredentialFile) {
            throw new EthanAiException('Não foi possível renovar a autorização do ChatGPT.');
        }

        $ch = curl_init(self::TOKEN_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'refresh_token',
                'client_id' => (string)($credentials['client_id'] ?? ''),
                'refresh_token' => (string)($credentials['refresh_token'] ?? ''),
                'resource' => self::API_RESOURCE,
            ], '', '&', PHP_QUERY_RFC3986),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
        ]);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        $token = json_decode((string)$raw, true);
        if ($raw === false || $error !== '' || $status < 200 || $status >= 300 || !is_array($token) || empty($token['access_token']) || empty($token['refresh_token'])) {
            error_log('Falha ao renovar autorização do ChatGPT. HTTP ' . $status . '.');
            throw new EthanAiException('A autorização do ChatGPT expirou. O administrador precisa conectá-la novamente.');
        }

        $now = time();
        $credentials['access_token'] = (string)$token['access_token'];
        $credentials['refresh_token'] = (string)$token['refresh_token'];
        $credentials['id_token'] = (string)($token['id_token'] ?? $credentials['id_token'] ?? '');
        $credentials['expires_in'] = (int)($token['expires_in'] ?? 3600);
        $credentials['expires_at'] = $now + $credentials['expires_in'];
        $credentials['earliest_refresh_at'] = $token['earliest_refresh_at'] ?? null;
        $credentials['saved_at'] = gmdate(DATE_ATOM, $now);
        if (!empty($token['scope'])) {
            $credentials['scopes'] = preg_split('/\s+/', trim((string)$token['scope'])) ?: $credentials['scopes'];
        }
        $this->atomicWriteCredentials($credentials);
        return (string)$credentials['access_token'];
    }

    private function readOAuthCredentials(): array
    {
        if (!$this->oauthCredentialFile || !is_file($this->oauthCredentialFile)) {
            return [];
        }
        $decoded = json_decode((string)@file_get_contents($this->oauthCredentialFile), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function refreshTimestamp(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }
        if (is_string($value) && $value !== '') {
            $parsed = strtotime($value);
            return $parsed === false ? 0 : $parsed;
        }
        return 0;
    }

    private function atomicWriteCredentials(array $credentials): void
    {
        $temporary = $this->oauthCredentialFile . '.tmp-' . bin2hex(random_bytes(6));
        $json = json_encode($credentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
        if (file_put_contents($temporary, $json, LOCK_EX) === false || !rename($temporary, $this->oauthCredentialFile)) {
            @unlink($temporary);
            throw new EthanAiException('Não foi possível salvar a renovação da autorização.');
        }
        @chmod($this->oauthCredentialFile, 0600);
    }

    private function extractOutputText(array $response): string
    {
        foreach (($response['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text' && trim((string)($content['text'] ?? '')) !== '') {
                    return trim((string)$content['text']);
                }
            }
        }
        throw new EthanAiException('A IA não retornou conteúdo. Reformule a solicitação.');
    }

    private function model(): string
    {
        if ($this->usesChatGptPlan()) {
            $oauthModel = trim((string)($this->readOAuthCredentials()['model'] ?? ''));
            if ($oauthModel !== '') {
                return $oauthModel;
            }
        }
        return trim((string)($this->config['model'] ?? 'gpt-6-luna')) ?: 'gpt-6-luna';
    }

    private function cleanInput(string $value, int $limit): string
    {
        $value = trim(preg_replace('/\R{3,}/u', "\n\n", $value) ?? $value);
        $value = preg_replace('/\bsk-[A-Za-z0-9_-]{12,}\b/', '[CHAVE REMOVIDA]', $value) ?? $value;
        $value = preg_replace('/\b(Bearer\s+)[A-Za-z0-9._-]{12,}\b/i', '$1[REMOVIDO]', $value) ?? $value;
        return mb_substr($value, 0, $limit);
    }
}
