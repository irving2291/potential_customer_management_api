# API Endpoints Documentation

Este documento describe todos los endpoints implementados para completar la integración entre el frontend (boost-from) y el backend (synfony_p1).

## Módulos Implementados

### 1. **Assignees (Responsables)**

#### Endpoints disponibles:
- `GET /assignees` - Listar responsables
- `GET /assignees/stats` - Estadísticas de responsables
- `PATCH /requests/{id}/reassign` - Reasignar petición

#### Ejemplos de uso:

**Listar responsables activos:**
```http
GET /assignees?active=true
Headers: X-Org-Id: {organizationId}
```

**Obtener estadísticas:**
```http
GET /assignees/stats?dateFrom=2024-01-01&dateTo=2024-12-31
Headers: X-Org-Id: {organizationId}
```

**Reasignar petición:**
```http
PATCH /requests/{requestId}/reassign
Headers: X-Org-Id: {organizationId}
Content-Type: application/json

{
  "toAssigneeId": "assignee-uuid",
  "reason": "Motivo de reasignación"
}
```

---

### 2. **Activations (Activaciones)**

#### Endpoints disponibles:
- `GET /activations` - Listar activaciones (con filtros y paginación)
- `POST /activations` - Crear activación
- `GET /activations/{id}` - Obtener activación por ID
- `PUT /activations/{id}` - Actualizar activación
- `PATCH /activations/{id}/status` - Cambiar estado de activación
- `DELETE /activations/{id}` - Eliminar activación

#### Ejemplos de uso:

**Listar activaciones con filtros:**
```http
GET /activations?page=1&perPage=20&status=active&type=promotion&search=black friday
Headers: X-Org-Id: {organizationId}
```

**Crear nueva activación:**
```http
POST /activations
Headers: X-Org-Id: {organizationId}
Content-Type: application/json

{
  "title": "Promoción Black Friday",
  "description": "Descuentos especiales del 50%",
  "type": "promotion",
  "priority": "high",
  "channels": ["email", "sms", "whatsapp"],
  "targetAudience": "Clientes activos",
  "scheduledFor": "2024-11-29T09:00:00Z"
}
```

**Cambiar estado:**
```http
PATCH /activations/{id}/status
Headers: X-Org-Id: {organizationId}
Content-Type: application/json

{
  "status": "active",
  "scheduledFor": "2024-11-29T09:00:00Z"
}
```

---

### 3. **Accounts (Cuentas) - usando PotentialCustomer**

#### Endpoints disponibles:
- `GET /accounts` - Listar cuentas (con filtros y paginación)
- `POST /accounts` - Crear cuenta
- `GET /accounts/{id}` - Obtener cuenta por ID
- `PUT /accounts/{id}` - Actualizar cuenta
- `DELETE /accounts/{id}` - Eliminar cuenta
- `GET /accounts/stats` - Estadísticas de cuentas

**Nota:** Los endpoints de `/accounts` están implementados en el controlador [`PotentialCustomerController`](synfony_p1/app/src/PotentialCustomer/Infrastructure/Controller/PotentialCustomerController.php) para mantener consistencia con la estructura existente del proyecto.

#### Ejemplos de uso:

**Listar cuentas con filtros:**
```http
GET /accounts?page=1&perPage=20&type=person&status=client&priority=high&search=juan
Headers: X-Org-Id: {organizationId}
```

**Crear nueva cuenta:**
```http
POST /accounts
Headers: X-Org-Id: {organizationId}
Content-Type: application/json

{
  "type": "person",
  "firstName": "Juan",
  "lastName": "Pérez",
  "email": "juan.perez@email.com",
  "phone": "+593 99 123 4567",
  "city": "Quito",
  "priority": "high",
  "assignedTo": "user1",
  "assignedToName": "María García"
}
```

**Obtener estadísticas:**
```http
GET /accounts/stats
Headers: X-Org-Id: {organizationId}
```

---

### 4. **Landing Pages**

#### Endpoints disponibles:
- `GET /landing-pages` - Listar landing pages
- `POST /landing-pages` - Crear landing page
- `GET /landing-pages/{id}` - Obtener landing page por ID
- `GET /landing-pages/slug/{slug}` - Obtener landing page por slug (acceso público)
- `PUT /landing-pages/{id}` - Actualizar landing page
- `DELETE /landing-pages/{id}` - Eliminar landing page
- `POST /landing-pages/{id}/submit` - Enviar formulario de contacto

#### Ejemplos de uso:

**Listar landing pages:**
```http
GET /landing-pages?published=true
Headers: X-Org-Id: {organizationId}
```

**Crear nueva landing page:**
```http
POST /landing-pages
Headers: X-Org-Id: {organizationId}
Content-Type: application/json

{
  "title": "Página de Contacto",
  "slug": "contacto",
  "htmlContent": "<div><h1>Contáctanos</h1></div>",
  "isPublished": true,
  "hasContactForm": true,
  "contactFormConfig": {
    "title": "Formulario de Contacto",
    "description": "Déjanos tus datos",
    "submitButtonText": "Enviar",
    "successMessage": "¡Gracias por contactarnos!",
    "fields": [
      {
        "id": "firstName",
        "name": "firstName", 
        "label": "Nombre",
        "type": "text",
        "required": true
      }
    ]
  }
}
```

**Enviar formulario (acceso público):**
```http
POST /landing-pages/{id}/submit
Content-Type: application/json

{
  "firstName": "Juan",
  "lastName": "Pérez", 
  "email": "juan@email.com",
  "phone": "+593999999999",
  "message": "Estoy interesado en sus servicios",
  "programInterest": "Consulta desde Landing Page",
  "city": "Quito"
}
```

---

## Headers Requeridos

Todos los endpoints (excepto los públicos de landing pages) requieren:

```http
X-Org-Id: {organizationId}
Content-Type: application/json
```

## Respuestas Estándar

### Éxito:
```json
{
  "data": [...],
  "total": 100,
  "page": 1,
  "perPage": 20
}
```

### Error:
```json
{
  "error": true,
  "message": "Descripción del error"
}
```

## Estados y Tipos

### Activations:
- **Status**: `draft`, `scheduled`, `active`, `completed`, `cancelled`
- **Type**: `promotion`, `announcement`, `reminder`, `survey`
- **Priority**: `low`, `medium`, `high`, `urgent`

### Accounts:
- **Type**: `person`, `company`
- **Status**: `prospect`, `client`, `inactive`
- **Priority**: `low`, `medium`, `high`

## Integración con Frontend

### Stores a actualizar:

1. **`boost-from/src/stores/assignees.ts`**
   - Actualizar URLs de API para usar los nuevos endpoints
   - Los endpoints ya están implementados y devuelven datos compatibles

2. **`boost-from/src/stores/accounts.ts`**
   - Cambiar URLs de `/requests/accounts` a `/accounts`
   - Los endpoints están implementados en [`PotentialCustomerController`](synfony_p1/app/src/PotentialCustomer/Infrastructure/Controller/PotentialCustomerController.php)
   - Actualizar métodos para usar los nuevos endpoints

3. **`boost-from/src/stores/landing-pages.ts`**
   - Implementar llamadas reales a la API en lugar de datos mock
   - Usar endpoints `/landing-pages`

4. **Crear nuevo store para activaciones:**
   - `boost-from/src/stores/activations.ts`
   - Implementar métodos para CRUD de activaciones

### Páginas que ya funcionarán:
- [`ActivationsPage.vue`](boost-from/src/pages/ActivationsPage.vue) ✅
- [`AssigneesPage.vue`](boost-from/src/pages/AssigneesPage.vue) ✅
- [`AccountsPage.vue`](boost-from/src/pages/AccountsPage.vue) ✅ (usando PotentialCustomer)
- [`LandingPagesPage.vue`](boost-from/src/pages/LandingPagesPage.vue) ✅

## Próximos Pasos

1. **Configurar servicios en Symfony** para inyección de dependencias
2. **Crear migraciones de base de datos** para las nuevas entidades
3. **Implementar repositorios reales** (actualmente usan datos mock)
4. **Actualizar frontend** para usar los nuevos endpoints
5. **Agregar autenticación y autorización** si es necesario
6. **Implementar tests** para los nuevos endpoints

## Notas Importantes

- Todos los controladores están implementados con **datos mock** para permitir testing inmediato
- La estructura sigue **DDD y arquitectura hexagonal** como el resto del proyecto
- Los endpoints están documentados con **OpenAPI/Swagger**
- Se mantiene consistencia con los endpoints existentes de [`RequestInformation`](synfony_p1/app/src/RequestInformation/Infrastructure/Controller/RequestInformationController.php) y [`Quotation`](synfony_p1/app/src/Quotation/Infrastructure/Controller/QuotationController.php)