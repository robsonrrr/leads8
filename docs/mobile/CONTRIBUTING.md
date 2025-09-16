# Guia de Contribuição - Leads8 Mobile

## 🚀 Começando

### Pré-requisitos
- Node.js 18+
- Yarn ou npm
- React Native CLI
- XCode (para iOS)
- Android Studio (para Android)
- VS Code (recomendado)

### Setup do Ambiente
```bash
# Clone o repositório
git clone [repo-url]
cd leads8-mobile

# Instale as dependências
yarn install

# Instale os pods (iOS)
cd ios && pod install && cd ..

# Inicie o Metro bundler
yarn start

# Execute no iOS
yarn ios

# Execute no Android
yarn android
```

## 📝 Processo de Desenvolvimento

### 1. Branches
```bash
# Padrão de nomenclatura
feature/[nome-da-feature]
bugfix/[nome-do-bug]
hotfix/[nome-do-hotfix]
release/[versão]

# Exemplo
git checkout -b feature/cart-improvements
```

### 2. Commits
```bash
# Formato
<tipo>(<escopo>): <descrição>

# Tipos
feat: Nova funcionalidade
fix: Correção de bug
docs: Documentação
style: Formatação
refactor: Refatoração
test: Testes
chore: Manutenção

# Exemplo
feat(cart): adiciona funcionalidade de desconto
```

### 3. Pull Requests
- Use o template de PR fornecido
- Inclua screenshots/GIFs para mudanças visuais
- Vincule as issues relacionadas
- Aguarde review de pelo menos 2 desenvolvedores

## 🧪 Testes

### Executando Testes
```bash
# Testes unitários
yarn test

# Testes com coverage
yarn test:coverage

# Testes e2e (iOS)
yarn e2e:ios

# Testes e2e (Android)
yarn e2e:android
```

### Escrevendo Testes
```typescript
// Exemplo de teste de componente
import { render, fireEvent } from '@testing-library/react-native';

describe('ProductCard', () => {
  it('should handle add to cart', () => {
    const onAddToCart = jest.fn();
    const { getByText } = render(
      <ProductCard 
        product={mockProduct}
        onAddToCart={onAddToCart}
      />
    );
    
    fireEvent.press(getByText('Adicionar ao Carrinho'));
    expect(onAddToCart).toHaveBeenCalled();
  });
});
```

## 📱 Padrões de Código

### Componentes
```typescript
// Componente funcional com TypeScript
import React from 'react';
import { View, Text } from 'react-native';
import { styles } from './styles';
import { ComponentProps } from './types';

export const MyComponent: React.FC<ComponentProps> = ({
  title,
  onPress,
}) => {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>{title}</Text>
    </View>
  );
};
```

### Hooks
```typescript
// Hook customizado
import { useState, useEffect } from 'react';

export const useDebounce = (value: string, delay: number) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};
```

### Estilos
```typescript
// Arquivo de estilos
import { StyleSheet } from 'react-native';
import { theme } from '@/theme';

export const styles = StyleSheet.create({
  container: {
    padding: theme.spacing.md,
    backgroundColor: theme.colors.background,
  },
  title: {
    ...theme.typography.h1,
    color: theme.colors.text.primary,
  },
});
```

## 📦 Gerenciamento de Estado

### Redux
```typescript
// Slice
import { createSlice, PayloadAction } from '@reduxjs/toolkit';

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    addItem: (state, action: PayloadAction<CartItem>) => {
      state.items.push(action.payload);
    },
    removeItem: (state, action: PayloadAction<string>) => {
      state.items = state.items.filter(
        item => item.id !== action.payload
      );
    },
  },
});
```

### Queries
```typescript
// GraphQL Query
const GET_PRODUCTS = gql`
  query GetProducts($filter: ProductFilter) {
    products(filter: $filter) {
      id
      name
      price
      stock
      images {
        url
        alt
      }
    }
  }
`;
```

## 🔍 Code Review

### Checklist
- [ ] Código segue style guide
- [ ] Testes implementados
- [ ] Documentação atualizada
- [ ] Performance otimizada
- [ ] Sem warnings/erros
- [ ] Acessibilidade verificada

### Performance
- Usar `useMemo` e `useCallback` apropriadamente
- Implementar virtualização para listas longas
- Otimizar imagens e assets
- Minimizar re-renders

## 📱 Build e Deploy

### Versão
```bash
# Incrementar versão
yarn version

# Build iOS
yarn build:ios

# Build Android
yarn build:android
```

### Release
1. Atualizar CHANGELOG.md
2. Criar tag de versão
3. Gerar builds
4. Submeter para as stores

## 🐛 Reportando Bugs

### Template de Issue
```markdown
## Descrição
[Descrição clara e concisa do bug]

## Passos para Reproduzir
1. Vá para '...'
2. Clique em '....'
3. Scroll até '....'
4. Veja o erro

## Comportamento Esperado
[O que deveria acontecer]

## Screenshots
[Se aplicável]

## Ambiente
- Device: [ex: iPhone 12]
- OS: [ex: iOS 15.0]
- App Version: [ex: 1.0.0]
```

## 📚 Recursos Úteis

- [React Native Docs](https://reactnative.dev/docs/getting-started)
- [TypeScript Docs](https://www.typescriptlang.org/docs)
- [Redux Toolkit Docs](https://redux-toolkit.js.org/)
- [React Navigation Docs](https://reactnavigation.org/)
- [Native Base Docs](https://nativebase.io/)

---

*Última atualização: 10/09/2025*


