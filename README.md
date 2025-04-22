
# Sistema de Gerenciamento de Solicitações

Uma aplicação web baseada em PHP para gerenciar solicitações de usuários com autenticação e funcionalidade de chat em tempo real.

## Funcionalidades

- Autenticação de Usuário (Login/Registro)
- Gerenciamento de Solicitações
- Painel do Usuário
- Chat em Tempo Real
- Gerenciamento de Perfil
- Teste de Conexão com Banco de Dados

## Estrutura do Projeto

```
├── assets/  
│   ├── css/ (Bootstrap e estilos personalizados)
│   ├── js/ (Scripts JavaScript)
│   ├── img/ (Ícones e logos)
├── includes/  
│   ├── header.php (Cabeçalho comum)
│   ├── footer.php (Rodapé comum)
│   ├── db.php (Conexão MySQL)
│   ├── auth.php (Funções de autenticação)
├── classes/  
│   ├── User.php (Classe de gerenciamento de usuário)
│   ├── Request.php (Classe de gerenciamento de solicitações)
├── pages/  
│   ├── login.php (Login de usuário)
│   ├── register.php (Registro de usuário)
│   ├── dashboard.php (Listagem de solicitações)
│   ├── profile.php (Perfil do usuário)
│   ├── create_request.php (Criar novas solicitações)
│   ├── chat.php (Chat em tempo real)
└── config.php (Configurações globais)
```

## Configuração

1. Configure suas definições de banco de dados em `config.php`
2. Crie um banco de dados MySQL chamado `request_system`
3. Importe o esquema do banco de dados (se fornecido)
4. Inicie o servidor PHP: `php -S 0.0.0.0:8000`

## Requisitos

- PHP 8.0+
- MySQL 5.7+
- Navegador web moderno

## Autenticação

O sistema utiliza autenticação baseada em sessão com as seguintes funcionalidades:
- Hash seguro de senha
- Gerenciamento de sessão
- Funcionalidade de login/logout
- Registro com verificação de e-mail

## Estrutura do Banco de Dados

O sistema utiliza MySQL com as seguintes tabelas principais:
- users (usuários)
- requests (solicitações)
- messages (mensagens)

## Contribuindo

1. Faça um fork do repositório
2. Crie sua branch de funcionalidade
3. Commit suas alterações
4. Push para a branch
5. Crie um novo Pull Request
