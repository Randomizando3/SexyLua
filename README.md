# SexyLua

Aplicação em PHP puro com HTML, CSS e JavaScript, usando o protótipo visual como base e mantendo compatibilidade com Apache.

## Requisitos

- PHP 8.3+
- Extensão PDO PostgreSQL opcional
- Apache ou Nginx + Apache

## Estrutura

- `public/`: front controller, assets e `.htaccess`
- `src/`: core, controladores, serviços e repositório
- `templates/`: telas e layouts
- `storage/`: dados locais, logs e temporários
- `database/`: scripts de apoio para PostgreSQL

## Ambiente local

1. Copie `.env.example` para `.env`
2. Ajuste o driver de armazenamento se necessário
3. Rode:

```powershell
php -S 127.0.0.1:8088 -t public public/index.php
```

4. Acesse:

- `http://127.0.0.1:8088`

## Variáveis principais

```ini
SEXYLUA_STORAGE_DRIVER=json
SEXYLUA_BASE_URL=
SEXYLUA_TIMEZONE=America/Sao_Paulo
```

## Persistência

- `json`: usa `storage/data`
- `postgresql`: usa as variáveis `SEXYLUA_PG_*`

## Pagamentos

As recargas de LuaCoins usam SyncPay via PIX. As chaves operacionais são configuradas no painel administrativo.

## Observações

- Arquivos sensíveis não devem ser versionados
- Uploads públicos ficam em `public/uploads`
- Dados locais em JSON ficam fora do repositório
