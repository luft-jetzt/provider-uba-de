# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Symfony console application that fetches air quality data from the German Federal Environmental Agency (Umweltbundesamt, UBA) API. Retrieves measurements for PM10, NO2, O3, CO, and SO2 from Germany's official air quality monitoring network.

- **PHP**: ^8.5
- **Framework**: Symfony 8.0
- **Data Source**: UBA API v2 (`umweltbundesamt.de/api/air_data/v2/`)
- **Pollutants**: PM10, NO2, O3, CO, SO2

## Common Commands

```bash
composer install                                          # Install dependencies
php bin/console luft:fetch pm10 no2 o3                    # Fetch specific pollutants
php bin/console luft:fetch pm10 --from-date-time="2024-01-01 00:00:00"  # With date filter
php bin/console luft:fetch pm10 --tag=mytag               # Add tag to values
php bin/console luft:station:load                         # Load stations from UBA meta API
php bin/console luft:station:cache                        # Manage station cache

vendor/bin/phpunit                                        # Run tests
vendor/bin/phpstan analyse --no-progress                  # Static analysis
```

## Architecture

### Pollutant Enum — UBA Component Mappings

| Pollutant | Component ID | Scope |
|-----------|-------------|-------|
| PM10      | 1           | 2     |
| CO        | 2           | 4     |
| O3        | 3           | 2     |
| SO2       | 4           | 2     |
| NO2       | 5           | 2     |

### Data Fetching Pipeline

1. **`SourceFetcher/SourceFetcher`** — Queries UBA API with date range (default: last 2 hours)
2. **`SourceFetcher/QueryBuilder/QueryBuilder`** — Constructs API query parameters, maps pollutants to component IDs/scopes
3. **`SourceFetcher/Parser/Parser`** — Parses API response, maps UBA station IDs to Luft.jetzt station codes, creates `Value` objects

### Station Management

- **`StationLoader/StationLoader`** — Downloads station metadata from UBA meta API, maps 16+ fields
- **`StationManager/StationManager`** — In-memory station index for ID lookups
- **`StationCache/`** — Filesystem caching of station data between runs

### Key Endpoints

- Measures: `https://www.umweltbundesamt.de/api/air_data/v2/measures/json`
- Meta: `https://www.umweltbundesamt.de/api/air_data/v2/meta/json?use=measure&lang=de`

## Dependencies

- `symfony/http-client` ^8.0, `symfony/console` ^8.0
- `luft-jetzt/luft-api-bundle` ^0.11 — Pushes data to Luft.jetzt API
- `luft-jetzt/luft-model` ^0.5 — Shared data models
