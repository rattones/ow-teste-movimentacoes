# ow-teste-movimentacoes
teste para a Ow-Interact - movimentações financeiras

- desenvolver uma API restful em PHP para controle de movimentações financeiras

## Requisitos

- PHP 7.4 com:
  - MySQLi driver
  - mbString
  - cURL

- Docker
  - Docker-compose

## Executando

- clonar este respositório numa pasta
- para criar o banco de dados executando `docker-compose up -d`
- criar as tabelas do bando de dados executando `php spark migrate`
- executando o projeto com `php spark serve`

### Configurando o acesso ao banco

Editar o arquivo App/Config/Database.php e alterar o _IP_ do **hostname** para o _IP_ de onde está sendo executado o serviço de banco de dados

## Endpoints
**baseUrl** _http://localhost:8080_

### Usuários

> GET:/usuarios => retorna a lista de usuários cadastrados

> GET:/usuarios/(usuario_id) => retorna os dados de um usuário

> POST:/ususarios => cria um usuário
  - **nome**: texto | obrigatório
  - **email**: texto | obrigatório | email válido
  - **data_nascimento**: data | obrigatório | data válida
  - **saldo**: numérico | padrão: _0_

> PUT:/usuarios => altera o saldo do usuário
  - **saldo**: numérico

> DEL:/usuario/(usuario_id) => exclui um usuário

### Movimentações

necessário enviar o (usuario_id) como **Bearer Token** para todas as chamadas deste endpoint

> GET:/movimentacoes[/pagina] => retorna as movimentações do usuário de 10 em 10 conforme a página solicitada
  - **pagina**: numérico | padrão: _0_

> GET:/movimentacoes/(movimentacoes_id) => retorna os dados de uma movimentação do usuário

> POST:/movimentacoes => insere uma movimentação para o usuário
  - **datahora**: data hora | obrigatório | data hora válido
  - **motivo**: texto | obrigatório
  - **valor**: numérico | obrigatório
  - **tipo**: text | obrigatório | valor válidos ( _débito_, _crédito_, _estorno_ ) | padrão: _débito_

> DEL:/movimentacoes/(movimentacoes_id) => exclui uma movimentação do usuário

### Saldo

necessário enviar o (usuario_id) como **Bearer Token** para todas as chamadas deste endpoint

> GET:/saldo => retorna o saldo do usuário

### Relatórios

necessário enviar o (usuario_id) como **Bearer Token** para todas as chamadas deste endpoint

> POST:/report => retorna um arquivo .CSV com todas as movimentações do usuário
  - **tipo**: obrigatório | valor válidos ( _30_, _YYYY-MM_, _vazio_ )
    - **30**: retorna a movimentação dos últimos 30 dias do usuário
    - **YYYY-MM**: retorna a movimenação do período selecionado do usuário
    - **vazio**: retorna toda a movimentação do usuário
