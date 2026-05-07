# Tasks API — Client Integration Guide

## Overview

The Tasks API provides a multi-tenant task management system where external applications (clients) can:

- Create tasks
- Retrieve tasks (filtered or assigned)
- Update tasks
- Delete tasks
- Filter by project, status, and assignment email
- Operate within an organisation-scoped context

Each client application must authenticate using a Client ID + Client Secret pair.

---

## Authentication

Each API client is issued:

- client_id (public identifier)
- client_secret (private key, shown once at creation)

Example:

client_id: cli_5SWsDR4s6pzE3JlFnFFiYFoh  
client_secret: CCCs24ESLMPvtZ9Ojos7jOuSugSbsUoDB9ybqrF9T9BVL9nzTjGmwiczhkgh6AEq

---

### Token Request

POST /api/auth/token

Request:
{
  "client_id": "cli_xxx",
  "client_secret": "xxx"
}

Response:
{
  "access_token": "...",
  "token_type": "Bearer",
  "expires_in": 3600
}

---

## Base URL

https://your-api-domain.com/api

---

## Tasks API

GET /tasks

Filters:
- status
- project_id
- assigned_email
- per_page

POST /tasks

GET /tasks/{id}

PUT /tasks/{id}

DELETE /tasks/{id}

---

## Projects API

GET /projects

POST /projects

---

## Multi-Tenancy Rules

- Scoped to organisation
- Tasks auto-linked to organisation_id
- created_by_client_id tracks origin client
- No cross-org access

---

## Assignment Model

- assigned_email (external users)
- assigned_client_id (future)

---

## Status Metadata

GET /meta/task-statuses

---

## Error Handling

401 Unauthorized  
403 Forbidden  
422 Validation Error

---

## Rate Limits

60 requests per minute per client

---

## SDK Example

$client = new TasksApiClient([...]);

$client->tasks()
    ->whereStatus('pending')
    ->get();

---

## Design Rules

MUST:
- authenticate
- use bearer token
- respect organisation scope

MUST NOT:
- cross-org queries
- store secrets long-term
