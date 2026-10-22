import { createHash, createPublicKey, randomBytes, randomUUID, verify } from 'node:crypto';
import { createServer } from 'node:http';
import { readFile, rename, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(dirname(fileURLToPath(import.meta.url)));
const configDir = join(root, 'config');
const credentialFile = join(configDir, 'chatgpt.oauth.local.json');
const registrationFile = join(configDir, 'chatgpt.registration.local.json');
const hostFile = join(configDir, 'chatgpt.host.local.txt');
const callback = 'http://127.0.0.1:1455/auth/callback';
const resource = 'https://api.openai.com/v1';
const authorizeEndpoint = 'https://auth.openai.com/api/accounts/authorize';
const tokenEndpoint = 'https://auth.openai.com/api/accounts/oauth/token';
const jwksEndpoint = 'https://auth.openai.com/.well-known/jwks.json';
const scopes = 'openid profile email offline_access resource.invoke chatgpt.tokens.use.direct';
let transaction;

const server = createServer(async (request, response) => {
  try {
    const url = new URL(request.url ?? '/', 'http://127.0.0.1:1455');
    if (url.pathname === '/start') return await start(response);
    if (url.pathname === '/auth/callback') return await finish(url, response);
    return page(response, 200, 'Conectar o Ethan ao ChatGPT', 'Autorize sua conta pelo fluxo oficial da OpenAI.', '<a class="button" href="/start">Continuar com ChatGPT</a>');
  } catch (error) {
    const status = Number.isInteger(error?.status) ? error.status : 400;
    const code = typeof error?.code === 'string' ? ` (${escapeHtml(error.code)})` : '';
    return page(response, 400, 'Não foi possível conectar', `${escapeHtml(error?.message ?? 'Falha inesperada.')}${code}`, '<a class="button secondary" href="/start">Tentar novamente</a>');
  }
});

server.listen(1455, '127.0.0.1', () => {
  process.stdout.write('Conector do Ethan pronto em http://127.0.0.1:1455/start\n');
});

async function start(response) {
  const registration = await readJson(registrationFile);
  const clientId = registration?.client_id || 'dynamic_agent_client';
  const verifier = base64url(randomBytes(32));
  const state = base64url(randomBytes(32));
  const nonce = base64url(randomBytes(32));
  const hostId = await getHostId();
  transaction = { clientId, verifier, state, nonce, createdAt: Date.now() };

  const authorization = new URL(authorizeEndpoint);
  authorization.search = new URLSearchParams({
    client_id: clientId,
    response_type: 'code',
    redirect_uri: callback,
    scope: scopes,
    resource,
    state,
    nonce,
    code_challenge_method: 'S256',
    code_challenge: createHash('sha256').update(verifier).digest('base64url'),
    ext_agent_host_id: hostId,
  }).toString();
  if (clientId === 'dynamic_agent_client') authorization.searchParams.set('agent_name_hint', 'Ethan Assistant');

  response.writeHead(302, { location: authorization.toString(), 'cache-control': 'no-store' });
  response.end();
}

async function finish(url, response) {
  if (!transaction || Date.now() - transaction.createdAt > 600_000) throw new Error('A tentativa expirou. Inicie novamente.');
  if (!url.searchParams.get('state') || url.searchParams.get('state') !== transaction.state) throw new Error('A validação de segurança da autorização falhou.');
  if (url.searchParams.get('error')) throw new Error('A autorização foi cancelada ou não está disponível.');

  const code = url.searchParams.get('code');
  const returnedClientId = url.searchParams.get('client_id');
  const issuedClientId = transaction.clientId === 'dynamic_agent_client' ? returnedClientId : transaction.clientId;
  if (!code || !issuedClientId || issuedClientId === 'dynamic_agent_client') throw new Error('A OpenAI não retornou os dados necessários.');
  if (transaction.clientId !== 'dynamic_agent_client' && returnedClientId && returnedClientId !== transaction.clientId) throw new Error('A conta retornada não corresponde ao registro selecionado.');

  await atomicJson(registrationFile, { client_id: issuedClientId, saved_at: new Date().toISOString() });
  const token = await requestJson(tokenEndpoint, {
    method: 'POST',
    headers: { 'content-type': 'application/x-www-form-urlencoded', accept: 'application/json' },
    body: new URLSearchParams({
      grant_type: 'authorization_code',
      client_id: issuedClientId,
      code,
      code_verifier: transaction.verifier,
      redirect_uri: callback,
      resource,
    }),
  });
  for (const field of ['access_token', 'refresh_token', 'id_token', 'scope']) {
    if (typeof token[field] !== 'string' || !token[field]) throw new Error(`A OpenAI não retornou ${field}.`);
  }
  const grantedScopes = token.scope.split(/\s+/).filter(Boolean);
  if (!grantedScopes.includes('chatgpt.tokens.use.direct')) throw new Error('A conta não autorizou o uso do plano ChatGPT.');
  const claims = await validateIdToken(token.id_token, issuedClientId, transaction.nonce);

  let model = 'gpt-6.1-sol';
  let catalogNotice = '';
  try {
    const catalog = await requestJson('https://api.openai.com/v1/models', { headers: { authorization: `Bearer ${token.access_token}`, accept: 'application/json' } });
    const available = (catalog.models ?? catalog.data ?? []).filter(item => (item.visibility ?? 'list') === 'list').map(item => item.slug ?? item.id).filter(Boolean);
    const preferred = ['gpt-6.1-sol', 'gpt-6-sol', 'gpt-5.6-sol', 'gpt-5.6-luna'];
    model = preferred.find(candidate => available.includes(candidate)) ?? available[0] ?? model;
  } catch {
    catalogNotice = ' O catálogo não estava disponível; será usado o modelo oficial padrão da prévia.';
  }

  const now = Math.floor(Date.now() / 1000);
  await atomicJson(credentialFile, {
    email: typeof claims.email === 'string' ? claims.email : '',
    issuer: 'https://auth.openai.com',
    subject: claims.sub,
    client_id: issuedClientId,
    ext_agent_host_id: await getHostId(),
    id_token: token.id_token,
    access_token: token.access_token,
    refresh_token: token.refresh_token,
    token_type: token.token_type ?? 'Bearer',
    expires_in: Number(token.expires_in ?? 3600),
    expires_at: now + Number(token.expires_in ?? 3600),
    earliest_refresh_at: token.earliest_refresh_at ?? null,
    scopes: grantedScopes,
    model,
    saved_at: new Date().toISOString(),
  });
  transaction = undefined;
  return page(response, 200, 'Ethan conectado ao ChatGPT', `A autorização foi validada e guardada fora do Git. Modelo selecionado: ${escapeHtml(model)}.${catalogNotice}`, '<strong>Você já pode fechar esta aba.</strong>');
}

async function validateIdToken(jwt, clientId, nonce) {
  const parts = jwt.split('.');
  if (parts.length !== 3) throw new Error('O token de identidade é inválido.');
  const header = JSON.parse(Buffer.from(parts[0], 'base64url').toString('utf8'));
  const claims = JSON.parse(Buffer.from(parts[1], 'base64url').toString('utf8'));
  if (header.alg !== 'RS256' || !header.kid) throw new Error('A assinatura recebida não é suportada.');
  const jwks = await requestJson(jwksEndpoint, { headers: { accept: 'application/json' } });
  const jwk = jwks.keys?.find(key => key.kid === header.kid);
  if (!jwk) throw new Error('A chave pública da OpenAI não foi encontrada.');
  const valid = verify('RSA-SHA256', Buffer.from(`${parts[0]}.${parts[1]}`), createPublicKey({ key: jwk, format: 'jwk' }), Buffer.from(parts[2], 'base64url'));
  const audiences = Array.isArray(claims.aud) ? claims.aud : [claims.aud];
  if (!valid || claims.iss !== 'https://auth.openai.com' || !audiences.includes(clientId) || claims.exp <= Math.floor(Date.now() / 1000) || claims.nonce !== nonce || !claims.sub) {
    throw new Error('A identidade retornada não passou na validação.');
  }
  return claims;
}

async function requestJson(url, init = {}) {
  let response;
  try {
    response = await fetch(url, { ...init, redirect: 'error', signal: AbortSignal.timeout(45_000) });
  } catch {
    throw new Error('Não foi possível conectar ao serviço da OpenAI.');
  }
  let data;
  try { data = await response.json(); } catch { data = undefined; }
  if (!response.ok) {
    const error = new Error(data?.error_description ?? data?.error?.message ?? 'A OpenAI recusou a solicitação.');
    error.status = response.status;
    error.code = typeof data?.error === 'string' ? data.error : data?.error?.code;
    throw error;
  }
  if (!data || typeof data !== 'object' || Array.isArray(data)) throw new Error('A OpenAI retornou uma resposta inesperada.');
  return data;
}

async function getHostId() {
  try {
    const existing = (await readFile(hostFile, 'utf8')).trim();
    if (existing) return existing;
  } catch {}
  const hostId = `urn:uuid:${randomUUID()}`;
  await atomicText(hostFile, `${hostId}\n`);
  return hostId;
}

async function readJson(path) {
  try { return JSON.parse(await readFile(path, 'utf8')); } catch { return undefined; }
}

async function atomicJson(path, value) {
  await atomicText(path, `${JSON.stringify(value, null, 2)}\n`);
}

async function atomicText(path, value) {
  const temporary = `${path}.tmp-${randomBytes(6).toString('hex')}`;
  await writeFile(temporary, value, { encoding: 'utf8', mode: 0o600, flag: 'wx' });
  await rename(temporary, path);
}

function base64url(value) { return Buffer.from(value).toString('base64url'); }
function escapeHtml(value) { return String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char]); }
function page(response, status, title, message, action) {
  response.writeHead(status, { 'content-type': 'text/html; charset=utf-8', 'cache-control': 'no-store', 'x-content-type-options': 'nosniff' });
  response.end(`<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${escapeHtml(title)}</title><style>body{margin:0;font-family:system-ui;background:#071525;color:#eef7ff;display:grid;place-items:center;min-height:100vh}.card{width:min(520px,calc(100% - 48px));padding:42px;border:1px solid #22425f;border-radius:24px;background:#0b2034;box-shadow:0 24px 70px #0007}h1{margin:0 0 12px;font-size:30px}p{color:#acc5d9;line-height:1.6}.button{display:inline-block;margin-top:18px;padding:13px 20px;border-radius:12px;background:#2f7df4;color:white;text-decoration:none;font-weight:700}.secondary{background:#173b5a}</style><body><main class="card"><div style="color:#59c9ff;font-weight:800;margin-bottom:18px">ETHAN ASSISTANT</div><h1>${escapeHtml(title)}</h1><p>${message}</p>${action}</main></body></html>`);
}
