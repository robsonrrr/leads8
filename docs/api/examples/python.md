# Exemplos de Uso - Python

## Configuração

```python
# api.py
import requests
from typing import Optional, Dict, Any
from dataclasses import dataclass

@dataclass
class ApiConfig:
    base_url: str
    token: Optional[str] = None
    device_id: Optional[str] = None

class Leads8API:
    def __init__(self, config: ApiConfig):
        self.config = config
    
    def _get_headers(self) -> Dict[str, str]:
        headers = {
            'Content-Type': 'application/json'
        }
        
        if self.config.token:
            headers['Authorization'] = f'Bearer {self.config.token}'
        
        return headers
    
    def _request(self, method: str, endpoint: str, **kwargs) -> Any:
        url = f'{self.config.base_url}{endpoint}'
        headers = self._get_headers()
        
        if 'headers' in kwargs:
            headers.update(kwargs.pop('headers'))
        
        response = requests.request(
            method=method,
            url=url,
            headers=headers,
            **kwargs
        )
        
        response.raise_for_status()
        data = response.json()
        
        if data.get('error'):
            raise Exception(data.get('message', 'API Error'))
        
        return data.get('data')
    
    # Autenticação
    def login(self, username: str, password: str) -> Dict[str, Any]:
        data = {
            'username': username,
            'password': password
        }
        
        if self.config.device_id:
            data['device_id'] = self.config.device_id
        
        response = self._request('POST', '/auth', json=data)
        self.config.token = response['token']
        return response
    
    def refresh_token(self) -> Dict[str, Any]:
        response = self._request('POST', '/refresh-token')
        self.config.token = response['token']
        return response
    
    def logout(self) -> Dict[str, Any]:
        data = {}
        if self.config.device_id:
            data['device_id'] = self.config.device_id
        
        return self._request('POST', '/logout', json=data)
    
    # Leads
    def get_leads(self, **params) -> Dict[str, Any]:
        return self._request('GET', '/leads', params=params)
    
    def get_lead(self, lead_id: int) -> Dict[str, Any]:
        return self._request('GET', f'/leads/{lead_id}')
    
    def create_lead(self, data: Dict[str, Any]) -> Dict[str, Any]:
        return self._request('POST', '/leads', json=data)
    
    def update_lead(self, lead_id: int, data: Dict[str, Any]) -> Dict[str, Any]:
        return self._request('PUT', f'/leads/{lead_id}', json=data)
    
    def delete_lead(self, lead_id: int) -> Dict[str, Any]:
        return self._request('DELETE', f'/leads/{lead_id}')
    
    # Produtos
    def get_products(self, **params) -> Dict[str, Any]:
        return self._request('GET', '/products', params=params)
    
    def get_product(self, product_id: int) -> Dict[str, Any]:
        return self._request('GET', f'/products/{product_id}')
    
    def get_product_by_barcode(self, code: str) -> Dict[str, Any]:
        return self._request('GET', '/product/barcode', params={'code': code})
    
    def get_product_by_code(self, code: str) -> Dict[str, Any]:
        return self._request('GET', '/product/code', params={'code': code})
    
    # Carrinho
    def get_cart(self) -> Dict[str, Any]:
        return self._request('GET', '/cart')
    
    def add_to_cart(self, data: Dict[str, Any]) -> Dict[str, Any]:
        return self._request('POST', '/cart', json=data)
    
    def update_cart_item(self, data: Dict[str, Any]) -> Dict[str, Any]:
        return self._request('PUT', '/cart', json=data)
    
    def remove_from_cart(self, item_id: int) -> Dict[str, Any]:
        return self._request('DELETE', f'/cart/{item_id}')
    
    def clear_cart(self) -> Dict[str, Any]:
        return self._request('DELETE', '/cart')
    
    # Clientes
    def get_customers(self, **params) -> Dict[str, Any]:
        return self._request('GET', '/customers', params=params)
    
    def get_customer(self, customer_id: int) -> Dict[str, Any]:
        return self._request('GET', f'/customers/{customer_id}')
    
    def get_customer_by_document(self, document: str) -> Dict[str, Any]:
        return self._request('GET', '/customer/document', params={'document': document})
    
    def get_customer_by_email(self, email: str) -> Dict[str, Any]:
        return self._request('GET', '/customer/email', params={'email': email})
    
    def get_customer_addresses(self, customer_id: int) -> Dict[str, Any]:
        return self._request('GET', f'/customer/addresses/{customer_id}')
    
    def get_customer_contacts(self, customer_id: int) -> Dict[str, Any]:
        return self._request('GET', f'/customer/contacts/{customer_id}')
```

## Exemplos de Uso

### Inicialização

```python
from api import Leads8API, ApiConfig

config = ApiConfig(
    base_url='https://api.leads8.com.br/v1',
    device_id='device_123'
)

api = Leads8API(config)
```

### Autenticação

```python
try:
    # Login
    auth = api.login('user@example.com', 'password123')
    print(f'Logged in as: {auth["user"]["name"]}')
    
    # Refresh token
    new_token = api.refresh_token()
    print('Token refreshed')
    
    # Logout
    api.logout()
    print('Logged out')
except Exception as e:
    print(f'Auth error: {str(e)}')
```

### Leads

```python
try:
    # Lista leads
    leads = api.get_leads(
        page=1,
        limit=20,
        status='pending'
    )
    print(f'Total leads: {leads["total"]}')
    
    # Cria lead
    lead = api.create_lead({
        'customer_id': 100,
        'items': [
            {
                'product_id': 100,
                'quantity': 2,
                'unit_price': 100.00
            }
        ],
        'notes': 'Lead via API'
    })
    print(f'Lead created: {lead["id"]}')
    
    # Atualiza lead
    updated = api.update_lead(lead['id'], {
        'status': 'completed'
    })
    print('Lead updated')
    
    # Remove lead
    api.delete_lead(lead['id'])
    print('Lead deleted')
except Exception as e:
    print(f'Lead error: {str(e)}')
```

### Produtos

```python
try:
    # Lista produtos
    products = api.get_products(
        page=1,
        limit=20,
        stock='available',
        sort_by='price',
        sort_order='ASC'
    )
    print(f'Total products: {products["total"]}')
    
    # Busca produto por código de barras
    product = api.get_product_by_barcode('7891234567890')
    print(f'Product: {product["name"]}')
    
    # Busca produto por código
    product_by_code = api.get_product_by_code('PROD001')
    print(f'Product: {product_by_code["name"]}')
except Exception as e:
    print(f'Product error: {str(e)}')
```

### Carrinho

```python
try:
    # Obtém carrinho
    cart = api.get_cart()
    print(f'Cart items: {len(cart["items"])}')
    
    # Adiciona item
    updated = api.add_to_cart({
        'product_id': 100,
        'quantity': 2
    })
    print('Item added')
    
    # Atualiza item
    api.update_cart_item({
        'item_id': updated['items'][0]['id'],
        'quantity': 3
    })
    print('Item updated')
    
    # Remove item
    api.remove_from_cart(updated['items'][0]['id'])
    print('Item removed')
    
    # Limpa carrinho
    api.clear_cart()
    print('Cart cleared')
except Exception as e:
    print(f'Cart error: {str(e)}')
```

### Clientes

```python
try:
    # Lista clientes
    customers = api.get_customers(
        page=1,
        limit=20,
        status='active'
    )
    print(f'Total customers: {customers["total"]}')
    
    # Busca cliente por documento
    customer = api.get_customer_by_document('123.456.789-01')
    print(f'Customer: {customer["name"]}')
    
    # Busca endereços
    addresses = api.get_customer_addresses(customer['id'])
    print(f'Addresses: {len(addresses)}')
    
    # Busca contatos
    contacts = api.get_customer_contacts(customer['id'])
    print(f'Contacts: {len(contacts)}')
except Exception as e:
    print(f'Customer error: {str(e)}')
```

## FastAPI Integration

### API Client como Dependência

```python
# dependencies.py
from fastapi import Depends, HTTPException
from api import Leads8API, ApiConfig

async def get_api():
    config = ApiConfig(
        base_url='https://api.leads8.com.br/v1'
    )
    return Leads8API(config)

# routers/leads.py
from fastapi import APIRouter, Depends
from dependencies import get_api

router = APIRouter()

@router.get('/leads')
async def list_leads(
    page: int = 1,
    limit: int = 20,
    status: str = None,
    api: Leads8API = Depends(get_api)
):
    try:
        return api.get_leads(
            page=page,
            limit=limit,
            status=status
        )
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.post('/leads')
async def create_lead(
    data: dict,
    api: Leads8API = Depends(get_api)
):
    try:
        return api.create_lead(data)
    except Exception as e:
        raise HTTPException(status_code=400, detail=str(e))
```

### Background Tasks

```python
# tasks.py
from celery import Celery
from api import Leads8API, ApiConfig

celery = Celery('tasks', broker='redis://localhost:6379/0')

@celery.task
def sync_leads():
    config = ApiConfig(
        base_url='https://api.leads8.com.br/v1',
        token='your_token'
    )
    api = Leads8API(config)
    
    try:
        # Busca leads pendentes
        leads = api.get_leads(status='pending')
        
        for lead in leads['leads']:
            # Processa lead
            process_lead.delay(lead['id'])
            
    except Exception as e:
        print(f'Sync error: {str(e)}')

@celery.task
def process_lead(lead_id: int):
    config = ApiConfig(
        base_url='https://api.leads8.com.br/v1',
        token='your_token'
    )
    api = Leads8API(config)
    
    try:
        # Busca detalhes do lead
        lead = api.get_lead(lead_id)
        
        # Processa itens
        for item in lead['items']:
            # Verifica estoque
            product = api.get_product(item['product_id'])
            if product['stock'] < item['quantity']:
                # Notifica indisponibilidade
                notify_stock_issue.delay(lead_id, item['product_id'])
                continue
            
            # Processa item
            process_item.delay(lead_id, item['id'])
            
    except Exception as e:
        print(f'Process lead error: {str(e)}')

@celery.task
def notify_stock_issue(lead_id: int, product_id: int):
    # Envia notificação
    pass

@celery.task
def process_item(lead_id: int, item_id: int):
    # Processa item
    pass
```

### Cache

```python
# cache.py
from functools import wraps
from redis import Redis
import json

redis = Redis(host='localhost', port=6379, db=1)

def cache_response(ttl=3600):
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            # Gera chave do cache
            key = f'{func.__name__}:{json.dumps(args)}:{json.dumps(kwargs)}'
            
            # Tenta obter do cache
            cached = redis.get(key)
            if cached:
                return json.loads(cached)
            
            # Executa função
            result = func(*args, **kwargs)
            
            # Salva no cache
            redis.setex(
                key,
                ttl,
                json.dumps(result)
            )
            
            return result
        return wrapper
    return decorator

# Uso
@cache_response(ttl=300)  # 5 minutos
def get_product(api: Leads8API, product_id: int):
    return api.get_product(product_id)
```

### Retry com Backoff

```python
# retry.py
from functools import wraps
import time
from requests.exceptions import RequestException

def retry_with_backoff(retries=3, backoff_in_seconds=1):
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            x = 0
            while True:
                try:
                    return func(*args, **kwargs)
                except RequestException as e:
                    if x == retries:
                        raise e
                    sleep = (backoff_in_seconds * 2 ** x +
                            random.uniform(0, 1))
                    time.sleep(sleep)
                    x += 1
        return wrapper
    return decorator

# Uso
@retry_with_backoff(retries=3)
def get_product(api: Leads8API, product_id: int):
    return api.get_product(product_id)
```

### Logging

```python
# logging_config.py
import logging
from logging.handlers import RotatingFileHandler

def setup_logging():
    logger = logging.getLogger('leads8_api')
    logger.setLevel(logging.INFO)
    
    # Handler de arquivo
    file_handler = RotatingFileHandler(
        'leads8_api.log',
        maxBytes=1024 * 1024,  # 1MB
        backupCount=5
    )
    file_handler.setLevel(logging.INFO)
    
    # Handler de console
    console_handler = logging.StreamHandler()
    console_handler.setLevel(logging.INFO)
    
    # Formato
    formatter = logging.Formatter(
        '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
    )
    file_handler.setFormatter(formatter)
    console_handler.setFormatter(formatter)
    
    # Adiciona handlers
    logger.addHandler(file_handler)
    logger.addHandler(console_handler)
    
    return logger

# Uso
logger = setup_logging()

try:
    # Operação
    result = api.get_products()
    logger.info(f'Products fetched: {len(result["products"])}')
except Exception as e:
    logger.error(f'Error fetching products: {str(e)}')
```


