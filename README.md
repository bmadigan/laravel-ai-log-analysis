# Laravel Log Analysis AI

An AI-powered log analysis system that automatically monitors, vectorizes, and analyzes Laravel application logs to detect incidents and assess severity levels.

## Features

- **Real-time Log Monitoring**: MCP server integration for continuous log file watching
- **AI-Powered Analysis**: Uses LLMs (Claude, GPT, etc.) via Prism to analyze log entries
- **Semantic Search**: Vector embeddings enable similarity-based log grouping
- **Incident Detection**: Automatic severity classification (low, medium, high, critical)
- **Interactive Dashboard**: Livewire-powered UI with dark mode support
- **Asynchronous Processing**: Queue-based architecture for scalable analysis

## Tech Stack

- **Laravel 12**: Latest Laravel framework
- **Prism v0.96**: Multi-provider LLM integration
- **Overpass v0.7**: Python ML bridge for embeddings
- **Livewire 3 + Volt**: Interactive, real-time dashboard
- **Laravel MCP v0.3**: Model Context Protocol server
- **SQLite**: Vector embeddings stored as JSON for future semantic search
- **Tailwind CSS v4**: Modern styling with dark mode
- **Sentence Transformers**: all-MiniLM-L6-v2 for 384-dim embeddings

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- Python 3.9 or higher
- SQLite 3.x

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd LogAnalysisAi
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Set up environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**

   The default SQLite database is already configured in `.env`:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

   Create the database file:
   ```bash
   touch database/database.sqlite
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Install Python dependencies for Overpass**
   ```bash
   cd overpass-ai
   pip3 install -r requirements.txt
   cd ..
   ```

   Note: The first run will download the sentence-transformers model (~80MB).

## Configuration

### AI Provider Setup

Configure your preferred LLM provider in `.env`:

**For Anthropic Claude:**
```env
LOG_ANALYSIS_PROVIDER=anthropic
LOG_ANALYSIS_MODEL=claude-3-haiku-20240307
ANTHROPIC_API_KEY=your_api_key_here
```

**For OpenAI:**
```env
LOG_ANALYSIS_PROVIDER=openai
LOG_ANALYSIS_MODEL=gpt-4-turbo
OPENAI_API_KEY=your_api_key_here
```

### Analysis Parameters

Adjust AI analysis settings:
```env
LOG_ANALYSIS_MAX_TOKENS=200
LOG_ANALYSIS_TEMPERATURE=0.3
```

### Python Bridge Configuration

Overpass is pre-configured to use the `overpass-ai` directory. Ensure Python 3.9+ is active:
```bash
python3 --version  # Should show Python 3.9 or higher
```

## Running the Application

### Quick Start (Recommended)

Start all services at once with a single command:
```bash
composer run dev
```

This starts: web server, queue worker, log viewer (Pail), and Vite dev server.

### Individual Services

Alternatively, run services separately:

**1. Start the Development Server**
```bash
php artisan serve
```

**2. Start the Queue Worker**
In a separate terminal:
```bash
php artisan queue:work
```

**3. Build Frontend Assets**
For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

**4. MCP Server (Optional)**
The MCP server runs automatically with the web server. It's accessible at:
```
http://loganalysisai.test/mcp/log-watcher
```

To test it with MCP Inspector:
```bash
php artisan mcp:inspector
```

## Usage

### Web Dashboard

Visit `http://loganalysisai.test` (if using Laravel Herd) or `http://localhost:8000` to access the interactive dashboard showing:
- Recent log entries (latest 10)
- AI-detected incidents (latest 5)
- Real-time refresh capability

### MCP Integration

The application includes an MCP (Model Context Protocol) server for AI assistant integration. It's available at `/mcp/log-watcher` and provides:

- **Tools**: `monitor_logs` - Process recent log entries with AI
- **Resources**: `log_entries` - Access analyzed log data

### Monitoring Logs

You can manually trigger log analysis:

```php
use App\Actions\LogMonitor;

$monitor = new LogMonitor();
$monitor->handleNewLine('[2024-10-22 12:00:00] production.ERROR: Database connection failed');
```

### Generating Test Data

The best way to test the full analysis pipeline is to generate real log entries:

```bash
php artisan tinker
```

```php
// Generate log entries that will be analyzed by AI
Log::error('Database connection failed');
Log::warning('High memory usage detected: 95%');
Log::error('API rate limit exceeded for endpoint /users');
Log::critical('Payment gateway timeout - transaction failed');
exit
```

These logs will automatically:
1. Be detected by the log monitor
2. Trigger the analysis queue job
3. Get vectorized via Overpass
4. Be analyzed by Prism AI
5. Create incidents visible in the dashboard

Alternatively, use factories to create sample data (bypasses analysis):
```php
// Create log entries without analysis
App\Models\LogEntry::factory()->count(20)->create();

// Create incidents directly
App\Models\Incident::factory()->count(5)->create();
```

## Development Commands

**Format code with Laravel Pint:**
```bash
vendor/bin/pint
```

**Check specific files:**
```bash
vendor/bin/pint --dirty
```

**Run Tinker REPL:**
```bash
php artisan tinker
```

**Clear all caches:**
```bash
php artisan optimize:clear
```

## Project Structure

```
app/
├── Actions/           # Domain logic classes
│   ├── LogMonitor.php
│   ├── LogVectorizer.php
│   ├── LogAnalyzer.php
│   └── IncidentManager.php
├── Jobs/              # Queue jobs
│   └── AnalyzeLogJob.php
├── Mcp/               # MCP server components
│   ├── Servers/
│   ├── Tools/
│   └── Resources/
└── Models/            # Eloquent models
    ├── LogEntry.php
    ├── LogVector.php
    └── Incident.php

overpass-ai/           # Python ML bridge
├── main.py
├── vectorize.py
└── requirements.txt

resources/views/livewire/  # Volt components
└── log-dashboard.blade.php
```

## Architecture

The application follows a clean architecture pattern:

1. **Log Ingestion**: `LogMonitor` creates `LogEntry` records
2. **Async Processing**: `AnalyzeLogJob` is dispatched to queue
3. **Vectorization**: `LogVectorizer` generates embeddings via Overpass
4. **AI Analysis**: `LogAnalyzer` uses Prism to assess severity
5. **Incident Creation**: `IncidentManager` stores analysis results
6. **Dashboard Display**: Livewire shows real-time insights

## Common Issues

### Queue not processing

Ensure the queue worker is running:
```bash
php artisan queue:work --verbose
```

### Python environment errors

Reinstall Python dependencies:
```bash
cd overpass-ai
pip3 install --upgrade -r requirements.txt
```

If you see "ModuleNotFoundError: No module named 'sentence_transformers'", ensure you've installed the requirements.

### LLM API errors

Check your API key configuration and ensure you have credits/quota:
```bash
php artisan tinker
>>> config('prism.providers.anthropic.api_key')
```

### Database locked errors

SQLite has limited concurrency. For production, consider PostgreSQL or MySQL.

## Tutorial Purpose

This project is designed as a tutorial for integrating AI capabilities into Laravel applications. It demonstrates:
- Multi-provider LLM integration via Prism
- Python ML bridge with Overpass
- MCP server implementation
- Real-time Livewire dashboards
- Queue-based async processing

**Note**: This is an educational project. Automated testing is intentionally omitted to focus on core functionality.

## License

This project is open-sourced software licensed under the MIT license.
