# Arquitetura do Leads8 Mobile

## 📱 Visão Geral da Arquitetura

```
leads8-mobile/
├── src/
│   ├── app/                    # Configuração do app
│   ├── components/             # Componentes reutilizáveis
│   ├── features/              # Features principais
│   ├── services/              # Serviços e APIs
│   └── utils/                 # Utilitários
└── docs/                      # Documentação
```

## 🏗️ Estrutura de Diretórios Detalhada

### 1. Components
```
components/
├── common/                    # Componentes base
│   ├── Button/
│   ├── Input/
│   └── Card/
├── product/                   # Componentes de produto
│   ├── ProductCard/
│   ├── ProductList/
│   └── ProductDetails/
├── cart/                      # Componentes do carrinho
│   ├── CartItem/
│   ├── CartSummary/
│   └── CartActions/
└── layout/                    # Componentes de layout
    ├── Header/
    ├── Footer/
    └── Navigation/
```

### 2. Features
```
features/
├── auth/                      # Autenticação
│   ├── screens/
│   ├── components/
│   └── services/
├── catalog/                   # Catálogo de produtos
│   ├── screens/
│   ├── components/
│   └── services/
├── cart/                      # Carrinho
│   ├── screens/
│   ├── components/
│   └── services/
└── checkout/                  # Checkout
    ├── screens/
    ├── components/
    └── services/
```

### 3. Services
```
services/
├── api/                       # Serviços de API
│   ├── graphql/
│   └── rest/
├── storage/                   # Armazenamento local
│   ├── async-storage/
│   └── secure-storage/
└── analytics/                 # Analytics e tracking
```

## 🔄 Fluxo de Dados

### Estado Global (Redux)
```typescript
// Store Structure
interface RootState {
  auth: {
    user: User | null;
    token: string | null;
    isLoading: boolean;
  };
  catalog: {
    products: Product[];
    filters: FilterState;
    search: string;
  };
  cart: {
    items: CartItem[];
    total: number;
    discount: number;
  };
}
```

### Cache e Persistência
```typescript
// Cache Configuration
const cacheConfig = {
  storage: AsyncStorage,
  version: 1,
  ttl: 24 * 60 * 60 * 1000, // 24 hours
  invalidation: {
    products: ['UPDATE_PRODUCT', 'DELETE_PRODUCT'],
    cart: ['CLEAR_CART', 'CHECKOUT_SUCCESS'],
  },
};
```

## 🔒 Segurança

### Autenticação
- JWT Tokens
- Refresh Token Strategy
- Secure Storage para dados sensíveis

### Criptografia
- Dados sensíveis criptografados em repouso
- HTTPS para todas as comunicações
- Certificate Pinning

## 📱 UI/UX Guidelines

### Componentes Base
```typescript
// Button Component Example
interface ButtonProps {
  variant: 'primary' | 'secondary' | 'outline';
  size: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  icon?: IconName;
  onPress: () => void;
}
```

### Temas
```typescript
// Theme Configuration
const theme = {
  colors: {
    primary: '#007AFF',
    secondary: '#5856D6',
    success: '#34C759',
    danger: '#FF3B30',
    warning: '#FFCC00',
    background: '#FFFFFF',
    text: '#000000',
  },
  spacing: {
    xs: 4,
    sm: 8,
    md: 16,
    lg: 24,
    xl: 32,
  },
};
```

## 🔄 Integração com Backend

### GraphQL Schema
```graphql
type Product {
  id: ID!
  name: String!
  description: String
  price: Float!
  stock: Int!
  images: [String!]!
}

type Query {
  products(
    filter: ProductFilter
    pagination: PaginationInput
  ): ProductConnection!
  
  product(id: ID!): Product
}

type Mutation {
  addToCart(
    productId: ID!
    quantity: Int!
  ): Cart!
}
```

### API Endpoints
```typescript
const API_ENDPOINTS = {
  auth: {
    login: '/auth/login',
    refresh: '/auth/refresh',
    logout: '/auth/logout',
  },
  products: {
    list: '/products',
    detail: (id: string) => `/products/${id}`,
    search: '/products/search',
  },
  cart: {
    get: '/cart',
    add: '/cart/add',
    remove: '/cart/remove',
    clear: '/cart/clear',
  },
};
```

## 📊 Performance

### Métricas Chave
- Time to Interactive (TTI)
- First Contentful Paint (FCP)
- Bundle Size
- Memory Usage
- Network Requests

### Otimizações
```typescript
// Image Optimization
const ImageOptimizer = {
  quality: 0.8,
  maxWidth: 1200,
  cacheTimeout: 7 * 24 * 60 * 60 * 1000, // 7 days
  compressionRatio: 0.7,
};

// List Virtualization
const VirtualizedConfig = {
  windowSize: 5,
  initialNumToRender: 10,
  maxToRenderPerBatch: 10,
  updateCellsBatchingPeriod: 50,
};
```

## 🧪 Testes

### Estrutura de Testes
```
__tests__/
├── unit/
│   ├── components/
│   ├── services/
│   └── utils/
├── integration/
│   ├── features/
│   └── flows/
└── e2e/
    └── scenarios/
```

### Configuração de Testes
```typescript
// Jest Configuration
module.exports = {
  preset: 'react-native',
  setupFilesAfterEnv: ['@testing-library/jest-native/extend-expect'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/src/$1',
  },
  transformIgnorePatterns: [
    'node_modules/(?!(react-native|@react-native|@react-navigation)/)',
  ],
};
```

## 📱 Suporte a Dispositivos

### Requisitos Mínimos
- iOS 13+
- Android 6.0+
- 2GB RAM
- 100MB Espaço livre

### Adaptação de Tela
```typescript
const screenConfig = {
  minWidth: 320,
  maxWidth: 1024,
  breakpoints: {
    sm: 360,
    md: 480,
    lg: 720,
    xl: 1024,
  },
};
```

## 🔄 CI/CD

### Pipeline
1. Build
2. Testes Unitários
3. Testes de Integração
4. Code Quality
5. Build de Release
6. Deploy para Stores

### Ambientes
- Desenvolvimento
- Staging
- Produção

## 📚 Documentação Adicional

- [Setup Guide](./SETUP.md)
- [Contributing Guide](./CONTRIBUTING.md)
- [Testing Guide](./TESTING.md)
- [Style Guide](./STYLE_GUIDE.md)

---

*Última atualização: 10/09/2025*


