# Leads8 Mobile - Guia de Estilo

## 🎨 Design System

### Cores
```typescript
export const colors = {
  // Cores Primárias
  primary: {
    main: '#007AFF',
    light: '#47A3FF',
    dark: '#0055B3',
    contrast: '#FFFFFF',
  },
  
  // Cores Secundárias
  secondary: {
    main: '#5856D6',
    light: '#7A79E0',
    dark: '#3E3D96',
    contrast: '#FFFFFF',
  },
  
  // Estados
  success: {
    main: '#34C759',
    light: '#5FD37B',
    dark: '#248B3E',
    contrast: '#FFFFFF',
  },
  
  error: {
    main: '#FF3B30',
    light: '#FF6961',
    dark: '#B32921',
    contrast: '#FFFFFF',
  },
  
  warning: {
    main: '#FFCC00',
    light: '#FFD633',
    dark: '#B38F00',
    contrast: '#000000',
  },
  
  // Tons de Cinza
  grey: {
    50: '#F9FAFB',
    100: '#F3F4F6',
    200: '#E5E7EB',
    300: '#D1D5DB',
    400: '#9CA3AF',
    500: '#6B7280',
    600: '#4B5563',
    700: '#374151',
    800: '#1F2937',
    900: '#111827',
  },
  
  // Background e Texto
  background: {
    default: '#FFFFFF',
    paper: '#F3F4F6',
    elevated: '#FFFFFF',
  },
  
  text: {
    primary: '#000000',
    secondary: '#6B7280',
    disabled: '#9CA3AF',
    hint: '#4B5563',
  },
};
```

### Tipografia
```typescript
export const typography = {
  // Famílias de Fonte
  fontFamily: {
    regular: 'Inter-Regular',
    medium: 'Inter-Medium',
    semibold: 'Inter-SemiBold',
    bold: 'Inter-Bold',
  },
  
  // Tamanhos
  fontSize: {
    xs: 12,
    sm: 14,
    md: 16,
    lg: 18,
    xl: 20,
    '2xl': 24,
    '3xl': 30,
    '4xl': 36,
  },
  
  // Altura da Linha
  lineHeight: {
    tight: 1.25,
    normal: 1.5,
    relaxed: 1.75,
  },
  
  // Estilos Predefinidos
  styles: {
    h1: {
      fontSize: 30,
      fontFamily: 'Inter-Bold',
      lineHeight: 1.25,
    },
    h2: {
      fontSize: 24,
      fontFamily: 'Inter-Bold',
      lineHeight: 1.25,
    },
    h3: {
      fontSize: 20,
      fontFamily: 'Inter-SemiBold',
      lineHeight: 1.5,
    },
    body1: {
      fontSize: 16,
      fontFamily: 'Inter-Regular',
      lineHeight: 1.5,
    },
    body2: {
      fontSize: 14,
      fontFamily: 'Inter-Regular',
      lineHeight: 1.5,
    },
    caption: {
      fontSize: 12,
      fontFamily: 'Inter-Regular',
      lineHeight: 1.5,
    },
  },
};
```

### Espaçamento
```typescript
export const spacing = {
  // Base: 4px
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  '2xl': 48,
  '3xl': 64,
  
  // Específicos
  layout: {
    screenPadding: 16,
    sectionGap: 24,
    itemGap: 16,
  },
};
```

### Sombras
```typescript
export const shadows = {
  sm: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.18,
    shadowRadius: 1.0,
    elevation: 1,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.30,
    shadowRadius: 4.65,
    elevation: 8,
  },
};
```

### Bordas
```typescript
export const borders = {
  radius: {
    sm: 4,
    md: 8,
    lg: 12,
    xl: 16,
    full: 9999,
  },
  width: {
    thin: 1,
    normal: 2,
    thick: 3,
  },
};
```

## 🎯 Componentes Base

### Botões
```typescript
interface ButtonProps {
  variant: 'primary' | 'secondary' | 'outline' | 'ghost';
  size: 'sm' | 'md' | 'lg';
  isFullWidth?: boolean;
  isLoading?: boolean;
  isDisabled?: boolean;
  leftIcon?: IconName;
  rightIcon?: IconName;
  onPress: () => void;
}

const buttonStyles = {
  base: {
    borderRadius: borders.radius.md,
    padding: spacing.md,
  },
  variants: {
    primary: {
      backgroundColor: colors.primary.main,
      color: colors.primary.contrast,
    },
    secondary: {
      backgroundColor: colors.secondary.main,
      color: colors.secondary.contrast,
    },
    outline: {
      backgroundColor: 'transparent',
      borderWidth: borders.width.normal,
      borderColor: colors.primary.main,
      color: colors.primary.main,
    },
    ghost: {
      backgroundColor: 'transparent',
      color: colors.primary.main,
    },
  },
  sizes: {
    sm: {
      height: 32,
      fontSize: typography.fontSize.sm,
    },
    md: {
      height: 40,
      fontSize: typography.fontSize.md,
    },
    lg: {
      height: 48,
      fontSize: typography.fontSize.lg,
    },
  },
};
```

### Inputs
```typescript
interface InputProps {
  variant: 'outlined' | 'filled';
  size: 'sm' | 'md' | 'lg';
  label?: string;
  placeholder?: string;
  error?: string;
  helperText?: string;
  leftIcon?: IconName;
  rightIcon?: IconName;
  onChangeText: (text: string) => void;
}

const inputStyles = {
  base: {
    borderRadius: borders.radius.md,
    padding: spacing.md,
    fontFamily: typography.fontFamily.regular,
  },
  variants: {
    outlined: {
      borderWidth: borders.width.normal,
      borderColor: colors.grey[300],
      backgroundColor: 'transparent',
    },
    filled: {
      backgroundColor: colors.grey[100],
      borderWidth: 0,
    },
  },
  states: {
    focus: {
      borderColor: colors.primary.main,
    },
    error: {
      borderColor: colors.error.main,
    },
    disabled: {
      backgroundColor: colors.grey[100],
      opacity: 0.7,
    },
  },
};
```

### Cards
```typescript
interface CardProps {
  variant: 'elevated' | 'outlined';
  padding?: keyof typeof spacing;
  onPress?: () => void;
}

const cardStyles = {
  base: {
    borderRadius: borders.radius.lg,
    backgroundColor: colors.background.paper,
  },
  variants: {
    elevated: {
      ...shadows.md,
    },
    outlined: {
      borderWidth: borders.width.thin,
      borderColor: colors.grey[200],
    },
  },
};
```

## 📱 Layout

### Grid System
```typescript
const grid = {
  columns: 12,
  gutter: spacing.md,
  margin: spacing.lg,
  breakpoints: {
    xs: 0,
    sm: 360,
    md: 480,
    lg: 720,
    xl: 1024,
  },
};
```

### Containers
```typescript
const containers = {
  sm: 360,
  md: 480,
  lg: 720,
  xl: 1024,
  padding: {
    xs: spacing.md,
    sm: spacing.lg,
    md: spacing.xl,
  },
};
```

## 🎨 Ícones e Imagens

### Ícones
```typescript
const iconSizes = {
  sm: 16,
  md: 24,
  lg: 32,
  xl: 48,
};

const iconColors = {
  default: colors.grey[600],
  primary: colors.primary.main,
  secondary: colors.secondary.main,
  success: colors.success.main,
  error: colors.error.main,
  warning: colors.warning.main,
};
```

### Imagens
```typescript
const imageStyles = {
  aspectRatios: {
    square: 1,
    portrait: 4/3,
    landscape: 16/9,
  },
  borderRadius: {
    sm: borders.radius.sm,
    md: borders.radius.md,
    lg: borders.radius.lg,
    full: borders.radius.full,
  },
};
```

## 🔄 Animações

```typescript
const animations = {
  duration: {
    fast: 200,
    normal: 300,
    slow: 500,
  },
  easing: {
    easeIn: 'cubic-bezier(0.4, 0, 1, 1)',
    easeOut: 'cubic-bezier(0, 0, 0.2, 1)',
    easeInOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
  },
};
```

## 📱 Gestos

```typescript
const gestures = {
  pressable: {
    feedback: {
      opacity: 0.7,
      scale: 0.98,
    },
    duration: 100,
  },
  swipeable: {
    threshold: 50,
    velocity: 0.3,
  },
};
```

## 🌗 Temas

```typescript
const themes = {
  light: {
    ...colors,
    background: {
      default: '#FFFFFF',
      paper: '#F3F4F6',
    },
    text: {
      primary: '#000000',
      secondary: '#6B7280',
    },
  },
  dark: {
    ...colors,
    background: {
      default: '#111827',
      paper: '#1F2937',
    },
    text: {
      primary: '#FFFFFF',
      secondary: '#9CA3AF',
    },
  },
};
```

## 🎯 Boas Práticas

### Nomenclatura
- Componentes: PascalCase
- Funções: camelCase
- Variáveis: camelCase
- Constantes: UPPER_SNAKE_CASE
- Arquivos de componente: PascalCase.tsx
- Arquivos de utilitários: camelCase.ts

### Estrutura de Componentes
```typescript
// ComponentName/
// ├── index.tsx
// ├── styles.ts
// ├── types.ts
// └── ComponentName.test.tsx
```

### Padrões de Código
- Usar TypeScript strict mode
- Preferir funções puras
- Componentizar código repetitivo
- Documentar props com JSDoc
- Usar prop-types para validação

---

*Última atualização: 10/09/2025*


