CREATE SCHEMA IF NOT EXISTS public;

CREATE TABLE IF NOT EXISTS public.app_collections (
    name VARCHAR(120) PRIMARY KEY,
    payload JSONB NOT NULL DEFAULT '[]'::jsonb,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE public.app_collections IS 'Colecoes serializadas usadas pelo runtime PHP puro do SexyLua.';
