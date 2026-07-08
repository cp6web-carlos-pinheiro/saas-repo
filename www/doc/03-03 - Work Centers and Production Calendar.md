# Work Centers and Production Calendar Module

## Scope

Module for shop-floor capacity base data and operational calendar.

Includes:
- machines / lines (`resource_type`)
- capacity per day
- shifts
- efficiency factor
- working calendar by date

## Data model

- `work_centers`
- `work_center_shifts`
- `production_calendar_days`

All entities are tenant-scoped by `company_id`.

## API Endpoints

- GET `/api/v1/work-centers`
- POST `/api/v1/work-centers`
- GET `/api/v1/work-centers/{id}`
- PUT `/api/v1/work-centers/{id}`
- DELETE `/api/v1/work-centers/{id}`
- POST `/api/v1/work-centers/{id}/shifts`
- GET `/api/v1/work-centers/{workCenterId}/calendar?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD`
- PUT `/api/v1/work-centers/{workCenterId}/calendar/day`
- POST `/api/v1/work-centers/{workCenterId}/calendar/generate`

## Calendar generation behavior

Bulk generation creates or updates date rows in the interval:
- weekend defaults to non-working day with 0 capacity
- weekdays default to `capacity_per_day * (efficiency_factor / 100)`

## Required permissions

- `work-centers.read`
- `work-centers.create`
- `work-centers.update`
- `work-centers.delete`
- `work-centers.shifts.create`
- `production-calendar.read`
- `production-calendar.update`
- `production-calendar.generate`
