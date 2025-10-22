# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## Project Overview

This is an **AI-Driven Log Analysis System** built with Laravel 12 that analyzes Laravel application logs using AI. The system:
- Monitors `storage/logs/laravel.log` in real-time
- Vectorizes log entries for semantic search using **Overpass** (Python AI bridge)
- Analyzes patterns and generates insights using **Prism** (LLM integration)
- Displays results in a Livewire-based dashboard

## Technology Stack

- **Framework**: Laravel 12 with Livewire 3 + Volt (class-based single-file components)
- **Database**: SQLite with vector extension (sqlite-vec)
- **AI/ML**:
  - Prism (v0.96) - Multi-provider LLM integration (OpenAI, Anthropic, Gemini, etc.)
  - Overpass (v0.7) - Python AI bridge for embeddings and ML operations
- **Real-time**: Laravel Reverb (v1.6) for WebSocket connections
- **Testing**: Pest 4 with browser testing capabilities
- **Frontend**: Tailwind CSS v4 + Alpine.js (bundled with Livewire)
- **Dev Tools**: Laravel Boost, Pint, Pail

## Architecture Pattern: Active Classes

This application uses the **Active Pattern** for domain logic. Business logic is organized into focused, single-responsibility classes in the `app/Active/` directory rather than being scattered across controllers or models.

### Active Classes Structure

```
app/Active/
├── LogMonitor.php        - Handles new log line detection
├── LogVectorizer.php     - Creates embeddings via Overpass
├── LogAnalyzer.php       - AI analysis via Prism
└── IncidentManager.php   - Creates and manages incidents
```

Each Active class:
- Has a single, clear responsibility
- Contains domain logic (not HTTP concerns)
- Is instantiated in Jobs or Livewire components
- Uses type hints and return types (PHP 8.2+)

## Key Development Commands

### Running the Application

```bash
# Start all services (server, queue, logs, vite)
composer run dev

# Individual services
php artisan serve                    # Web server (port 8000)
php artisan queue:work              # Process queued jobs
php artisan reverb:start            # WebSocket server
npm run dev                         # Vite dev server
php artisan pail                    # Real-time log viewer
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Filter by test name
php artisan test --filter=testName

# Browser tests (Pest v4)
php artisan test tests/Browser/
```

### Code Quality

```bash
# Format code (always run before committing)
vendor/bin/pint --dirty

# Run static analysis (if configured)
vendor/bin/phpstan analyse
```

## Database Architecture

### Core Tables

- **log_entries** - Raw log lines with timestamp
- **log_vectors** - Vector embeddings (JSON) linked to log entries
- **incidents** - AI-identified issues with severity and summary

### Vector Operations

The application uses SQLite with the `sqlite-vec` extension for vector similarity searches. Embeddings are stored as JSON arrays and queried for semantic similarity when analyzing new log entries.

## AI Integration Details

### Prism Configuration

Prism is configured in `config/prism.php` with support for multiple LLM providers. The application can define custom agents for specific analysis tasks:

```php
// Example agent definition
'log_analyst' => [
    'model' => 'gpt-4-turbo',
    'prompt' => 'You are a log analysis assistant...',
],
```

Access via:
- `Prism::text()->using('openai', 'gpt-4')` - Text generation
- `Prism::structured()->using(...)` - Structured JSON output
- `Prism::embeddings()->using(...)` - Generate embeddings
- `prism()` helper function - Fluent alternative

**Before implementing Prism features**, use web search to find current documentation at prismphp.com.

### Overpass Python Bridge

Overpass executes Python scripts for ML operations. The main script is at `overpass-ai/main.py` (configurable via `OVERPASS_SCRIPT_PATH`).

Configuration in `config/overpass.php`:
- `timeout`: Max execution time (default: 90s)
- `max_output_length`: Output size limit
- Error handling with retry logic

Example usage:
```php
use Overpass\Facades\Overpass;

$embedding = Overpass::call('vectorize', ['text' => $logLine]);
```

Python dependencies (sentence-transformers, etc.) must be installed separately.

## Livewire & Volt Components

This project uses **Livewire Volt** (class-based) for all interactive components.

### Creating Volt Components

```bash
php artisan make:volt [name] [--test] [--pest]
```

Components combine PHP logic and Blade templates in single files under `resources/views/livewire/`.

### Volt Best Practices

- Use `wire:model.live` for real-time updates (deferred by default in v3)
- Add `wire:key` in loops: `wire:key="item-{{ $item->id }}"`
- Use `wire:loading` and `wire:dirty` for loading states
- Components must have a single root element
- Server-side validation is required (all requests hit Laravel backend)

### Testing Volt Components

```php
use Livewire\Volt\Volt;

test('component works', function () {
    Volt::test('log-dashboard')
        ->assertSee('Dashboard')
        ->call('refreshData')
        ->assertSet('logs', fn($logs) => count($logs) > 0);
});
```

## Job Queue Architecture

Log analysis is performed asynchronously via queued jobs:

```php
// app/Jobs/AnalyzeLogJob.php
// 1. Vectorize the log entry
// 2. Analyze with Prism + context
// 3. Create incident if needed
```

Queue configuration:
- Connection: `database` (see `QUEUE_CONNECTION` in .env)
- Run worker: `php artisan queue:work`
- In development, use `composer run dev` to auto-start queue listener

## Testing Strategy

### Test Organization

- **Feature tests**: `tests/Feature/` - HTTP, Livewire, full-stack scenarios
- **Unit tests**: `tests/Unit/` - Individual class logic
- **Browser tests**: `tests/Browser/` - Pest v4 browser automation

### Pest Conventions

- Use `it()` and `expect()` syntax
- Dataset pattern for validation tests
- Mock Prism/Overpass calls in tests to avoid API usage
- Use `RefreshDatabase` trait for database tests

Example:
```php
it('analyzes log entries', function () {
    $entry = LogEntry::factory()->create(['raw' => '[ERROR] Test']);

    expect($entry->raw)->toContain('ERROR');
});
```

## Environment Variables

Key variables beyond standard Laravel config:

```bash
# Prism (choose one or more providers)
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
GEMINI_API_KEY=

# Overpass
OVERPASS_SCRIPT_PATH=overpass-ai/main.py
OVERPASS_TIMEOUT=90

# Prism Server (optional)
PRISM_SERVER_ENABLED=false
```

## Laravel Herd

The application runs on Laravel Herd:
- URL: `http://loganalysisai.test` (auto-configured based on directory name)
- No need to run `php artisan serve` manually when using Herd
- Use Laravel Boost's `get-absolute-url` tool to generate correct URLs

## MCP Server (Log Watcher)

The application includes an MCP server (`app/MCP/LogWatcher.php`) that monitors log file changes:

```bash
php artisan mcp:serve
```

The watcher:
- Polls `storage/logs/laravel.log` every 3 seconds
- Detects new lines via file size comparison
- Dispatches jobs for new log entries

## Code Style Guidelines

Beyond standard Laravel Boost rules:

### Active Classes
- Instantiate dependencies in constructors (use property promotion)
- Return explicit types always
- Keep methods focused and small
- No static methods unless truly stateless

### Models
- Use `casts()` method (not `$casts` property)
- Define relationship return types: `public function vector(): HasOne`
- Eager load relationships to prevent N+1 queries

### Migrations
- Use descriptive names: `create_log_vectors_table`
- Include foreign key constraints with cascade rules
- When modifying columns, include ALL previous attributes

### Controllers
- This application has minimal controllers (single welcome route)
- Use Form Requests for validation if adding API endpoints
- Keep controllers thin, delegate to Active classes

## Common Patterns

### Processing New Log Entry

1. LogMonitor detects new line
2. Creates LogEntry model
3. Dispatches AnalyzeLogJob
4. Job calls LogVectorizer → LogAnalyzer → IncidentManager
5. Dashboard refreshes to show new incident

### Semantic Search Flow

1. Vectorize query text via Overpass
2. Query `log_vectors` table for similar embeddings
3. Use results as context for Prism analysis
4. Return enriched AI response

## Project-Specific Notes

- The welcome page (`resources/views/welcome.blade.php`) is the main dashboard
- No authentication system (per requirements)
- Logs can grow large - consider periodic archiving/truncation
- SQLite file: `database/database.sqlite` (created via `touch` or migrations)
- Python environment must have sentence-transformers and dependencies installed
- Simulate errors with: `Log::error('Test failure message');`

## Additional Resources

- Laravel Boost tools are available via MCP server
- Use `search-docs` tool for Laravel/Livewire/Pest documentation
- Prism docs: https://prismphp.com
- Technical spec: `.ai/technical-spec.md`
