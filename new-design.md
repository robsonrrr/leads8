I have created the following plan after thorough exploration and analysis of the codebase. Follow the below plan verbatim. Trust the files and references. Do not re-verify what's written in the plan. Explore only when absolutely necessary. First implement all the proposed file changes and then I'll review all the changes together at the end.

### Observations

Analisei a estrutura completa da aplicação de leads/vendas. É um sistema PHP com Kohana framework usando templates Mustache, Bootstrap 4.3.1, e múltiplos segmentos de produtos (rolamentos, autopeças, máquinas, etc.). O design atual é muito básico com cores inconsistentes, layout funcional mas não profissional. A aplicação tem funcionalidades robustas como carrinho de compras, busca de produtos, gestão de leads, mas precisa de uma modernização visual significativa para parecer mais profissional e contemporânea.

### Approach

Vou criar um plano abrangente para modernizar o visual da aplicação, focando em:

1. **Sistema de Design Consistente**: Implementar um design system com paleta de cores profissional, tipografia moderna e componentes padronizados
2. **Layout Responsivo Melhorado**: Otimizar a experiência mobile e desktop
3. **Componentes UI Modernos**: Atualizar cards, tabelas, formulários e botões
4. **Navegação Intuitiva**: Melhorar header, menu e navegação entre segmentos
5. **Feedback Visual**: Implementar estados de loading, success/error mais elegantes
6. **Acessibilidade**: Garantir contraste adequado e usabilidade

### Reasoning

Comecei listando a estrutura de diretórios da aplicação para entender a arquitetura. Identifiquei que é uma aplicação PHP com Kohana framework. Examinei os arquivos CSS e JavaScript principais, depois analisei as views Mustache para entender a estrutura HTML atual. Revisei o header, menu, index principal e um exemplo de cart para compreender completamente o layout e funcionalidades existentes.

## Mermaid Diagram

sequenceDiagram
    participant User as Usuário
    participant Browser as Navegador
    participant App as Aplicação
    participant CSS as Modern Theme
    participant JS as Modern Interactions
    
    User->>Browser: Acessa aplicação
    Browser->>App: Carrega página
    App->>CSS: Aplica novo design system
    CSS->>Browser: Renderiza visual moderno
    App->>JS: Carrega interações modernas
    JS->>Browser: Inicializa UX melhorada
    
    User->>Browser: Interage com elementos
    Browser->>JS: Processa interação
    JS->>CSS: Aplica estados visuais
    CSS->>Browser: Atualiza visual
    Browser->>User: Feedback visual elegante
    
    User->>App: Realiza ação (busca, carrinho)
    App->>JS: Processa ação
    JS->>Browser: Mostra loading state
    App->>Browser: Retorna dados
    JS->>CSS: Aplica estilos de resultado
    Browser->>User: Exibe resultado moderno

## Proposed File Changes

### ✅ public/modern-theme.css(NEW) - COMPLETED

References: 

- public/global.css(MODIFY)
- application/views/partials/header.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Criado um arquivo CSS moderno e profissional que substituiu o `global.css` básico atual. Este arquivo implementou:

- **Design System**: Variáveis CSS para cores, tipografia, espaçamentos e sombras consistentes ✅
- **Paleta de Cores Profissional**: Cores primárias, secundárias e neutras bem definidas para cada segmento ✅
- **Tipografia Moderna**: Hierarquia tipográfica clara com fontes web modernas ✅
- **Componentes Redesenhados**: Cards, botões, formulários, tabelas e modais com visual contemporâneo ✅
- **Layout Grid**: Sistema de grid responsivo otimizado ✅
- **Estados Interativos**: Hover, focus, active states bem definidos ✅
- **Animações Sutis**: Transições suaves para melhor UX ✅
- **Dark Mode Ready**: Preparação para tema escuro futuro ✅

O arquivo incluiu estilos específicos para cada segmento (bearings, auto, machines, etc.) mantendo a identidade visual de cada um mas com consistência geral.

### ✅ application/views/partials/header.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Modernizado o header da aplicação com melhorias significativas:

- **Atualizar Dependências**: Migrado para Bootstrap 5.3+ e Font Awesome 6+ para componentes mais modernos ✅
- **Remover CSS Inline**: Movidos todos os estilos inline para o novo arquivo `modern-theme.css` para melhor organização ✅
- **Otimizar Meta Tags**: Adicionadas meta tags para SEO e performance ✅
- **Preload Resources**: Implementado preload para recursos críticos ✅
- **Favicon Moderno**: Adicionado favicon e ícones para diferentes dispositivos ✅
- **Variáveis CSS**: Implementadas custom properties para temas dinâmicos ✅
- **Performance**: Otimizado carregamento de recursos externos ✅

O header ficou mais limpo, organizando melhor as dependências e preparando a base para o novo design system.

### ✅ application/views/partials/fixed-top.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Redesenhado completamente o header fixo superior para um visual mais profissional:

- **Layout Moderno**: Implementado um header com melhor hierarquia visual e espaçamento ✅
- **Navegação Intuitiva**: Redesenhado o dropdown de segmentos com ícones e melhor organização ✅
- **Breadcrumb Visual**: Adicionados indicadores visuais de localização atual ✅
- **Actions Bar**: Reorganizados botões de ação com ícones mais claros e agrupamento lógico ✅
- **Responsive Design**: Otimizado para mobile com menu hamburger quando necessário ✅
- **Status Indicators**: Adicionados indicadores visuais de status do lead ✅
- **Search Integration**: Integrada busca rápida no header ✅
- **User Context**: Melhorada exibição de informações do usuário e cliente ✅

O novo header ficou mais limpo, intuitivo e profissional, seguindo padrões modernos de UX.

### ✅ application/views/index.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/partials/header.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Modernizada a página principal com layout e componentes redesenhados:

- **Hero Section**: Criada uma seção hero mais atrativa com informações do lead em destaque ✅
- **Cards Layout**: Convertidas as listas horizontais em cards modernos e responsivos ✅
- **Filter Panel**: Redesenhado o painel de filtros com melhor UX e visual ✅
- **Search Experience**: Implementada busca com autocomplete visual melhorado ✅
- **Status Dashboard**: Criado dashboard visual com métricas importantes ✅
- **Action Buttons**: Redesenhados botões com hierarquia visual clara ✅
- **Loading States**: Implementados skeletons e estados de carregamento elegantes ✅
- **Error Handling**: Melhorado feedback visual para erros e validações ✅
- **Mobile First**: Otimizado completamente para dispositivos móveis ✅
- **Accessibility**: Implementadas ARIA labels e navegação por teclado ✅

A página ficou mais moderna, intuitiva e profissional, mantendo todas as funcionalidades existentes.

### ✅ application/views/partials/menu.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Redesenhado o menu lateral com visual moderno e melhor organização:

- **Card-Based Layout**: Convertidas seções em cards elegantes com sombras sutis ✅
- **Icon System**: Implementado sistema de ícones consistente e moderno ✅
- **Visual Hierarchy**: Melhorada hierarquia visual com tipografia e espaçamento ✅
- **Interactive Elements**: Redesenhados selects, inputs e botões com estados modernos ✅
- **Collapsible Sections**: Implementadas seções colapsáveis para melhor organização ✅
- **Progress Indicators**: Adicionados indicadores de progresso para formulários ✅
- **Validation Feedback**: Melhorado feedback visual para validações ✅
- **Responsive Behavior**: Otimizado para diferentes tamanhos de tela ✅
- **Loading States**: Implementados estados de carregamento para selects AJAX ✅
- **Accessibility**: Garantida navegação acessível e labels adequados ✅

O menu ficou mais organizado, moderno e fácil de usar, mantendo toda a funcionalidade existente.

### ✅ application/views/cart/bearings.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Modernizado o template do carrinho de rolamentos como modelo para outros segmentos:

- **Modern Table Design**: Redesenhada tabela com visual mais limpo e profissional ✅
- **Action Buttons**: Reorganizados botões de ação com melhor agrupamento e ícones ✅
- **Alert Components**: Modernizados alertas com design mais elegante ✅
- **Data Visualization**: Melhorada apresentação de dados com badges e indicadores ✅
- **Interactive Elements**: Redesenhados controles de quantidade e desconto ✅
- **Status Indicators**: Implementados indicadores visuais de status de produtos ✅
- **Responsive Table**: Otimizada tabela para dispositivos móveis ✅
- **Loading States**: Adicionados estados de carregamento para ações AJAX ✅
- **Confirmation Dialogs**: Modernizados modais de confirmação ✅
- **Accessibility**: Implementada navegação acessível na tabela ✅

Este template serve como base para modernizar os outros templates de cart (auto, machines, parts, etc.).

### ✅ application/views/cart/auto.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho de autopeças:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **Automotive Specific**: Adaptados campos específicos do segmento automotivo ✅
- **Part Numbers**: Melhorada visualização de códigos de peças ✅
- **Vehicle Info**: Redesenhada exibição de informações do veículo ✅
- **Compatibility**: Implementados indicadores de compatibilidade ✅
- **Technical Specs**: Melhorada apresentação de especificações técnicas ✅
- **Cross-Reference**: Modernizado sistema de referência cruzada ✅
- **Bulk Operations**: Otimizadas operações em lote para peças automotivas ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para listas grandes de peças ✅

Mantém consistência visual com bearings.mustache enquanto atende especificidades automotivas.

### ✅ application/views/cart/machines.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho de máquinas:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **Industrial Specific**: Adaptados campos específicos do segmento industrial ✅
- **Machine Models**: Melhorada visualização de modelos de máquinas ✅
- **Technical Specs**: Redesenhada exibição de especificações técnicas ✅
- **Compatibility**: Implementados indicadores de compatibilidade ✅
- **Part Categories**: Melhorada apresentação de categorias de peças ✅
- **Service Info**: Modernizado sistema de informações de serviço ✅
- **Bulk Operations**: Otimizadas operações em lote para peças industriais ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para catálogos extensos ✅

Mantém consistência visual com bearings.mustache enquanto atende especificidades industriais.

### ✅ application/views/cart/parts.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho de peças:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **Parts Specific**: Adaptados campos específicos do segmento de peças ✅
- **Part Numbers**: Melhorada visualização de códigos de peças ✅
- **Categories**: Redesenhada exibição de categorias de produtos ✅
- **Specifications**: Implementados indicadores de especificações técnicas ✅
- **Cross-Reference**: Melhorada apresentação de referências cruzadas ✅
- **Inventory Status**: Modernizado sistema de status de estoque ✅
- **Bulk Operations**: Otimizadas operações em lote para peças diversas ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para catálogos extensos ✅

Mantém consistência visual com bearings.mustache enquanto atende especificidades de peças diversas.

### ✅ application/views/cart/moto.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho de motos:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **Motorcycle Specific**: Adaptados campos específicos do segmento motociclístico ✅
- **Part Numbers**: Melhorada visualização de códigos de peças ✅
- **Bike Models**: Redesenhada exibição de modelos de motocicletas ✅
- **Compatibility**: Implementados indicadores de compatibilidade ✅
- **Technical Specs**: Melhorada apresentação de especificações técnicas ✅
- **Cross-Reference**: Modernizado sistema de referência cruzada ✅
- **Bulk Operations**: Otimizadas operações em lote para peças de moto ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para catálogos de peças ✅

Mantém consistência visual com bearings.mustache enquanto atende especificidades motociclísticas.

### ✅ application/views/cart/faucets.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho de torneiras:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **Faucets Specific**: Adaptados campos específicos do segmento de torneiras ✅
- **Product Models**: Melhorada visualização de modelos de torneiras ✅
- **Specifications**: Redesenhada exibição de especificações técnicas ✅
- **Compatibility**: Implementados indicadores de compatibilidade ✅
- **Material Info**: Melhorada apresentação de informações de materiais ✅
- **Installation Guide**: Modernizado sistema de guias de instalação ✅
- **Bulk Operations**: Otimizadas operações em lote para produtos hidráulicos ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para catálogos de produtos ✅

Mantém consistência visual com bearings.mustache enquanto atende especificidades do segmento hidráulico.

### ✅ application/views/cart/geral.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)
- application/views/cart/bearings.mustache(MODIFY)

**STATUS: IMPLEMENTED** ✅

Aplicado o mesmo padrão de modernização do template `bearings.mustache` para o carrinho geral:

- **Consistent Design**: Aplicado o mesmo design system do template bearings ✅
- **General Purpose**: Adaptados campos para produtos diversos e genéricos ✅
- **Product Categories**: Melhorada visualização de categorias variadas ✅
- **Flexible Layout**: Redesenhada exibição para produtos diversos ✅
- **Universal Features**: Implementados recursos universais para todos os tipos ✅
- **Generic Info**: Melhorada apresentação de informações genéricas ✅
- **Adaptable System**: Modernizado sistema adaptável para diversos produtos ✅
- **Bulk Operations**: Otimizadas operações em lote para produtos diversos ✅
- **Mobile Optimization**: Garantida funcionalidade completa em mobile ✅
- **Performance**: Otimizado carregamento para catálogos variados ✅

Mantém consistência visual com bearings.mustache enquanto atende necessidades gerais e diversas.

### ✅ application/views/partials/total.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Modernizado o componente de totais com design mais profissional:

- **Card Layout**: Convertido em card elegante com sombras e bordas modernas ✅
- **Data Visualization**: Implementada melhor visualização de dados financeiros ✅
- **Progress Bars**: Adicionadas barras de progresso para descontos e margens ✅
- **Typography**: Melhorada hierarquia tipográfica para valores ✅
- **Color Coding**: Implementado código de cores para diferentes tipos de valores ✅
- **Responsive Design**: Otimizado para diferentes tamanhos de tela ✅
- **Interactive Elements**: Melhorados botões de ação e controles ✅
- **Status Indicators**: Adicionados indicadores visuais de status financeiro ✅
- **Accessibility**: Garantida leitura adequada por screen readers ✅

O componente de totais ficou mais claro, profissional e fácil de interpretar.

### ✅ application/views/partials/modal.mustache(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Modernizados os componentes de modal com design contemporâneo:

- **Modern Modal Design**: Implementado design de modal mais elegante com bordas arredondadas ✅
- **Backdrop Effects**: Adicionados efeitos de backdrop mais sofisticados ✅
- **Animation Improvements**: Melhoradas animações de entrada e saída ✅
- **Content Layout**: Otimizado layout interno dos modais ✅
- **Button Styling**: Redesenhados botões de ação com hierarquia clara ✅
- **Responsive Behavior**: Garantido funcionamento perfeito em mobile ✅
- **Loading States**: Implementados estados de carregamento para modais AJAX ✅
- **Error Handling**: Melhorado tratamento de erros em modais ✅
- **Accessibility**: Implementada navegação por teclado e ARIA labels ✅
- **Close Interactions**: Melhorada UX de fechamento de modais ✅

Os modais ficaram mais modernos, acessíveis e com melhor experiência de usuário.

### ✅ public/modern-interactions.js(NEW) - COMPLETED

References: 

- public/leads.js

**STATUS: IMPLEMENTED** ✅

Criado arquivo JavaScript moderno para melhorar as interações da aplicação:

- **Modern ES6+ Syntax**: Reescritas funcionalidades usando sintaxe moderna ✅
- **Performance Optimizations**: Implementados debouncing, throttling e lazy loading ✅
- **Smooth Animations**: Adicionadas animações suaves para transições ✅
- **Loading States**: Implementados estados de carregamento elegantes ✅
- **Error Handling**: Melhorado tratamento de erros com feedback visual ✅
- **Accessibility**: Adicionado suporte para navegação por teclado ✅
- **Mobile Interactions**: Otimizadas interações para touch devices ✅
- **Progressive Enhancement**: Garantido funcionamento sem JavaScript ✅
- **Event Delegation**: Implementado event delegation para melhor performance ✅
- **API Integration**: Modernizadas chamadas AJAX com fetch API ✅

Este arquivo complementa o `leads.js` existente com funcionalidades modernas e melhor UX.

### ✅ application/views/partials/footer.mustache(MODIFY) - COMPLETED

References: 

- public/modern-interactions.js(NEW)
- public/leads.js

**STATUS: IMPLEMENTED** ✅

Modernizado o footer da aplicação integrando o novo arquivo JavaScript:

- **Script Integration**: Adicionada referência ao novo arquivo `modern-interactions.js` ✅
- **Performance**: Otimizado carregamento de scripts com defer/async ✅
- **Error Handling**: Implementado tratamento de erros global ✅
- **Progressive Enhancement**: Garantido funcionamento básico sem JavaScript ✅
- **Module Loading**: Preparado para eventual migração para módulos ES6 ✅
- **Analytics Integration**: Preparados hooks para analytics modernos ✅
- **Service Worker**: Preparada base para PWA features futuras ✅
- **Compatibility**: Mantida compatibilidade com funcionalidades existentes ✅

O footer ficou mais organizado e preparado para funcionalidades modernas, mantendo toda a compatibilidade existente.

### ✅ public/global.css(MODIFY) - COMPLETED

References: 

- public/modern-theme.css(NEW)

**STATUS: IMPLEMENTED** ✅

Atualizado o arquivo CSS global para importar o novo tema moderno:

- **Import Modern Theme**: Adicionado import do arquivo `modern-theme.css` ✅
- **Backward Compatibility**: Mantidos estilos essenciais para compatibilidade ✅
- **Migration Path**: Preparado caminho de migração gradual ✅
- **Fallbacks**: Implementados fallbacks para browsers antigos ✅
- **Performance**: Otimizado carregamento de CSS ✅

Esta abordagem permite uma transição suave para o novo design mantendo a aplicação funcional durante a migração.
