# Plataforma de Economia Compartilhada

Uma aplicação web em PHP para facilitar a economia compartilhada, permitindo aos usuários criar, explorar e gerenciar pedidos, além de oferecer produtos e serviços, com autenticação segura e chat em tempo real.

## Funcionalidades

- Autenticação de Usuários (Login/Registro)
- Sistema de Sessões Seguro
- Página Inicial Personalizada
- Exploração e Filtro de Pedidos Publicados
- Criação, Edição e Exclusão de Pedidos
- Upload e Visualização de Imagem do Produto
- Visualização de Status do Pedido
- Perfil de Usuário com Edição e Upload de Imagem
- Geolocalização para Pedidos Próximos
- Sistema de Categorias para Pedidos
- Interface Responsiva (Bootstrap 5 e/ou Tailwind)
- Pesquisa e Filtros Avançados de Pedidos
- Visualização Detalhada de Pedidos
- Chat em Tempo Real entre Usuários
- Histórico e Notificações de Mensagens
- Avatares Personalizados
- Indicadores de Status de Mensagem
- Busca de Usuários para Conversa

## Estrutura do Projeto

```
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── db.php
│   └── auth.php
├── classes/
│   ├── User.php
│   └── Request.php
├── pages/
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   ├── create_order.php
│   ├── edit_order.php
│   ├── view_order.php
│   ├── delete_order.php
│   ├── explore_orders.php
│   ├── chat.php
│   └── logout.php
├── uploads/
│   └── (imagens de produtos e perfis)
├── config/
│   └── db.php
└── index.php
```

## Configuração

1. Configure o banco de dados em `config/db.php` ou `includes/db.php`
2. Crie o banco de dados MySQL (ex: `request_system`)
3. Importe o esquema SQL fornecido abaixo
4. Inicie o servidor local (XAMPP recomendado) e acesse via navegador

## Requisitos

- PHP 8.0+
- MySQL 5.7+
- Navegador web moderno
- XAMPP ou similar para desenvolvimento

## Estrutura do Banco de Dados

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('publicado', 'rascunho', 'finalizado', 'cancelado') DEFAULT 'publicado',
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    product_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Como contribuir

1. Faça um fork do repositório
2. Crie uma branch para sua feature
3. Faça commit das alterações
4. Envie um Pull Request

---

**Observação:**  
- Os pedidos criados já são publicados automaticamente (`status = 'publicado'`).
- Apenas pedidos publicados aparecem na listagem de exploração.
- Imagens de produtos são exibidas nas páginas de detalhes e listagem.
- O sistema segue uma estrutura organizada, podendo ser adaptado para MVC facilmente.
