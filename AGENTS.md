# Career Mode Tracker — instruções de desenvolvimento

## Objetivo e âmbito

Aplicação web server-side para substituir uma folha Excel de acompanhamento de carreiras de *Career Mode* do FC. Permite a vários utilizadores gerir carreiras, épocas, plantéis, transferências, táticas e objetivos, preservando o histórico entre épocas.

A interface, as mensagens apresentadas ao utilizador e o código-fonte — identificadores, comentários, docblocks, logs e mensagens de exceções internas — são sempre em inglês. O MVP não inclui SPA, API pública, React/Vue/Angular, aplicação móvel, estatísticas de jogo, exportações, multi-idioma nem sincronização automática do catálogo.

## Stack e convenções

- PHP 8.5+; Yii Framework 3; MySQL 8; Composer.
- Identificadores de entidades usam UUIDv7 através de `ramsey/uuid` e `Ramsey\Uuid\UuidInterface`; persistir como `CHAR(36)` e não criar wrappers de ID específicos do projeto.
- HTML/CSS com Bootstrap e JavaScript simples apenas quando necessário. A UI é renderizada no servidor.
- Testes de domínio com PHPUnit/Codeception (`composer test`).
- O namespace de aplicação é `App\\` e o código vive em `src/`.
- Não implementar scraping no PHP. A importação de dados usa um serviço Python externo baseado em `soccerdata`; Yii valida e persiste o resultado.
- Nunca deixar processos iniciados durante o desenvolvimento ou testes a correr em background; terminá-los sempre no fim da operação.
- Nunca iniciar `yii serve` na porta `8080`, que está reservada ao utilizador. Quando for necessário iniciar um servidor local, indicar explicitamente uma porta alternativa e terminar o processo depois de o usar.
- O desenvolvimento do MVP é nativo no sistema do utilizador; não usar, propor, alterar ou exigir Docker antes de o utilizador pedir explicitamente essa via.
- Enquanto o projeto estiver apenas em desenvolvimento local, é permitido reverter, editar e reaplicar migrations para manter um schema inicial limpo. Depois de o utilizador declarar o projeto em produção, migrations aplicadas tornam-se imutáveis e alterações de schema exigem novas migrations.

## Design e experiência visual

O Manager Hub é um workspace premium para gerir uma carreira de futebol. A interface deve ser dark, moderna, elegante, limpa, respirada e rápida, com clareza e eficiência acima de decoração. A personalidade vem da execução cuidada de uma UI simples, não de elementos visuais excessivos nem de uma estética "gaming".

- Preservar a linguagem visual existente: superfícies dark em tons slate, texto de alto contraste, linhas discretas e accent dourado usado com contenção. Não usar verde como cor dominante nem substituir o accent por outra cor sem redesenhar conscientemente o sistema inteiro.
- Usar `public/logo_no_backround.png` ou `public/logo.png` (quando preferível, tem moldura propria) como símbolo de marca. O símbolo deve permanecer pequeno, refinado e funcional tanto na navegação como junto do nome; não criar lettermarks grandes, monogramas artificiais ou tratamento de logo decorativo.
- A sidebar é a navegação principal para utilizadores autenticados. Deve ser discreta, ocupar pouco espaço, suportar o estado expandido (ícone e label) e colapsado (apenas ícones), e manter o conteúdo como protagonista. Persistir o comportamento de colapso sem introduzir dependências de frontend pesadas.
- Só apresentar destinos, ações, dados e estados que existam de facto. Nunca criar dados fictícios, placeholders que pareçam dados reais, ou links para funcionalidades ainda não implementadas apenas para tornar a interface mais interessante.
- A homepage pública deve explicar imediatamente o propósito do produto e orientar para as ações reais disponíveis. Evitar painéis de dashboard, métricas, épocas, plantéis ou estados de transferências simulados.
- Adaptar o layout à tarefa em vez de impor uma grelha de cards a todas as páginas. Gestão de plantel deve privilegiar densidade, estrutura, navegação rápida e teclado; táticas/XI podem ser espaciais e visuais; formulários, histórico e detalhes devem escolher a composição mais clara para a tarefa.
- Não transformar páginas numa coleção de cards. Evitar estética de template Bootstrap/admin genérico, excesso de cantos arredondados, sombras pesadas, gradientes gratuitos, animações decorativas e elementos sem função.
- Manter uma escala coerente para tipografia, spacing, superfícies, bordas, botões, formulários, tabelas, badges e estados de erro/vazio/carregamento. Interações hover, focus e active devem ser subtis, previsíveis, acessíveis por teclado e rápidas; respeitar `:focus-visible`.
- Usar Bootstrap como base de estrutura quando útil, mas personalizar totalmente os seus componentes para que nunca pareçam o tema Bootstrap padrão.
- Antes de concluir uma alteração visual, percorrer as páginas afetadas e confirmar que fazem parte de um único produto deliberadamente desenhado. Atualizar testes de apresentação afetados e validar a renderização em browser quando a alteração de layout for material.

## Arquitetura obrigatória

Respeitar Onion Architecture e DDD. Dependências apontam sempre para dentro:

`Web -> Application -> Domain <- Infrastructure`

- `Domain`: entidades, value objects, regras de negócio e interfaces/contratos. Nunca depender de Yii, MySQL, HTTP, Bootstrap ou Python.
- `Application`: casos de uso e coordenação de transações/interfaces (por exemplo, criar carreira, registar transferência, finalizar época).
- `Infrastructure`: MySQL, ORM/persistência, repositórios e serviço Python.
- `Web`: controllers, forms, views, layouts e assets. Controllers devem ser finos: validar input, invocar um caso de uso e produzir a resposta; não colocar regras de negócio complexas aqui.

Organizar os módulos por domínio (Career, Season, Squad, Transfer, Tactic, Rule e Catalog) sem sacrificar esta separação de camadas.

## Dados, histórico e soft delete

- Todas as tabelas têm `is_deleted` e `deleted_at`. Não fazer hard delete.
- As queries normais devem excluir automaticamente `is_deleted = 1`.
- `deleted_at` regista a última remoção por soft delete e não é limpo ao restaurar um registo; o estado atual é determinado por `is_deleted`.
- `username` é um identificador alfanumérico ASCII de 1 a 20 caracteres; preservar o casing para apresentação, mas garantir unicidade case-insensitive na base de dados. `password_hash` nunca é vazio e tem no máximo 255 bytes; passwords em claro e respetivas regras de segurança não pertencem ao Domain. A Application valida passwords em claro: 8–128 caracteres Unicode, pelo menos uma maiúscula, um número e um carácter de pontuação ou símbolo; não fazer `trim`.
- `email` é normalizado por trim e lowercase, tem no máximo 255 bytes e é validado sintaticamente com `egulias/email-validator` e `NoRFCWarningsValidation`. Não fazer DNS/MX lookup no Domain nem no registo do MVP.
- `player_catalog` é apenas uma fonte partilhada para criar dados iniciais. Uma reimportação pode atualizar catálogo, mas nunca pode reescrever dados históricos de carreira.
- `squad_players` e `transfers` guardam snapshots dos valores relevantes no momento. Idade nunca é persistida: calcular sempre a partir de `birth_date` e da data relevante.
- A valorização é derivada, não persistida: `((value_final - value_initial) / value_initial) * 100`, tratando `value_initial = 0`.
- `season_snapshots` são históricos e não a fonte do estado atual.
- Instantes (`*_at` e datas de acontecimentos) são gravados em `DATETIME` UTC; datas de calendário, como `birth_date`, usam `DATE` sem timezone. O domínio recebe e decide explicitamente esses instantes e estados de ciclo de vida; a base de dados não deve preenchê-los ou alterá-los através de defaults, `CURRENT_TIMESTAMP` ou `ON UPDATE`.

## Invariantes de domínio

- Cada `Career` pertence a um único utilizador; validar ownership no backend para cada leitura e mutação de dados da carreira. Nunca confiar apenas em IDs enviados pelo browser.
- Catálogo (`game_versions`, `clubs`, `player_catalog`) é partilhado; carreiras e os seus dados são privados ao respetivo utilizador.
- Uma carreira só pode ter uma época ativa (`finalized_at IS NULL`). Não adicionar `current_season_id` a `careers`.
- Só pode existir uma tática ativa por carreira. Ativar outra desativa a anterior mas mantém-na no histórico.
- Um jogador não pode ocupar mais de um `tactic_slot` da mesma tática.
- Vender um jogador cria uma transferência histórica, faz soft delete da associação ativa ao plantel e remove-o dos `tactic_slots` ativos.
- Empréstimo de saída mantém o jogador na carreira, mas altera o estado para `Emprestado` e torna-o indisponível para o XI. Empréstimo de entrada cria um `SquadPlayer` na época atual.

## Fluxos críticos

### Criar carreira

Ao criar uma carreira, criar a primeira época e copiar para `SquadPlayer` os dados do clube no catálogo: nome, posição, nascimento, overall, potencial e valor. Estes tornam-se valores iniciais; os valores finais começam a `NULL`.

### Transferências

Transferências mantêm `position`, `birth_date`, `overall`, `potential` e `value` como fotografia do momento, sem depender de futuras alterações no catálogo ou no jogador do plantel. Tipos: `Compra`, `Venda`, `Emprestimo_Entrada`, `Emprestimo_Saida`.

### Finalizar época

Esta operação é atómica e deve decorrer numa única transação MySQL:

1. Criar `SeasonSnapshot` com plantel e tática ativa.
2. Definir `finalized_at` na época atual.
3. Criar a próxima época ativa.
4. Copiar somente os jogadores que permanecem; usar os valores finais da época anterior como valores iniciais da nova (`overall`, `potential`, `value`), mantendo `birth_date`.
5. Em erro, fazer rollback completo.

Jogadores vendidos não transitam para a nova época; o registo anterior e a transferência permanecem no histórico.

## Testes mínimos ao alterar regras de negócio

Qualquer alteração de comportamento no código exige alterar ou acrescentar testes que cubram essa alteração.

Cobrir o comportamento afetado, em especial:

- Finalização: snapshot, nova época, rollover dos valores finais, exclusão de vendidos e rollback.
- Transferências: compra cria jogador, venda remove plantel/XI, empréstimos alteram o estado correto e valores históricos não mudam.
- Táticas: ativação exclusiva, histórico e jogador sem duplicação.
- Segurança: um utilizador não consegue ler nem alterar carreiras, plantéis ou transferências de outro.

## Ordem de entrega do MVP

1. Esqueleto, autenticação, migrations e camadas.
2. Serviço Python e catálogo.
3. Carreiras, épocas e plantel.
4. Transferências.
5. Finalizar época.
6. Táticas.
7. Objetivos/regras e polish.

Antes de introduzir funcionalidade fora desta sequência ou do âmbito indicado, confirmar que é necessária para o MVP.
