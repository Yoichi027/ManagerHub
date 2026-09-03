# Career Mode Tracker — instruções de desenvolvimento

## Objetivo e âmbito

Aplicação web server-side para substituir uma folha Excel de acompanhamento de carreiras de *Career Mode* do FC. Permite a vários utilizadores gerir carreiras, épocas, plantéis, transferências, táticas e objetivos, preservando o histórico entre épocas.

O idioma da interface e das mensagens de negócio é português. O MVP não inclui SPA, API pública, React/Vue/Angular, aplicação móvel, estatísticas de jogo, exportações, multi-idioma nem sincronização automática do catálogo.

## Stack e convenções

- PHP 8.5+; Yii Framework 3; MySQL 8; Composer.
- HTML/CSS com Bootstrap e JavaScript simples apenas quando necessário. A UI é renderizada no servidor.
- Testes de domínio com PHPUnit/Codeception (`composer test`).
- O namespace de aplicação é `App\\` e o código vive em `src/`.
- Não implementar scraping no PHP. A importação de dados usa um serviço Python externo baseado em `soccerdata`; Yii valida e persiste o resultado.

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
- `player_catalog` é apenas uma fonte partilhada para criar dados iniciais. Uma reimportação pode atualizar catálogo, mas nunca pode reescrever dados históricos de carreira.
- `squad_players` e `transfers` guardam snapshots dos valores relevantes no momento. Idade nunca é persistida: calcular sempre a partir de `birth_date` e da data relevante.
- A valorização é derivada, não persistida: `((value_final - value_initial) / value_initial) * 100`, tratando `value_initial = 0`.
- `season_snapshots` são históricos e não a fonte do estado atual.

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
