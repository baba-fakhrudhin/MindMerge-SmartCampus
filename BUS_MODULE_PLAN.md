# Bus Management Module Plan

## Goal

Build a complete transport ERP with future GPS readiness but no live GPS implementation yet.

## Database Tables

### `buses`

- `bus_id`
- `bus_number`
- `registration_number`
- `capacity`
- `status`
- `latitude`
- `longitude`
- `last_updated`

### `drivers`

- `driver_id`
- `full_name`
- `phone`
- `license_number`
- `address`
- `status`

### `routes`

- `route_id`
- `route_code`
- `route_name`
- `start_point`
- `end_point`
- `status`

### `stops`

- `stop_id`
- `stop_name`
- `latitude`
- `longitude`
- `status`

### `route_stops`

- `route_stop_id`
- `route_id`
- `stop_id`
- `stop_order`
- `arrival_time`
- `departure_time`

### `bus_assignments`

- `assignment_id`
- `bus_id`
- `route_id`
- `driver_id`
- `student_id`
- `pickup_stop_id`
- `drop_stop_id`
- `status`

## Admin Features

- Bus Management.
- Driver Management.
- Route Management.
- Stop Management.
- Assign Students.
- Manage Timings.

## Student Features

- View assigned bus.
- View route.
- View stops.
- View pickup/drop timing.

## Parent Features

- View child transport details.

## Permission Module

Add `transport.view`, `transport.create`, `transport.edit`, `transport.delete`.

## Future GPS Readiness

Keep `latitude`, `longitude`, and `last_updated` on `buses`.

Do not implement:

- Live map tracking.
- GPS device ingestion.
- Parent live tracking.

Those should be a later phase after transport CRUD is stable.
