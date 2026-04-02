# SexyLua - Plataforma de Criadores, Assinaturas e Lives

SexyLua e uma plataforma premium para criadores, assinantes e operacao administrativa, com assinaturas, mensagens privadas, microconteudos pagos, lives, carteira em LuaCoins e recarga via PIX.

## Visao geral

- area publica com home, explorar, perfil do criador, lives e catalogo
- area do assinante com assinaturas, favoritos, mensagens, carteira e configuracoes
- area do criador com conteudos, membros, microconteudos, lives, carteira e minha pagina
- area administrativa com usuarios, moderacao, financeiro, operacoes, configuracoes e SEO

## Stack

- PHP 8.3+
- HTML server-side com templates em `templates/`
- JavaScript pontual para interacoes de interface
- armazenamento em JSON ou PostgreSQL
- SyncPay para recarga PIX
- MediaMTX para ingest RTMP e entrega HLS das lives

## Estrutura principal

- `public/` front controller, assets e uploads publicos
- `src/` controladores, servicos, suporte e regra de negocio
- `templates/` paginas e partials
- `routes/` definicao de rotas
- `storage/` dados locais e ferramentas auxiliares
- `scripts/` startup local, reset e migracoes
- `documentação/` documentacao navegavel para cliente e operacao

## Recursos do projeto

- cadastro com documento, maioridade minima e verificacao administrativa
- assinaturas recorrentes e catalogo protegido por acesso
- chat privado com anexo, bloqueio por plano e microconteudo pago
- carteira interna em LuaCoins com historico detalhado
- recarga PIX via SyncPay com confirmacao por status e webhook
- lives com agenda, estudio e transmissao via OBS + MediaMTX
- painel administrativo com controle financeiro, operacoes e SEO

## Ambiente local

1. Copie `.env.example` para `.env`
2. Ajuste as variaveis necessarias do ambiente
3. Inicie o servidor local:

```powershell
php -S 127.0.0.1:8088 -t public public/index.php
```

4. Se for testar lives com ingest local, inicie tambem o MediaMTX:

```powershell
.\scripts\start-mediamtx.ps1
```

## Variaveis importantes

```ini
SEXYLUA_STORAGE_DRIVER=json
SEXYLUA_BASE_URL=
SEXYLUA_TIMEZONE=America/Sao_Paulo
SEXYLUA_LIVE_DRIVER=mediamtx
SEXYLUA_MEDIAMTX_API_URL=http://127.0.0.1:9997
SEXYLUA_MEDIAMTX_RTMP_URL=rtmp://127.0.0.1:1935/live
SEXYLUA_MEDIAMTX_HLS_URL=http://127.0.0.1:8888
SEXYLUA_SYNCPAY_BASE_URL=https://api.syncpayments.com.br
```

## Persistencia

- `json`: usa colecoes em `storage/data`
- `postgresql`: usa as variaveis `SEXYLUA_PG_*`

## Lives

Preset recomendado para transmissao:

- resolucao `854x480`
- video `800 kbps`
- `30 fps`
- `GOP 2s`
- audio `AAC 96 kbps`

Observacao:

- replay automatico esta desabilitado por padrao para reduzir impacto de disco e processamento

## Pagamentos

- recargas de LuaCoins usam SyncPay via PIX
- as chaves ficam no painel administrativo
- o saldo so entra apos aprovacao da transacao
- webhook padrao de producao: `/webhook/syncpay`

## Documentacao para entrega

A pasta `documentação/` contem:

- `index.html`
- `assinante.html`
- `criador.html`
- `admin.html`
- `tecnica.html`

Esses arquivos podem ser abertos diretamente pelo navegador e foram pensados para acompanhar o ZIP da raiz do projeto.

## Producao

Stack atual:

- HestiaCP
- Nginx proxy + Apache
- PHP 8.3
- MediaMTX para live

Layout de producao:

- web root publica em `public_html`
- aplicacao em `private/app`

## Boas praticas

- nao versionar `.env` real nem credenciais
- nao expor uploads privados por URL publica solta
- validar SyncPay apos trocar credenciais ou ambiente
- confirmar a porta `1935` liberada para ingest RTMP

## Licenciamento e uso

Projeto privado/publicado para operacao da plataforma SexyLua. Ajustes de uso e distribuicao devem seguir o acordo comercial do cliente.
