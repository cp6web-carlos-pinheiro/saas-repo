# Arquitetura Laravel 12+ Modular Monolith (Enterprise)

## 1) Estrutura de Pastas (DDD + Modular)

```text
app/
  Modules/
    MRP/
      Domain/
        Entities/
        Repositories/
      Application/
        DTO/
        Actions/
      Infrastructure/
        Persistence/
          Repositories/
      Presentation/
        Http/
          Controllers/

  Shared/
    Application/
      Actions/
        BaseAction.php
      Cache/
        CacheManager.php
      DTO/
        BaseDTO.php
      Events/
        BaseEvent.php
      Jobs/
        BaseJob.php
      Repositories/
        BaseRepository.php
      Services/
        BaseService.php
      Transactions/
        TransactionManager.php

    Infrastructure/
      Cache/
        RedisCacheManager.php
      Logging/
        AppLogger.php
        JsonLogFormatter.php
        LogContext.php
      Transactions/
        DbTransactionManager.php

    Presentation/
      Exceptions/
        ApiExceptionHandler.php
        DomainException.php
      Http/
        Responses/
          ApiResponse.php

  Providers/
    ArchitectureServiceProvider.php
    ObservabilityServiceProvider.php

config/
  architecture.php
  cache.php
  database.php
  logging.php
  queue.php

routes/
  api.php
```

## 2) Padrão de Resposta de API

Formato único:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "meta": null,
  "errors": null,
  "timestamp": "2026-01-01T10:00:00Z"
}
```

Paginação utiliza `meta` com `current_page`, `last_page`, `per_page`, `total`.

## 3) Exceções (API-first)

Mapeamento principal no `ApiExceptionHandler`:

- ValidationException -> 422
- AuthenticationException -> 401
- ModelNotFoundException -> 404
- DomainException -> status customizado
- Fallback -> 500

## 4) Logging Strategy

- Logs estruturados em JSON
- Canal principal: `stack` (`daily` + `stderr`)
- Canal dedicado para SQL: `sql`
- Enriquecimento com contexto: trace id, user id, ip e route

## 5) Transações

`TransactionManager` define contrato e `DbTransactionManager` encapsula `DB::transaction`.

Uso recomendado:

```php
return $this->inTransaction(function () use ($payload) {
    // regras de negocio + persistencia atomica
});
```

## 6) Cache Layer

- Contrato único `CacheManager`
- Implementação `RedisCacheManager`
- Uso por `BaseService` com `cacheRemember`

## 7) Queue Workers (Redis)

Config padrão em `config/queue.php` com `after_commit = true`.

Comando recomendado:

```bash
php artisan queue:work redis --queue=default --tries=3 --timeout=120
```

## 8) Banco de Dados

`config/database.php` está configurado para MySQL como default e Redis para cache/queue.

## 9) Integração no bootstrap/app.php (Laravel 12)

Adicionar registro de providers e renderização de exceções com o handler da camada Shared.

Exemplo de intenção:

```php
->withProviders([
    App\Providers\ArchitectureServiceProvider::class,
    App\Providers\ObservabilityServiceProvider::class,
])
->withExceptions(function ($exceptions) {
    $exceptions->render(function (\Throwable $e, $request) {
        return app(App\Shared\Presentation\Exceptions\ApiExceptionHandler::class)
            ->render($request, $e);
    });
})
```

## 10) Próximos módulos

Replicar o padrão de `app/Modules/MRP` para:

- Inventory
- Production
- Scheduling
- Purchasing
- Genealogy
- EngineeringChange
- MES

Mantendo separação por `Domain`, `Application`, `Infrastructure` e `Presentation`.
