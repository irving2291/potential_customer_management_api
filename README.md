
# Potential Customer Management API

**by PithayaSoft**

---

## Español

### Descripción

API RESTful profesional para la gestión de solicitudes de información y potenciales clientes (leads), desarrollada por **PithayaSoft** utilizando **Symfony 7** bajo principios de **DDD** y **Arquitectura Hexagonal**.  
Incluye CQRS, mensajería asíncrona con RabbitMQ, documentación Swagger (OpenAPI), validación robusta y manejo profesional de errores.

---

### Características

- Arquitectura limpia y desacoplada (DDD + Hexagonal)
- API 100% REST, sin Twig ni frontend
- Comandos y consultas separados (CQRS)
- Symfony Messenger y RabbitMQ para ejecución asíncrona de procesos
- Documentación automática con Swagger UI (OpenAPI)
- Soporte para paginación, filtros, estados personalizables
- Docker/Docker Compose para fácil despliegue y desarrollo

---

### Estructura principal

```
src/
├── RequestInformation/
│   ├── Application/
│   ├── Domain/
│   ├── Infrastructure/
├── PotentialCustomer/
├── Shared/
└── ...
```

---

### Requisitos

- Docker y Docker Compose
- (Opcional) PHP 8.2+, Composer

---

### Instalación y ejecución

```bash
git clone https://github.com/pithayasoft/potential-customer-management-api.git
cd potential-customer-management-api

# Ajusta el .env si es necesario

docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec app php bin/console cache:clear
```

Para correr el worker Messenger (async):

```bash
docker-compose exec app php bin/console messenger:consume async -vv
```

---

### Acceso a Swagger UI

Accede a la documentación interactiva en:

```
http://localhost:8080/api/doc
```

---

### Endpoints principales

- **POST /api/v1/requests-information** — Crear una solicitud de información (lead)
- **GET /api/v1/requests-information** — Listar solicitudes por estado, con paginación
- **GET /api/v1/requests-information/total** — Obtener total de solicitudes

---

### Licencia

MIT  
© PithayaSoft

---

---

## English

### Description

A professional RESTful API for managing information requests and potential customers (leads),  
developed by **PithayaSoft** using **Symfony 7** and applying **DDD** and **Hexagonal Architecture**.  
Features CQRS, async messaging with RabbitMQ, Swagger (OpenAPI) documentation, robust validation, and professional error handling.

---

### Features

- Clean, decoupled architecture (DDD + Hexagonal)
- Pure REST API, no Twig nor frontend
- Commands and queries separated (CQRS)
- Symfony Messenger & RabbitMQ for asynchronous processing
- Automatic Swagger UI (OpenAPI) documentation
- Pagination, filtering, customizable statuses
- Docker/Docker Compose for fast setup and deployment

---

### Main Structure

```
src/
├── RequestInformation/
│   ├── Application/
│   ├── Domain/
│   ├── Infrastructure/
├── PotentialCustomer/
├── Shared/
└── ...
```

---

### Requirements

- Docker and Docker Compose
- (Optional) PHP 8.2+, Composer

---

### Installation & Running

```bash
git clone https://github.com/pithayasoft/potential-customer-management-api.git
cd potential-customer-management-api

# Adjust .env if needed

docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec app php bin/console cache:clear
```

To run the Messenger worker (for async commands):

```bash
docker-compose exec app php bin/console messenger:consume async -vv
```

---

### Swagger UI Access

Browse the live API documentation at:

```
http://localhost:8080/api/doc
```

---

### Main Endpoints

- **POST /api/v1/requests-information** — Create an information request (lead)
- **GET /api/v1/requests-information** — List requests by status, paginated
- **GET /api/v1/requests-information/total** — Get total number of requests

---

### License

MIT  
© PithayaSoft

---
