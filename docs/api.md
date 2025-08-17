# API Documentation

## Clientes

### GET /api/clientes
Lista todos os clientes

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "João Silva",
      "email": "joao@email.com",
      "telefone": "11987654321",
      "tipo": "residencial"
    }
  ]
}
```

### POST /api/clientes
Cria novo cliente

**Request:**
```json
{
  "nome": "João Silva",
  "email": "joao@email.com",
  "telefone": "11987654321",
  "endereco": "Rua das Flores, 123",
  "cidade": "São Paulo",
  "estado": "SP",
  "cep": "01234-567",
  "tipo": "residencial"
}
```

### PUT /api/clientes/{id}
Atualiza cliente existente

### DELETE /api/clientes/{id}
Remove cliente

### GET /api/clientes/search?nome=João
Busca clientes por filtros