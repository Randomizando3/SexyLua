# SexyLua - Continuacao do Projeto

## Estado Atual
- Etapa 1 concluida.
- As 19 telas do prototipo estao 1:1 com os HTMLs originais da pasta `Visual`.
- Login e registro foram recriados no mesmo idioma visual das telas publicas.
- Backend base em PHP puro funcionando com JSON local.
- Fluxos ja validados: login, registro, favoritos, assinatura, mensagens, salvar live, saque, configuracoes admin e moderacao.
- O backend entra por baixo do layout com `forms` ocultos, `JS` e `toasts`, sem descaracterizar o HTML aprovado.

## Regras Fixas do Projeto
- Manter o visual 1:1 em relacao aos HTMLs do prototipo.
- Nao redesenhar telas existentes.
- PHP puro, JS, HTML e CSS, sem framework para instalar no servidor.
- Priorizar compatibilidade com Apache.
- Sempre preservar a mesma estrutura visual e apenas encaixar backend e dados reais.
- Quando for dinamizar uma tela, manter a mesma hierarquia DOM, classes e espacamentos do prototipo.

## Etapas Ate Ficar 100%

### Etapa 1 - Base visual e backend inicial
Status: concluida

Entregas:
- 19 telas ligadas ao sistema.
- Login e registro alinhados ao visual.
- Rotas principais funcionando.
- Feedback de sucesso/erro por toast.

### Etapa 2 - Dinamizar conteudo real tela por tela
Status: proxima etapa

Objetivo:
- Trocar os textos, cards, listas e numeros estaticos do prototipo por dados reais do backend, sem alterar o layout.

Subetapas:
1. Publico: home, explorar, perfil e live com dados reais.
2. Assinante: dashboard, assinaturas, favoritos, mensagens e carteira com dados reais.
3. Criador: dashboard, conteudo, assinaturas, live e carteira com dados reais.
4. Admin: dashboard, usuarios, moderacao, financeiro e configuracoes com dados reais.

Criterio de conclusao:
- Todas as 19 telas continuam visualmente iguais, mas agora mostram dados do repositorio em vez de textos fixos do mock.

### Etapa 3 - Acoes completas por tela
Status: pendente

Objetivo:
- Fazer cada botao, formulario e acao importante funcionar de forma completa.

Itens:
1. Criacao e edicao de conteudo do criador.
2. Gestao real de assinaturas e planos.
3. Chat e conversa com atualizacao consistente.
4. Favoritos, salvos, gorjetas e carteira com reflexo visual imediato.
5. Moderacao e gestao de usuarios com impacto real nas telas.

### Etapa 4 - Persistencia definitiva
Status: pendente

Objetivo:
- Migrar do JSON local para banco real.

Preferencia:
1. PostgreSQL como prioridade.
2. So usar outra opcao se houver bloqueio no ambiente.

Itens:
1. Criar camada de conexao real.
2. Migrar schema.
3. Seed equivalente aos dados atuais.
4. Trocar repositorio para operacao em banco.

### Etapa 5 - Midia e uploads
Status: pendente

Objetivo:
- Tornar o sistema pronto para conteudo real.

Itens:
1. Upload de imagem e video.
2. Organizacao de arquivos em disco.
3. Validacao de tipo e tamanho.
4. Persistencia de caminhos no banco.

### Etapa 6 - Seguranca e regras de negocio
Status: pendente

Objetivo:
- Endurecer o sistema para uso real.

Itens:
1. Validacoes mais fortes de entrada.
2. Permissoes por perfil.
3. Protecao de sessoes e CSRF em todos os fluxos.
4. Hardening de uploads e areas protegidas.
5. Logs minimos administrativos.

### Etapa 7 - Deploy Apache e fechamento
Status: pendente

Objetivo:
- Deixar pronto para subir em hospedagem Apache.

Itens:
1. Revisar `.htaccess`.
2. Ajustar configuracoes de ambiente.
3. Testar rotas amigaveis.
4. Trocar base local por producao.
5. Revisao final de QA.

## Prompt Base de Continuidade
Use este prompt quando quiser retomar comigo:

```text
Continuar o projeto SexyLua em D:\PROJETOS\002 - PROJETOS WORKANA\008 - SexyLua.

Estado atual:
- Etapa 1 concluida.
- Visual das 19 telas deve permanecer 1:1 com os HTMLs do prototipo.
- Login e registro ja foram alinhados ao mesmo idioma visual.
- Backend atual em PHP puro com JSON local.

Regras obrigatorias:
- Nao redesenhar telas existentes.
- Nao alterar a identidade visual aprovada.
- Sempre encaixar o backend por baixo do HTML original.
- Manter compatibilidade com Apache e sem frameworks no servidor.

Etapa que quero executar agora:
- [COLE A ETAPA OU SUBETAPA AQUI]

Objetivo desta rodada:
- [COLE O OBJETIVO EXATO AQUI]
```

## Prompt Curto por Etapa

### Para iniciar a Etapa 2
```text
Continuar SexyLua pela Etapa 2.
Quero dinamizar as telas com dados reais do backend, sem mudar o layout.
Comece pelas telas Publico e depois avance para Assinante.
```

### Para iniciar a Etapa 3
```text
Continuar SexyLua pela Etapa 3.
Quero completar as acoes reais de cada tela, mantendo o visual 1:1.
Priorize fluxos de criador, mensagens, assinaturas e moderacao.
```

### Para iniciar a Etapa 4
```text
Continuar SexyLua pela Etapa 4.
Quero migrar a persistencia atual para PostgreSQL, preservando o funcionamento e o visual.
```

### Para iniciar a Etapa 5
```text
Continuar SexyLua pela Etapa 5.
Quero adicionar upload real de imagens e videos no sistema, sem mexer no layout aprovado.
```

### Para iniciar a Etapa 6
```text
Continuar SexyLua pela Etapa 6.
Quero reforcar seguranca, permissoes, validacoes e regras de negocio.
```

### Para iniciar a Etapa 7
```text
Continuar SexyLua pela Etapa 7.
Quero preparar deploy final em Apache e fechar o projeto para producao.
```
