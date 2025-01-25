# Aplicação Laravel - API de Gestão de Colaboradores

## Sobre o Projeto

Esta API Laravel permite o cadastro e autenticação de empresas (usuário admin) e upload de arquivo .csv para cadastro de colaboradores e documentação completa com Swagger.

### Pré-requisitos

Certifique-se de que sua máquina atenda aos seguintes requisitos:

PHP >= 8.1

Composer

Banco de Dados (MySQL, PostgreSQL, SQLite ou outro suportado pelo Laravel)


## Configuração do Projeto

1. Clonar o Repositório

```git
git clone https://github.com/luizcsbh/api-employee-management.git
cd api-employee-management
```

2. Instalar Dependências do PHP

```composer
composer install
```

3. Configurar o Arquivo .env

Renomeie o arquivo .env.example para .env:

cp .env.example .env

Abra o arquivo .env e configure as seguintes variáveis de ambiente:

Configuração do Banco de Dados
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```
Configuração de E-mail

Para receber notificações por e-mails use provedor de e-mail e configure
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.exemplo.com
MAIL_PORT=587
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu_email@exemplo.com
MAIL_FROM_NAME="Nome do Remetente"
```
Configuração do JWT
```
JWT_SECRET=gerar_seu_segredo_jwt
```
Para gerar o segredo JWT:
```
php artisan jwt:secret
```
4. Executar Migrações e Seeders

Crie as tabelas no banco de dados.

```
php artisan migrate 
```

5. Gerar a Chave da Aplicação

```
php artisan key:generate
```

6. Iniciar o Servidor

Inicie o servidor de desenvolvimento:

```
php artisan serve
```

7.   Iniciar o Worker da Fila

Os Jobs só serão processados se um worker estiver rodando. Para iniciar o worker, use o comando:
```
php artisan queue:work
```
Para rodar o worker em produção de forma contínua, use o supervisor (detalhes abaixo).

7.1. Configurar o Supervisor (Produção)

O Supervisor é um gerenciador de processos usado para manter os workers ativos em segundo plano. Instale o Supervisor no servidor de produção e configure-o para monitorar os workers.

7.2. Monitorar Falhas

Caso algum Job falhe, você pode monitorar e registrar as falhas. Use o comando abaixo para ver os Jobs com falhas:
````
php artisan queue:failed
````
Para tentar novamente os Jobs com falhas:
```
php artisan queue:retry all
```

8.  Configuração do Swagger 

8.1. Publicar o Configurador do Swagger

Execute o comando abaixo para publicar as configurações do Swagger:
````
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
````
Isso irá gerar o arquivo de configuração config/l5-swagger.php, onde você pode ajustar detalhes do Swagger.

8.2. Configurar o .env para o Swagger

No arquivo .env, adicione ou ajuste as seguintes configurações para o Swagger:

```env
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000/api
```
- L5_SWAGGER_GENERATE_ALWAYS: Gera automaticamente a documentação sempre que a aplicação é acessada.
- L5_SWAGGER_CONST_HOST: Define a URL base da API.

8.3. Gerar a Documentação do Swagger

```
php artisan l5-swagger:generate
```
8.4. Acessar a Documentação do Swagger

A documentação da API é gerada com Swagger e pode ser acessada no navegador:

```
http://localhost:8000/api/documentation
```


9. Configuração de Segurança da API

A API utiliza autenticação JWT. Para acessar rotas protegidas:

Faça login com um usuário válido na rota de login (/api/login).

Copie o token recebido.

Insira o token na seção "Authorize" da documentação Swagger no formato:

Bearer <seu_token>

Estrutura Principal de Rotas

Autenticação

POST /api/register: Registrar um novo usuário

POST /api/login: Autenticar usuário e gerar token

POST /api/logout: Logout do usuário autenticado (Protegida)

Empresas

GET /api/companies: Listar empresas associadas ao usuário (Protegida)

POST /api/companies: Criar nova empresa (Protegida)

GET /api/companies/{id}: Obter detalhes de uma empresa (Protegida)

PUT /api/companies/{id}: Atualizar informações de uma empresa (Protegida)

DELETE /api/companies/{id}: Excluir uma empresa (Protegida)

10. Logs

Os logs de erro e eventos estão configurados para serem salvos no arquivo storage/logs/laravel.log. Certifique-se de que a pasta storage tem permissões corretas para gravação.

## Suporte

Para dúvidas ou suporte, entre em contato pelo e-mail: luizcsdev@gmail.com.

