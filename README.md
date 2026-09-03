Career Mode Tracker — Especificação Técnica MVP
0. Contexto e origem

Ferramenta web para acompanhar carreiras de Career Mode do FC (FIFA), criada para substituir a folha Excel atualmente utilizada.

A folha analisada (Overhaul_Sporting.xlsx) tem a seguinte estrutura, que serve de base ao modelo de dados:

    Equipas — lista de posições-base por equipa (GR, DE, DC, DD, MC, ME, MD, MCO, PL), usada como "template" de plantel por clube.
    Plantel {época} (ex. Plantel 2526, Plantel 2627) — uma linha por jogador com: Posição, Nome, Idade, Over Inicial / Over Final, Pot. Inicial / Pot. Final, Valor Inicial / Valor Final, Valorização (%), Estado (Titular / Suplente / Emprestado).
    Transferências {época} — jogadores que saíram/entraram nessa época: Posição, Nome, Idade, Overall, Potencial, Tipo (Compra / Venda), Valor, Rating (avaliação subjetiva do negócio).

Isto mapeia diretamente para o conceito de "época": cada época tem um snapshot inicial e final do plantel, mais um registo de transferências.

É exatamente este ciclo que o botão "Finalizar Época" deverá automatizar.

O objetivo é construir um MVP simples, que substitua a utilização da folha Excel e permita acompanhar várias carreiras, épocas, plantéis, transferências, táticas e objetivos.
1. Stack e decisões técnicas
Tema	Decisão
Backend	PHP 8.3+
Framework	Yii Framework 3
Arquitetura	Onion Architecture + DDD
Base de dados	MySQL 8
ORM / Persistência	Componentes de persistência compatíveis com Yii 3
Frontend	HTML + CSS + Bootstrap
JavaScript	JavaScript básico apenas quando necessário
Autenticação	Sistema de autenticação e sessão do Yii 3
Multi-user	Sim — vários utilizadores, cada um com as suas carreiras
Dados iniciais	Serviço externo em Python
Fonte dos dados	soccerdata
Comunicação com Python	Chamada externa a partir da aplicação Yii 3
Scraping dentro do Yii	Não
Testes	PHPUnit
Gestão de dependências	Composer
Idioma	Português

Não será utilizada uma SPA.

Não será utilizado React, Vue ou Angular.

Não será criada uma API separada apenas para o frontend.

O frontend será maioritariamente server-side através do Yii 3, utilizando Bootstrap para a interface e JavaScript apenas quando seja realmente necessário.
2. Arquitetura — Onion + DDD

A aplicação será estruturada utilizando Onion Architecture, com princípios de Domain-Driven Design.

A dependência entre camadas deve apontar sempre para dentro.

┌─────────────────────────────────────────────┐
│                  Web                         │
│        Controllers / Views / Forms           │
├─────────────────────────────────────────────┤
│               Application                   │
│       Use Cases / Application Services       │
├─────────────────────────────────────────────┤
│                 Domain                      │
│ Entities / Value Objects / Domain Rules      │
├─────────────────────────────────────────────┤
│              Infrastructure                 │
│ MySQL / Persistence / Python Service / etc.  │
└─────────────────────────────────────────────┘

Domain

Contém o núcleo da aplicação e as regras de negócio.

Principais conceitos:

Career
Season
SquadPlayer
Transfer
Tactic
TacticSlot
CareerRule

O Domain não deve depender de Yii, MySQL, Bootstrap ou do serviço Python.
Application

Representa os casos de utilização da aplicação.

Exemplos:

CreateCareer
CreateSeason
RegisterTransfer
FinalizeSeason
CreateTactic
ActivateTactic
CreateCareerRule

A Application coordena o Domain e as interfaces necessárias.
Infrastructure

Contém as implementações concretas:

MySQL
ORM
Repositories
Autenticação
Serviço de comunicação com Python
Integrações externas

Web

Contém:

Controllers
Views
Forms
Layouts
Bootstrap
CSS
JavaScript

Os Controllers não devem conter regras de negócio complexas.

Devem receber os dados do utilizador, chamar os casos de utilização e devolver a resposta adequada.
3. Estrutura conceptual do projeto

Uma estrutura possível:

career-tracker/
│
├── config/
│
├── src/
│   ├── Domain/
│   │   ├── Career/
│   │   ├── Season/
│   │   ├── Squad/
│   │   ├── Transfer/
│   │   ├── Tactic/
│   │   ├── Rule/
│   │   └── Catalog/
│   │
│   ├── Application/
│   │   ├── Career/
│   │   ├── Season/
│   │   ├── Squad/
│   │   ├── Transfer/
│   │   ├── Tactic/
│   │   ├── Rule/
│   │   └── Catalog/
│   │
│   └── Infrastructure/
│       ├── Persistence/
│       ├── Repositories/
│       └── External/
│           └── PythonDataService/
│
├── web/
│   ├── index.php
│   ├── css/
│   └── js/
│
├── templates/
│   ├── layout/
│   ├── auth/
│   ├── dashboard/
│   ├── careers/
│   ├── squad/
│   ├── transfers/
│   ├── tactics/
│   └── rules/
│
├── migrations/
│
├── tests/
│   ├── Unit/
│   └── Integration/
│
├── composer.json
└── README.md

A estrutura exata poderá ser ajustada durante o desenvolvimento, mas o princípio de separação entre Domain, Application, Infrastructure e Web deve ser mantido.
4. Regras gerais da base de dados

Todas as tabelas da aplicação devem utilizar soft delete.

Não existe hard delete.

Todas as tabelas terão obrigatoriamente:

is_deleted
deleted_at

Quando um registo é removido:

is_deleted = 1
deleted_at = CURRENT_TIMESTAMP

O registo permanece fisicamente na base de dados.

As queries normais devem ignorar automaticamente os registos onde:

is_deleted = 1

Isto permite manter o histórico e evitar perda permanente de dados.
5. Identidade / utilizadores
users
Campo	Tipo	Descrição
id	INT PK	Identificador
username	VARCHAR(100)	Nome de utilizador
email	VARCHAR(255)	Email
password_hash	VARCHAR(255)	Password hashed
created_at	DATETIME	Data de criação
updated_at	DATETIME	Última alteração
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

A autenticação será baseada em sessão/cookie através do Yii 3.

Não serão utilizados JWT ou refresh tokens no MVP.

O registo de utilizadores poderá ser:

    livre, mas com a aplicação fechada ao público; ou
    controlado por administrador.

Esta decisão pode ser tomada durante a implementação inicial sem alterar o modelo principal.
6. Catálogo de jogo

O catálogo é partilhado entre os utilizadores.

É constituído por:

GameVersions
Clubs
PlayerCatalog

Os dados são obtidos externamente através de um serviço Python.
6.1 game_versions

Representa uma versão do jogo.
Campo	Tipo	Descrição
id	INT PK	Identificador
name	VARCHAR(50)	Ex: FC26
sofifa_slug	VARCHAR(50)	Identificador utilizado pelo serviço externo
imported_at	DATETIME NULL	Data da última importação
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Exemplo:

FC25
FC26
FC27

7. Clubes
clubs

Representa os clubes disponíveis numa versão específica do jogo.
Campo	Tipo	Descrição
id	INT PK	Identificador
game_version_id	INT FK	Versão do jogo
name	VARCHAR(100)	Nome do clube
sofifa_team_id	VARCHAR(50)	ID externo
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Relação:

GameVersion
    │
    └── Clubs

O mesmo clube pode existir em várias versões:

Sporting CP - FC25
Sporting CP - FC26
Sporting CP - FC27

8. Catálogo de jogadores
player_catalog

Contém os jogadores obtidos através do serviço Python.
Campo	Tipo	Descrição
id	INT PK	Identificador
game_version_id	INT FK	Versão do jogo
club_id	INT NULL FK	Clube
name	VARCHAR(100)	Nome
birth_date	DATE	Data de nascimento
main_position	VARCHAR(5)	Posição principal
overall	INT	Overall
potential	INT	Potencial
market_value	DECIMAL(15,2)	Valor de mercado
external_player_id	VARCHAR(50)	ID externo
imported_at	DATETIME	Data da importação
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

A idade não é armazenada.

A idade é sempre calculada a partir de:

birth_date

Quando for necessário apresentar a idade, ela deverá ser calculada relativamente à data relevante.

Por exemplo, numa transferência realizada em 15/01/2027, a idade deverá corresponder à idade que o jogador tinha nessa data.
9. Carreiras
careers

Representa uma Career Mode criada por um utilizador.
Campo	Tipo	Descrição
id	INT PK	Identificador
user_id	INT FK	Utilizador
name	VARCHAR(100)	Nome da carreira
game_version_id	INT FK	Versão do jogo
club_id	INT FK	Clube
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Não existe current_season_id.

A época atual é determinada através das seasons da carreira.

A regra será:

Uma carreira só pode ter uma Season não finalizada.

Logo:

finalized_at IS NULL

identifica a época atual.
10. Épocas
seasons

Representa uma época dentro de uma carreira.
Campo	Tipo	Descrição
id	INT PK	Identificador
career_id	INT FK	Carreira
label	VARCHAR(20)	Ex: 25/26
started_at	DATETIME	Data de início
finalized_at	DATETIME NULL	Data de finalização
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Exemplo:

Career: Sporting CP

25/26
26/27
27/28

Apenas uma delas pode estar ativa.
11. Plantel
squad_players

Representa um jogador dentro do plantel de uma determinada época.

Esta tabela corresponde diretamente ao:

Plantel {época}

da folha Excel.
Campo	Tipo	Descrição
id	INT PK	Identificador
season_id	INT FK	Época
player_catalog_id	INT NULL FK	Jogador do catálogo
name	VARCHAR(100)	Nome
position	VARCHAR(5)	Posição
birth_date	DATE	Data de nascimento
overall_initial	INT	Overall inicial
overall_final	INT NULL	Overall final
potential_initial	INT	Potencial inicial
potential_final	INT NULL	Potencial final
value_initial	DECIMAL(15,2)	Valor inicial
value_final	DECIMAL(15,2) NULL	Valor final
status	VARCHAR(20)	Estado
notes	TEXT NULL	Notas
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Estados:

Titular
Suplente
Emprestado

A idade não é guardada.

É calculada através de birth_date.

A valorização também não necessita de ser armazenada.

Pode ser calculada:

((value_final - value_initial) / value_initial) * 100

com tratamento para value_initial = 0.
12. Transferências
transfers

Representa um acontecimento de mercado dentro de uma época.
Campo	Tipo	Descrição
id	INT PK	Identificador
season_id	INT FK	Época
squad_player_id	INT NULL FK	Jogador associado
position	VARCHAR(5)	Posição no momento
birth_date	DATE	Data de nascimento
overall	INT	Overall no momento
potential	INT	Potencial no momento
transfer_type	VARCHAR(30)	Tipo
value	DECIMAL(15,2)	Valor da transferência
rating	VARCHAR(2) NULL	Avaliação
date	DATETIME	Data
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

Não existe player_name.

Os dados importantes no momento da transferência são guardados diretamente:

position
birth_date
overall
potential
value

Isto permite preservar o estado histórico do jogador no momento da operação.

Tipos:

Compra
Venda
Emprestimo_Entrada
Emprestimo_Saida

13. Histórico das transferências

Os dados da transferência são independentes do estado futuro do jogador.

Exemplo:

15/07/2026

Overall: 79
Potential: 86
Value: €25M

Mesmo que posteriormente o jogador tenha:

Overall: 87
Potential: 90
Value: €70M

a transferência continua a guardar:

Overall: 79
Potential: 86
Value: €25M

Desta forma, o histórico não é alterado.
14. Snapshot de fim de época
season_snapshots

Guarda uma fotografia do estado da carreira no momento em que uma época é finalizada.
Campo	Tipo	Descrição
id	INT PK	Identificador
season_id	INT UNIQUE FK	Época
snapshot_json	JSON	Estado completo
created_at	DATETIME	Criação
updated_at	DATETIME	Atualização
is_deleted	BOOLEAN	Soft delete
deleted_at	DATETIME NULL	Data de remoção

O JSON poderá conter:

Plantel
Tática ativa
XI inicial
Formação
Roles

Exemplo:

{
    "season": "25/26",
    "squad": [],
    "tactic": {
        "formation": "4-3-3",
        "players": []
    }
}

O snapshot é histórico.

Não será utilizado para gerir o estado atual da carreira.
15. Formações
formations

Catálogo fixo de formações.
Campo	Tipo
id	INT PK
name	VARCHAR(20)
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Exemplos:

4-3-3
4-2-3-1
4-4-2
3-5-2
5-3-2

16. Posições das formações
position_slots

Representa os lugares existentes dentro de uma formação.
Campo	Tipo
id	INT PK
formation_id	INT FK
position	VARCHAR(5)
slot_order	INT
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Exemplo:

4-3-3

GK
LB
CB
CB
RB
CM
CM
CM
LW
ST
RW

17. Roles dos jogadores
player_roles

Catálogo genérico dos roles.
Campo	Tipo
id	INT PK
position	VARCHAR(5)
role_name	VARCHAR(50)
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Exemplo:

ST
 ├── Advanced Forward
 ├── Poacher
 └── False 9

CM
 ├── Box-to-Box
 ├── Playmaker
 └── Deep Lying Playmaker

Os roles não dependem da versão do jogo.
18. Táticas
tactics

Representa uma configuração tática da carreira.
Campo	Tipo
id	INT PK
career_id	INT FK
formation_id	INT FK
is_active	BOOLEAN
notes	TEXT NULL
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Regra:

Apenas uma tática ativa por carreira.

Quando uma nova tática é ativada:

Tática anterior → is_active = 0
Nova tática → is_active = 1

A tática anterior permanece no histórico.
19. Jogadores da tática
tactic_slots

Associa jogadores aos lugares da formação.
Campo	Tipo
id	INT PK
tactic_id	INT FK
position_slot_id	INT FK
squad_player_id	INT FK
player_role_id	INT NULL FK
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Exemplo:

ST → Jogador X → Advanced Forward
LW → Jogador Y → Inside Forward
CM → Jogador Z → Box-to-Box

20. Objetivos e regras
career_rules
Campo	Tipo
id	INT PK
career_id	INT FK
title	VARCHAR(150)
description	TEXT
is_global	BOOLEAN
start_date	DATE NULL
end_date	DATE NULL
is_done	BOOLEAN
created_at	DATETIME
updated_at	DATETIME
is_deleted	BOOLEAN
deleted_at	DATETIME NULL

Exemplos:

Ganhar o campeonato
Promover 2 jogadores da academia
Não contratar jogadores com mais de 30 anos

Uma regra pode ser:

Global

ou:

Limitada a um período

21. Relações principais

A estrutura geral da base de dados fica:

users
  │
  └── careers
        │
        ├── game_versions
        │
        ├── clubs
        │
        ├── seasons
        │     │
        │     ├── squad_players
        │     │
        │     ├── transfers
        │     │
        │     └── season_snapshots
        │
        ├── tactics
        │     │
        │     └── tactic_slots
        │
        └── career_rules


game_versions
  │
  ├── clubs
  │
  └── player_catalog


formations
  │
  └── position_slots
        │
        └── tactic_slots


player_roles
  │
  └── tactic_slots

22. Serviço externo Python

Os dados iniciais dos jogadores serão obtidos através de um serviço externo em Python.

A aplicação Yii 3 não terá o scraper implementado dentro dela.

O Python utilizará:

soccerdata

para obter os dados necessários.

O objetivo é manter esta integração simples para o MVP.

Arquitetura:

                 Yii 3
                   │
                   │ chamada externa
                   ▼
          Python Data Service
                   │
                   ▼
              soccerdata
                   │
                   ▼
             Dados externos
                   │
                   ▼
          Python Data Service
                   │
                   ▼
                 Yii 3
                   │
                   ▼
             player_catalog

O serviço Python será responsável por:

Obter dados
Processar dados
Normalizar dados
Devolver dados ao Yii

O Yii será responsável por:

Receber dados
Validar dados
Persistir dados
Gerir GameVersions
Gerir Clubs
Gerir PlayerCatalog

A forma concreta de comunicação poderá ser um pequeno serviço HTTP ou execução externa controlada.

Para o MVP, não existe necessidade de transformar isto numa arquitetura de microserviços complexa.

É apenas uma dependência externa para obtenção dos dados iniciais.
23. Importação de uma versão do jogo

O fluxo será:

Admin
  │
  ▼
Importar FC26
  │
  ▼
Yii 3
  │
  ▼
Python Data Service
  │
  ▼
soccerdata
  │
  ▼
Dados FC26
  │
  ▼
Yii 3
  │
  ├── GameVersions
  ├── Clubs
  └── PlayerCatalog

A importação será feita on-demand.

Não existe sincronização automática.

Uma versão importada fica armazenada na base de dados e passa a ser utilizada localmente.
24. Reimportação

É possível voltar a importar uma versão.

Por exemplo:

FC26
Importado: 02/09/2026

[Reimportar]

A reimportação atualiza o catálogo.

Contudo, não deve alterar os dados históricos das carreiras.

Por exemplo:

player_catalog
Overall = 85

não deve alterar:

squad_players
Overall Initial = 80
Overall Final = 83

nem:

transfers
Overall = 80

O catálogo é apenas a fonte de dados inicial.
25. Criar uma carreira

O fluxo é:

Utilizador
   │
   ▼
Nova Carreira
   │
   ├── Escolher GameVersion
   │
   ├── Escolher Club
   │
   └── Definir nome
   │
   ▼
Criar Career
   │
   ▼
Criar primeira Season
   │
   ▼
Obter jogadores do PlayerCatalog
   │
   ▼
Criar SquadPlayers

Ao criar os SquadPlayers, são copiados:

Nome
Posição
BirthDate
Overall
Potential
MarketValue

para:

Name
Position
BirthDate
OverallInitial
PotentialInitial
ValueInitial

Os valores finais ficam:

NULL

26. Página Squad

A página Squad substitui a folha Excel.

Colunas:

Posição
Nome
Idade
Over Inicial
Over Final
Pot. Inicial
Pot. Final
Valor Inicial
Valor Final
Valorização
Estado
Notas

Apesar de a idade aparecer na interface, ela será calculada através da birth_date.

A tabela deverá permitir edição dos campos relevantes.

Filtros mínimos:

Posição
Estado

27. Transferências
Compra

Comprar jogador
       │
       ▼
Pesquisar PlayerCatalog
       │
       ▼
Selecionar jogador
       │
       ▼
Confirmar dados
       │
       ├── Position
       ├── BirthDate
       ├── Overall
       ├── Potential
       └── Value
       │
       ▼
Criar SquadPlayer
       │
       ▼
Criar Transfer

Venda

Selecionar SquadPlayer
       │
       ▼
Registar venda
       │
       ├── Position
       ├── BirthDate
       ├── Overall
       ├── Potential
       └── Value
       │
       ▼
Remover do plantel ativo
       │
       ▼
Remover de TacticSlots ativos
       │
       ▼
Criar Transfer

A remoção é sempre feita através de soft delete.
28. Empréstimos
Empréstimo de saída

O jogador continua associado à carreira, mas deixa de estar disponível para o XI.

O seu estado passa para:

Emprestado

A transferência é registada como:

Emprestimo_Saida

Empréstimo de entrada

É criado um SquadPlayer para a época atual.

A transferência é registada como:

Emprestimo_Entrada

29. Tática

O utilizador escolhe uma formação:

4-3-3

A aplicação apresenta um campo:

                ST

       LW                 RW

          CM         CM

                CM

LB        CB       CB        RB

                GK

Cada posição representa um PositionSlot.

O utilizador seleciona o jogador e, opcionalmente, o role.

Exemplo:

ST
Jogador X
Advanced Forward

Ao guardar:

Tactic
   │
   └── TacticSlots

A tática passa a ser a ativa.
30. Histórico de táticas

Quando uma nova tática é criada:

Tática antiga
is_active = 0

Tática nova
is_active = 1

A antiga permanece guardada.

Exemplo:

02/09/2026
4-2-3-1
"Alterei para controlar melhor o meio campo."

15/08/2026
4-3-3
"Formação inicial da época."

31. Objetivos e regras

Cada carreira pode possuir vários objetivos/regras.

Exemplo:

Ganhar o campeonato
Promover dois jogadores da academia
Não contratar jogadores acima dos 30

Podem ser:

Globais

ou:

Com período

A página terá filtros:

Ativos
Globais
Expirados
Cumpridos

32. Finalizar época

Esta é uma das principais operações do sistema.

Quando o utilizador seleciona:

Finalizar Época

o sistema executa uma operação transacional.

Fluxo:

Season atual
     │
     ▼
Criar SeasonSnapshot
     │
     ▼
Finalizar Season
     │
     ▼
Criar nova Season
     │
     ▼
Copiar jogadores que permanecem
     │
     ▼
Nova Season ativa

33. Rollover dos jogadores

Exemplo:
Época 25/26

Jogador X

BirthDate: 2004-03-10

Overall Inicial: 78
Overall Final: 82

Potential Inicial: 85
Potential Final: 87

Valor Inicial: €20M
Valor Final: €35M

Ao finalizar:
Época 26/27

Jogador X

BirthDate: 2004-03-10

Overall Inicial: 82
Overall Final: NULL

Potential Inicial: 87
Potential Final: NULL

Valor Inicial: €35M
Valor Final: NULL

A idade não é copiada.

Continua a existir apenas:

BirthDate

e a idade será calculada quando necessária.
34. Jogadores que saíram

Jogadores vendidos durante a época não são copiados para a nova época.

Exemplo:

Season 25/26

Jogador X
Venda

Ao finalizar:

Season 25/26
    └── Jogador X

Season 26/27
    └── Jogador X não aparece

O registo da venda permanece no histórico.
35. Transação do Finalizar Época

A operação deve ser executada dentro de uma única transação MySQL.

Conceptualmente:

BEGIN

Criar Snapshot

Finalizar Season

Criar nova Season

Criar SquadPlayers da nova Season

Atualizar estado necessário

COMMIT

Se ocorrer algum erro:

ROLLBACK

Desta forma não existe uma situação em que a época fica parcialmente finalizada.
36. Fluxo completo da aplicação

O fluxo principal do MVP será:

                    LOGIN
                      │
                      ▼
                  DASHBOARD
                      │
             ┌────────┴────────┐
             │                 │
             ▼                 ▼
       NOVA CARREIRA      CARREIRAS EXISTENTES
             │                 │
             ▼                 ▼
      Escolher versão       Selecionar
      Escolher clube          carreira
             │                 │
             └────────┬────────┘
                      ▼
                    ÉPOCA
                      │
          ┌───────────┼───────────┐
          │           │           │
          ▼           ▼           ▼
        SQUAD    TRANSFERÊNCIAS  TÁTICA
          │           │           │
          └───────────┼───────────┘
                      │
                      ▼
              OBJETIVOS / REGRAS
                      │
                      ▼
              FINALIZAR ÉPOCA
                      │
          ┌───────────┼───────────┐
          │           │           │
          ▼           ▼           ▼
      SNAPSHOT    FECHAR ÉPOCA   NOVA ÉPOCA
                                  │
                                  ▼
                              ROLLOVER
                                  │
                                  ▼
                               repetir

37. Fluxo dos dados iniciais

Existe ainda um fluxo separado para o catálogo:

Admin
  │
  ▼
Importar FC26
  │
  ▼
Yii 3
  │
  ▼
Python Service
  │
  ▼
soccerdata
  │
  ▼
Dados externos
  │
  ▼
Python Service
  │
  ▼
Yii 3
  │
  ├── GameVersions
  ├── Clubs
  └── PlayerCatalog

Depois disso, o utilizador pode criar uma carreira utilizando os dados já existentes.
38. Histórico

O sistema deve preservar a evolução da carreira:

Career
 │
 ├── Season 25/26
 │    ├── Squad
 │    ├── Transfers
 │    ├── Tactics
 │    └── Snapshot
 │
 ├── Season 26/27
 │    ├── Squad
 │    ├── Transfers
 │    ├── Tactics
 │    └── Snapshot
 │
 └── Season 27/28
      └── ...

O utilizador consegue assim consultar a evolução da carreira ao longo das épocas.
39. Segurança e isolamento entre utilizadores

Cada carreira pertence a um utilizador.

Um utilizador só pode aceder às suas próprias:

Careers
Seasons
SquadPlayers
Transfers
Tactics
CareerRules
Snapshots

O catálogo é partilhado:

GameVersions
Clubs
PlayerCatalog

O acesso deve ser validado no backend.

Não é suficiente confiar no ID recebido pelo browser.

Exemplo:

User A
  │
  └── Career 1

User B
  │
  └── Career 2

User A nunca pode editar:

Career 2

mesmo que tente enviar diretamente o ID através de um request.
40. Testes

Será utilizado PHPUnit.

O foco inicial dos testes deverá estar na lógica de negócio.
Finalizar época

Testar:

Cria snapshot
Finaliza season
Cria nova season
Copia OverallFinal
Copia PotentialFinal
Copia ValueFinal
Mantém BirthDate
Não copia jogadores vendidos
Mantém histórico
Faz rollback em caso de erro

Transferências

Testar:

Compra cria jogador
Venda remove jogador do plantel ativo
Venda remove jogador da tática ativa
Empréstimo altera estado
Transferência guarda valores históricos

Tática

Testar:

Ativar nova tática
Desativar anterior
Manter histórico
Não permitir jogador duplicado

Ownership

Testar:

Utilizador A não acede à carreira B
Utilizador A não edita plantel da carreira B
Utilizador A não edita transferências da carreira B

41. Roadmap do MVP
Fase 0 — Esqueleto

Criar:

Yii 3
MySQL
Composer
Onion Architecture
Domain
Application
Infrastructure
Web
Autenticação
Migrations
Bootstrap

Objetivo:

Login
↓
Dashboard

Fase 1 — Serviço Python / Catálogo

Criar:

Python Data Service
soccerdata
GameVersions
Clubs
PlayerCatalog

Primeiro objetivo:

Uma versão
Um clube
Jogadores desse clube

Fase 2 — Carreira e Squad

Criar:

Careers
Seasons
SquadPlayers

Fluxo:

Criar carreira
↓
Selecionar clube
↓
Criar época
↓
Popular plantel

Depois:

Página Squad
Edição
Filtros
Valorização

Fase 3 — Transferências

Implementar:

Compra
Venda
Empréstimo

Integrar com:

Squad
Transfers
Tactics

Fase 4 — Finalizar Época

Implementar:

Snapshot
Finalização
Nova Season
Rollover

Esta fase deve possuir testes fortes porque contém uma das principais regras de negócio.
Fase 5 — Tática

Implementar:

Formations
PositionSlots
PlayerRoles
Tactics
TacticSlots

Criar:

Campo visual
XI
Roles
Tática ativa
Histórico

Fase 6 — Objetivos e Regras

Implementar:

CareerRules

Com:

CRUD
Global
Período
Cumprido
Filtros

Fase 7 — Polish

Melhorias:

UX
Validações
Dashboard
Melhorias da Squad
Melhorias da importação
Mais versões
Mais clubes
Tratamento de erros
Feedback visual

42. Fora de âmbito do MVP

Ficam explicitamente fora do MVP:

    Sincronização automática com fontes externas.
    API pública.
    SPA.
    React.
    Vue.
    Angular.
    Aplicação mobile nativa.
    Estatísticas avançadas dos jogos.
    Golos.
    Assistências.
    Minutos jogados.
    Competições.
    Exportação Excel.
    Exportação PDF.
    Multi-idioma.
    Roles específicos por versão do jogo.
    Formações específicas por versão do jogo.
    Integração com APIs oficiais da EA.
    Sistema avançado de permissões.
    Notificações push.
    Auditoria completa de todas as alterações.
    Social features entre utilizadores.

43. Riscos e assunções
Risco	Mitigação
Dados externos indisponíveis	Importação manual/on-demand
soccerdata deixar de funcionar	Correção futura do serviço Python
Alteração da fonte externa	Aceite como risco do MVP
Serviço Python indisponível	Importação fica temporariamente indisponível
Grid não ser tão fluida como Excel	Manter UI simples
Erro no Finalizar Época	Transação + rollback
Alterações ao catálogo afetarem histórico	Dados históricos vivem em SquadPlayers e Transfers
Utilizador tentar aceder a outra carreira	Ownership checks
Crescimento excessivo da arquitetura	Manter Onion/DDD simples e orientado ao domínio
44. Modelo final das tabelas

O MVP fica com as seguintes tabelas:

users

game_versions
clubs
player_catalog

careers
seasons
squad_players
transfers
season_snapshots

formations
position_slots
player_roles
tactics
tactic_slots

career_rules

Todas possuem:

is_deleted
deleted_at

Além dos campos específicos de cada tabela.
45. Resumo da arquitetura final

A aplicação fica essencialmente:

                         Browser
                            │
                            ▼
                     Yii 3 Web Layer
                  Controllers / Views
                            │
                            ▼
                     Application Layer
                       Use Cases
                            │
                            ▼
                       Domain Layer
                  Entities / Rules / Logic
                            │
                            ▼
                  Infrastructure Layer
                            │
                  ┌─────────┴─────────┐
                  ▼                   ▼
                MySQL          Python Service
                                      │
                                      ▼
                                 soccerdata

O objetivo do MVP é manter o sistema simples:

Yii 3
+
Onion / DDD
+
MySQL
+
Bootstrap
+
HTML / CSS
+
JavaScript básico
+
Python + soccerdata

O fluxo principal continua a ser:

Criar carreira
      ↓
Criar época
      ↓
Gerir plantel
      ↓
Registar transferências
      ↓
Definir XI / tática
      ↓
Gerir objetivos
      ↓
Finalizar época
      ↓
Guardar histórico
      ↓
Criar nova época
      ↓
Repetir

O ponto mais importante do desenho é separar claramente dados de catálogo de dados históricos da carreira. O player_catalog fornece os dados iniciais, enquanto squad_players, transfers, tactics e season_snapshots representam o estado/histórico da carreira e não devem ser alterados por uma futura atualização do catálogo.