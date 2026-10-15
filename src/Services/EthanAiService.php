<?php

declare(strict_types=1);

final class EthanAiException extends RuntimeException
{
}

final class EthanAiService
{
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

    public function __construct(private array $config, ?callable $transport = null)
    {
        $this->transport = $transport;
    }

    public function isConfigured(): bool
    {
        return trim((string)($this->config['api_key'] ?? '')) !== '';
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

        $response = $this->request([
            'model' => $this->model(),
            'store' => false,
            'instructions' => self::BASE_INSTRUCTIONS . <<<'PROMPT'

Responda como apoio ao técnico, não como substituto da validação profissional.
Apresente passos numerados quando houver procedimento. Inclua comandos somente quando forem seguros e explique o efeito deles.
Se a pergunta não trouxer dados suficientes, faça no máximo três perguntas objetivas antes de sugerir uma conclusão.
PROMPT,
            'input' => $context . "PERGUNTA DO TÉCNICO:\n" . $question,
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

        $endpoint = (string)($this->config['endpoint'] ?? 'https://api.openai.com/v1/responses');
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . (string)$this->config['api_key'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => max(15, min(90, (int)($this->config['timeout_seconds'] ?? 45))),
        ]);

        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $curlError !== '') {
            throw new EthanAiException('Não foi possível conectar ao serviço de IA. Tente novamente.');
        }

        $data = json_decode((string)$raw, true);
        if (!is_array($data)) {
            throw new EthanAiException('O serviço de IA retornou uma resposta inválida.');
        }
        if ($status < 200 || $status >= 300) {
            error_log('Falha na API de IA (' . $status . '): ' . mb_substr((string)$raw, 0, 1000));
            $message = $status === 429
                ? 'O limite temporário da IA foi atingido. Tente novamente em alguns minutos.'
                : 'O serviço de IA não conseguiu concluir a solicitação.';
            throw new EthanAiException($message);
        }

        return $data;
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
