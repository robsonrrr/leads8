# Exemplos de Uso - JavaScript

## Configuração

```javascript
// api.js
class Leads8API {
    constructor(baseUrl, options = {}) {
        this.baseUrl = baseUrl;
        this.token = options.token;
        this.deviceId = options.deviceId;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        const response = await fetch(url, {
            ...options,
            headers
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'API Error');
        }
        
        return data.data;
    }
    
    // Autenticação
    async login(username, password) {
        const data = await this.request('/auth', {
            method: 'POST',
            body: JSON.stringify({
                username,
                password,
                device_id: this.deviceId
            })
        });
        
        this.token = data.token;
        return data;
    }
    
    async refreshToken() {
        const data = await this.request('/refresh-token', {
            method: 'POST'
        });
        
        this.token = data.token;
        return data;
    }
    
    async logout() {
        return this.request('/logout', {
            method: 'POST',
            body: JSON.stringify({
                device_id: this.deviceId
            })
        });
    }
    
    // Leads
    async getLeads(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/leads?${query}`);
    }
    
    async getLead(id) {
        return this.request(`/leads/${id}`);
    }
    
    async createLead(data) {
        return this.request('/leads', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async updateLead(id, data) {
        return this.request(`/leads/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async deleteLead(id) {
        return this.request(`/leads/${id}`, {
            method: 'DELETE'
        });
    }
    
    // Produtos
    async getProducts(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/products?${query}`);
    }
    
    async getProduct(id) {
        return this.request(`/products/${id}`);
    }
    
    async getProductByBarcode(code) {
        return this.request(`/product/barcode?code=${code}`);
    }
    
    async getProductByCode(code) {
        return this.request(`/product/code?code=${code}`);
    }
    
    // Carrinho
    async getCart() {
        return this.request('/cart');
    }
    
    async addToCart(data) {
        return this.request('/cart', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async updateCartItem(data) {
        return this.request('/cart', {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async removeFromCart(itemId) {
        return this.request(`/cart/${itemId}`, {
            method: 'DELETE'
        });
    }
    
    async clearCart() {
        return this.request('/cart', {
            method: 'DELETE'
        });
    }
    
    // Clientes
    async getCustomers(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/customers?${query}`);
    }
    
    async getCustomer(id) {
        return this.request(`/customers/${id}`);
    }
    
    async getCustomerByDocument(document) {
        return this.request(`/customer/document?document=${document}`);
    }
    
    async getCustomerByEmail(email) {
        return this.request(`/customer/email?email=${email}`);
    }
    
    async getCustomerAddresses(customerId) {
        return this.request(`/customer/addresses/${customerId}`);
    }
    
    async getCustomerContacts(customerId) {
        return this.request(`/customer/contacts/${customerId}`);
    }
}
```

## Exemplos de Uso

### Inicialização

```javascript
const api = new Leads8API('https://api.leads8.com.br/v1', {
    deviceId: 'device_123'
});
```

### Autenticação

```javascript
try {
    // Login
    const auth = await api.login('user@example.com', 'password123');
    console.log('Logged in as:', auth.user.name);
    
    // Refresh token
    const newToken = await api.refreshToken();
    console.log('Token refreshed');
    
    // Logout
    await api.logout();
    console.log('Logged out');
} catch (error) {
    console.error('Auth error:', error.message);
}
```

### Leads

```javascript
try {
    // Lista leads
    const leads = await api.getLeads({
        page: 1,
        limit: 20,
        status: 'pending'
    });
    console.log('Total leads:', leads.total);
    
    // Cria lead
    const lead = await api.createLead({
        customer_id: 100,
        items: [
            {
                product_id: 100,
                quantity: 2,
                unit_price: 100.00
            }
        ],
        notes: 'Lead via API'
    });
    console.log('Lead created:', lead.id);
    
    // Atualiza lead
    const updated = await api.updateLead(lead.id, {
        status: 'completed'
    });
    console.log('Lead updated');
    
    // Remove lead
    await api.deleteLead(lead.id);
    console.log('Lead deleted');
} catch (error) {
    console.error('Lead error:', error.message);
}
```

### Produtos

```javascript
try {
    // Lista produtos
    const products = await api.getProducts({
        page: 1,
        limit: 20,
        stock: 'available',
        sort_by: 'price',
        sort_order: 'ASC'
    });
    console.log('Total products:', products.total);
    
    // Busca produto por código de barras
    const product = await api.getProductByBarcode('7891234567890');
    console.log('Product:', product.name);
    
    // Busca produto por código
    const productByCode = await api.getProductByCode('PROD001');
    console.log('Product:', productByCode.name);
} catch (error) {
    console.error('Product error:', error.message);
}
```

### Carrinho

```javascript
try {
    // Obtém carrinho
    const cart = await api.getCart();
    console.log('Cart items:', cart.items.length);
    
    // Adiciona item
    const updated = await api.addToCart({
        product_id: 100,
        quantity: 2
    });
    console.log('Item added');
    
    // Atualiza item
    await api.updateCartItem({
        item_id: updated.items[0].id,
        quantity: 3
    });
    console.log('Item updated');
    
    // Remove item
    await api.removeFromCart(updated.items[0].id);
    console.log('Item removed');
    
    // Limpa carrinho
    await api.clearCart();
    console.log('Cart cleared');
} catch (error) {
    console.error('Cart error:', error.message);
}
```

### Clientes

```javascript
try {
    // Lista clientes
    const customers = await api.getCustomers({
        page: 1,
        limit: 20,
        status: 'active'
    });
    console.log('Total customers:', customers.total);
    
    // Busca cliente por documento
    const customer = await api.getCustomerByDocument('123.456.789-01');
    console.log('Customer:', customer.name);
    
    // Busca endereços
    const addresses = await api.getCustomerAddresses(customer.id);
    console.log('Addresses:', addresses.length);
    
    // Busca contatos
    const contacts = await api.getCustomerContacts(customer.id);
    console.log('Contacts:', contacts.length);
} catch (error) {
    console.error('Customer error:', error.message);
}
```

## React Native

### Configuração com Context

```javascript
// contexts/ApiContext.js
import React, { createContext, useContext, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Leads8API } from './api';

const ApiContext = createContext();

export function ApiProvider({ children }) {
    const [api] = useState(() => new Leads8API('https://api.leads8.com.br/v1', {
        deviceId: 'device_123'
    }));
    
    return (
        <ApiContext.Provider value={api}>
            {children}
        </ApiContext.Provider>
    );
}

export function useApi() {
    return useContext(ApiContext);
}
```

### Hook de Autenticação

```javascript
// hooks/useAuth.js
import { useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useApi } from '../contexts/ApiContext';

export function useAuth() {
    const api = useApi();
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    
    useEffect(() => {
        loadToken();
    }, []);
    
    async function loadToken() {
        try {
            const token = await AsyncStorage.getItem('token');
            if (token) {
                api.token = token;
                // Verifica token
                const data = await api.refreshToken();
                setUser(data.user);
            }
        } catch (error) {
            console.error('Load token error:', error);
        } finally {
            setLoading(false);
        }
    }
    
    async function login(username, password) {
        try {
            const data = await api.login(username, password);
            await AsyncStorage.setItem('token', data.token);
            setUser(data.user);
            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }
    
    async function logout() {
        try {
            await api.logout();
            await AsyncStorage.removeItem('token');
            setUser(null);
        } catch (error) {
            console.error('Logout error:', error);
            throw error;
        }
    }
    
    return {
        user,
        loading,
        login,
        logout
    };
}
```

### Hook de Carrinho

```javascript
// hooks/useCart.js
import { useState, useCallback } from 'react';
import { useApi } from '../contexts/ApiContext';

export function useCart() {
    const api = useApi();
    const [cart, setCart] = useState(null);
    const [loading, setLoading] = useState(false);
    
    const loadCart = useCallback(async () => {
        try {
            setLoading(true);
            const data = await api.getCart();
            setCart(data);
        } catch (error) {
            console.error('Load cart error:', error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);
    
    const addItem = useCallback(async (product, quantity) => {
        try {
            setLoading(true);
            const data = await api.addToCart({
                product_id: product.id,
                quantity
            });
            setCart(data);
        } catch (error) {
            console.error('Add item error:', error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);
    
    const updateItem = useCallback(async (itemId, quantity) => {
        try {
            setLoading(true);
            const data = await api.updateCartItem({
                item_id: itemId,
                quantity
            });
            setCart(data);
        } catch (error) {
            console.error('Update item error:', error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);
    
    const removeItem = useCallback(async (itemId) => {
        try {
            setLoading(true);
            const data = await api.removeFromCart(itemId);
            setCart(data);
        } catch (error) {
            console.error('Remove item error:', error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);
    
    const clearCart = useCallback(async () => {
        try {
            setLoading(true);
            const data = await api.clearCart();
            setCart(data);
        } catch (error) {
            console.error('Clear cart error:', error);
            throw error;
        } finally {
            setLoading(false);
        }
    }, []);
    
    return {
        cart,
        loading,
        loadCart,
        addItem,
        updateItem,
        removeItem,
        clearCart
    };
}
```

### Exemplo de Tela

```javascript
// screens/ProductsScreen.js
import React, { useState, useEffect } from 'react';
import { View, FlatList, ActivityIndicator } from 'react-native';
import { useApi } from '../contexts/ApiContext';
import { useCart } from '../hooks/useCart';
import { ProductCard } from '../components/ProductCard';

export function ProductsScreen() {
    const api = useApi();
    const { addItem } = useCart();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(false);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    
    useEffect(() => {
        loadProducts();
    }, []);
    
    async function loadProducts(refresh = false) {
        if (loading || (!hasMore && !refresh)) return;
        
        try {
            setLoading(true);
            
            const newPage = refresh ? 1 : page;
            const data = await api.getProducts({
                page: newPage,
                limit: 20,
                stock: 'available'
            });
            
            setProducts(prev => refresh ? data.products : [...prev, ...data.products]);
            setHasMore(data.page < data.total_pages);
            setPage(newPage + 1);
        } catch (error) {
            console.error('Load products error:', error);
        } finally {
            setLoading(false);
        }
    }
    
    async function handleAddToCart(product) {
        try {
            await addItem(product, 1);
            // Mostra mensagem de sucesso
        } catch (error) {
            // Mostra erro
            console.error('Add to cart error:', error);
        }
    }
    
    return (
        <View style={{ flex: 1 }}>
            <FlatList
                data={products}
                renderItem={({ item }) => (
                    <ProductCard
                        product={item}
                        onAddToCart={() => handleAddToCart(item)}
                    />
                )}
                keyExtractor={item => item.id.toString()}
                onRefresh={() => loadProducts(true)}
                refreshing={loading && page === 1}
                onEndReached={() => loadProducts()}
                onEndReachedThreshold={0.2}
                ListFooterComponent={() => (
                    loading && page > 1 ? (
                        <ActivityIndicator style={{ padding: 20 }} />
                    ) : null
                )}
            />
        </View>
    );
}
```


