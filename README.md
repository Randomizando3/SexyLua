# SexyLua

Aplicacao em PHP puro, JS, HTML e CSS, baseada no prototipo visual da pasta `Visual`.

## Rodar localmente

1. Abra o projeto na pasta raiz.
2. Rode:

```powershell
php -S 127.0.0.1:8088 -t public
```

3. Acesse:

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
- `database/postgresql-schema.sql`: schema equivalente para futura migracao ao PostgreSQL

## Resetar dados demo

```powershell
php scripts/reset_data.php
```

## Observacao sobre banco

O runtime atual usa JSON porque o PHP desta maquina nao possui `pdo_pgsql` ativo. O schema PostgreSQL ja foi incluido para migracao futura quando o driver estiver disponivel no servidor.
