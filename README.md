# SexyLua

Aplicacao em PHP puro, JS, HTML e CSS, baseada no prototipo visual da pasta `Visual`.

## Rodar localmente

1. Abra o projeto na pasta raiz.
2. Opcional: copie `.env.example` para `.env` se quiser trocar o driver para PostgreSQL.
3. Rode:

```powershell
php -S 127.0.0.1:8088 -t public
```

4. Acesse:

`http://127.0.0.1:8088`

## Contas demo

- Admin: `admin@sexylua.local` / `admin123`
- Criador: `maria@sexylua.local` / `creator123`
- Assinante: `assinante@sexylua.local` / `subscriber123`

## Estrutura

- `public/`: front controller, assets e `.htaccess` para Apache
- `src/`: core, controladores e repositorio
- `templates/`: layouts e telas
- `storage/data/`: persistencia local em JSON
- `database/postgresql-schema.sql`: schema usado pelo driver PostgreSQL

## Resetar dados demo

```powershell
php scripts/reset_data.php
```

## Migrar JSON para PostgreSQL

1. Ative no `.env`:

```ini
SEXYLUA_STORAGE_DRIVER=postgresql
SEXYLUA_PG_DSN=pgsql:host=127.0.0.1;port=5432;dbname=sexylua
SEXYLUA_PG_USER=postgres
SEXYLUA_PG_PASSWORD=sua_senha
```

2. Rode a migracao:

```powershell
php scripts/migrate_json_to_postgres.php
```

3. Teste o driver:

```powershell
php scripts/postgres_smoke_test.php
```
