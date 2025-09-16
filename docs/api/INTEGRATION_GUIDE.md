# Guia de Integração - Leads8 Mobile API

## Introdução

Este guia fornece instruções detalhadas para integração com a API mobile do Leads8. A API foi projetada seguindo os princípios REST e utiliza JSON para comunicação.

## Índice

1. [Primeiros Passos](#primeiros-passos)
2. [Autenticação](#autenticação)
3. [Endpoints](#endpoints)
4. [Paginação](#paginação)
5. [Filtros](#filtros)
6. [Erros](#erros)
7. [Boas Práticas](#boas-práticas)
8. [Exemplos](#exemplos)
9. [Suporte](#suporte)

## Primeiros Passos

### Ambientes

A API está disponível nos seguintes ambientes:

```
Produção: https://api.leads8.com.br/v1
Homologação: https://api.staging.leads8.com.br/v1
Desenvolvimento: http://localhost:8000/v1
```

### Requisitos

- Credenciais de acesso (usuário e senha)
- HTTPS para todas as requisições em produção
- Content-Type: application/json

## Autenticação

A API utiliza autenticação via token JWT (JSON Web Token). Para obter um token:

1. Faça uma requisição POST para `/auth` com suas credenciais:

```json
{
    "username": "seu.email@exemplo.com",
    "password": "sua_senha",
    "device_id": "id_do_dispositivo" // opcional
}
```

2. A API retornará um token:

```json
{
    "error": false,
    "data": {
        "token": "seu.token.jwt",
        "user": {
            "id": 1,
            "name": "Nome do Usuário",
            "email": "seu.email@exemplo.com",
            "role": "sales",
            "permissions": ["create_lead", "edit_lead"]
        }
    }
}
```

3. Use o token em todas as requisições subsequentes no header `Authorization`:

```
Authorization: Bearer seu.token.jwt
```

### Renovação do Token

O token expira após 30 dias. Para renovar:

1. Faça uma requisição POST para `/refresh-token` com o token atual
2. A API retornará um novo token

### Logout

Para invalidar um token:

1. Faça uma requisição POST para `/logout`
2. Opcionalmente, envie o `device_id` para remover o dispositivo

## Endpoints

### Leads

#### Listar Leads

```http
GET /leads?page=1&limit=20&search=termo&status=pending
```

Parâmetros de query:
- `page`: Página atual (default: 1)
- `limit`: Itens por página (default: 20, max: 100)
- `search`: Termo de busca
- `status`: Status do lead (pending, completed, cancelled)
- `date_start`: Data inicial (YYYY-MM-DD)
- `date_end`: Data final (YYYY-MM-DD)

#### Criar Lead

```http
POST /leads

{
    "customer_id": 100,
    "items": [
        {
            "product_id": 100,
            "quantity": 2,
            "unit_price": 100.00,
            "discount": 10.00
        }
    ],
    "notes": "Observações do lead"
}
```

#### Atualizar Lead

```http
PUT /leads/1

{
    "status": "completed",
    "items": [...],
    "notes": "Novas observações"
}
```

#### Remover Lead

```http
DELETE /leads/1
```

### Produtos

#### Listar Produtos

```http
GET /products?page=1&limit=20&search=termo&category=1&brand=1&stock=available
```

Parâmetros de query:
- `page`: Página atual (default: 1)
- `limit`: Itens por página (default: 20, max: 100)
- `search`: Termo de busca
- `category`: ID da categoria
- `brand`: ID da marca
- `stock`: Filtro de estoque (available, unavailable)
- `min_price`: Preço mínimo
- `max_price`: Preço máximo
- `sort_by`: Campo de ordenação (code, name, price, stock)
- `sort_order`: Ordem (ASC, DESC)

#### Buscar Produto por Código de Barras

```http
GET /product/barcode?code=7891234567890
```

#### Buscar Produto por Código

```http
GET /product/code?code=PROD001
```

### Carrinho

#### Obter Carrinho

```http
GET /cart
```

#### Adicionar Item

```http
POST /cart

{
    "product_id": 100,
    "quantity": 2,
    "price": 100.00,
    "discount": 10.00
}
```

#### Atualizar Item

```http
PUT /cart

{
    "item_id": 1,
    "quantity": 3,
    "price": 90.00,
    "discount": 15.00
}
```

#### Remover Item

```http
DELETE /cart/1
```

#### Limpar Carrinho

```http
DELETE /cart
```

### Clientes

#### Listar Clientes

```http
GET /customers?page=1&limit=20&search=termo&status=active&type=pf
```

Parâmetros de query:
- `page`: Página atual (default: 1)
- `limit`: Itens por página (default: 20, max: 100)
- `search`: Termo de busca
- `status`: Status do cliente (active, inactive, blocked)
- `type`: Tipo do cliente (pf, pj)
- `state`: Estado
- `city`: Cidade
- `sort_by`: Campo de ordenação (name, email, document)
- `sort_order`: Ordem (ASC, DESC)

#### Buscar Cliente por Documento

```http
GET /customer/document?document=123.456.789-01
```

#### Buscar Cliente por Email

```http
GET /customer/email?email=cliente@exemplo.com
```

#### Listar Endereços

```http
GET /customer/addresses/100
```

#### Listar Contatos

```http
GET /customer/contacts/100
```

## Paginação

Todas as listagens são paginadas e retornam:

```json
{
    "error": false,
    "data": {
        "total": 100,
        "page": 1,
        "limit": 20,
        "total_pages": 5,
        "items": [...]
    }
}
```

## Filtros

- Busca textual: Usa `LIKE` case-insensitive
- Filtros de data: Formato YYYY-MM-DD
- Filtros numéricos: Valores exatos ou ranges
- Ordenação: Campos específicos, ASC ou DESC

## Erros

A API usa códigos HTTP padrão:

- 200: Sucesso
- 201: Criado
- 400: Erro de validação
- 401: Não autorizado
- 403: Proibido
- 404: Não encontrado
- 429: Muitas requisições
- 500: Erro interno

Formato de erro:

```json
{
    "error": true,
    "message": "Descrição do erro"
}
```

## Boas Práticas

1. **Cache**
   - Use cache para dados que mudam pouco
   - Respeite os headers de cache
   - Implemente cache offline

2. **Rate Limiting**
   - Limite: 100 requisições por minuto
   - Use exponential backoff em caso de erro

3. **Otimização**
   - Use compressão GZIP
   - Minimize número de requisições
   - Implemente batch operations

4. **Segurança**
   - Use HTTPS sempre
   - Não armazene tokens em plain text
   - Implemente certificate pinning

## Exemplos

### Fluxo de Venda

1. Autenticação:
```javascript
const response = await fetch('https://api.leads8.com.br/v1/auth', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: 'vendedor@exemplo.com',
        password: 'senha123'
    })
});

const { data } = await response.json();
const token = data.token;
```

2. Busca de Produtos:
```javascript
const response = await fetch('https://api.leads8.com.br/v1/products?stock=available', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

const { data } = await response.json();
const products = data.products;
```

3. Adicionar ao Carrinho:
```javascript
const response = await fetch('https://api.leads8.com.br/v1/cart', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        product_id: 100,
        quantity: 2
    })
});

const { data } = await response.json();
const cart = data;
```

4. Finalizar Lead:
```javascript
const response = await fetch('https://api.leads8.com.br/v1/leads', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        customer_id: 100,
        items: cart.items,
        notes: 'Pedido via app'
    })
});

const { data } = await response.json();
const lead = data;
```

## Suporte

- Email: suporte@leads8.com.br
- Documentação: https://docs.leads8.com.br
- Status da API: https://status.leads8.com.br


