# CS Control

Painel pessoal de Customer Success para registrar atividades do dia a dia
(reuniões, e-mails, cancelamentos, migrações, contratos, financeiro), ver
alertas automáticos e acompanhar indicadores. Ferramenta de uso individual —
não é um CRM de equipe.

## O que já existe nesta etapa

- Estrutura do projeto em PHP puro (sem framework, sem build step)
- Banco de dados MySQL com o modelo de dados completo
- Login com criação de conta no primeiro acesso
- Dashboard ("Minha Central") com dados reais do banco: cards do dia,
  alertas automáticos, agenda de hoje, timeline de atividades, painel de
  indicadores e um painel de insights baseado em regras (não usa nenhuma IA
  externa, então não tem custo)
- Registrar atividade (painel rápido no Dashboard + modal completo)
- Usuários e Permissões (gestão básica de acesso por módulo)

## O que ainda não existe

- Telas de Clientes, Atividades, Agenda, Contratos, Financeiro, Analytics,
  Relatórios e Configurações completas (aparecem como "em breve" no menu)
- Integrações reais com Google Calendar, HubSpot, Gmail e Slack — vêm nas
  próximas etapas, uma de cada vez

## Requisitos

- PHP 8.1 ou mais novo, com a extensão `pdo_mysql` habilitada
- MySQL 5.7+ ou MariaDB 10.3+
- Não precisa de Node, Composer ou qualquer ferramenta de build

## Rodando localmente (para testar antes de publicar)

1. Crie o banco e importe o schema:
   ```
   mysql -u root -p -e "CREATE DATABASE cs_control CHARACTER SET utf8mb4;"
   mysql -u root -p cs_control < database/schema.sql
   ```
2. Copie o arquivo de configuração e preencha com os dados do seu banco:
   ```
   cp .env.example .env
   ```
   Edite o `.env` com `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Suba o servidor embutido do PHP a partir da pasta `public`:
   ```
   cd public
   php -S localhost:8000
   ```
4. Acesse `http://localhost:8000` no navegador. Como é o primeiro acesso,
   você verá a tela de criação de conta.

## Publicando no cPanel

O CS Control foi pensado para o mesmo cPanel que você já usa no CS Hub
4YouSee, mas com uma diferença importante de segurança: **a pasta `public`
deve ser a única parte do projeto visível pela internet.** As pastas `app`
(código PHP) e `database` (schema SQL), e o arquivo `.env` (senhas), não
devem ficar acessíveis por URL.

Duas formas de fazer isso no cPanel:

**Opção recomendada — subdomínio com Document Root customizado:**
1. Suba todo o projeto (`app`, `public`, `database`, `.env`, etc.) para uma
   pasta fora de `public_html`, por exemplo `/home/seuusuario/cscontrol`.
2. Em cPanel → *Domínios* (ou *Subdomínios*), crie um subdomínio (ex.:
   `cscontrol.seudominio.com`) e defina o **Document Root** como
   `/home/seuusuario/cscontrol/public`.
3. Pronto: só o conteúdo de `public/` fica exposto na internet.

**Opção alternativa — tudo dentro de `public_html`:**
Se seu plano de hospedagem só permitir usar `public_html` diretamente, suba
o projeto inteiro para `public_html/cscontrol/` (mantendo a pasta `public/`
dentro dela) e aponte o acesso apenas para
`public_html/cscontrol/public/`. O arquivo `.htaccess` na raiz do projeto já
bloqueia o acesso direto às pastas `app` e `database` como proteção extra,
mas o ideal é sempre usar a Opção 1 quando possível.

**Banco de dados no cPanel:**
1. Em *MySQL Databases*, crie o banco e um usuário com todas as permissões
   nesse banco.
2. Em *phpMyAdmin*, abra o banco criado e importe `database/schema.sql`.
3. Preencha o `.env` na raiz do projeto com o nome do banco, usuário e senha
   que o cPanel gerou (geralmente com um prefixo, tipo
   `seuusuario_cs_control`).

## Estrutura de pastas

```
cs-control/
  app/            Código PHP que faz o sistema funcionar (não é acessível por URL)
  database/       schema.sql — importe este arquivo no MySQL
  public/         Tudo que o navegador acessa (é a pasta que vira o Document Root)
    partials/     Cabeçalho, rodapé, menu lateral e ícones reutilizados nas páginas
    api/          Endpoints que o formulário e os botões chamam para salvar dados
    assets/       CSS e JavaScript
  .env.example    Modelo do arquivo de configuração (copie para .env)
```

## Segurança

- Nunca suba o arquivo `.env` para o Git nem para um repositório público —
  ele guarda a senha do banco de dados.
- Todas as senhas de usuário ficam criptografadas no banco (nunca em texto
  puro).
- Quando as integrações (Google, HubSpot, Slack) forem implementadas, as
  chaves de acesso também vão morar só no `.env`, nunca no código.
