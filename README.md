# medas-config-manager

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Loads and merges hierarchical configuration from YAML files and `.env` files, then exposes the values through a single `ConfigManager` service that implements the core `ConfigManager` interface.

Key behaviours:

- **YAML directories** — `addDirectory()` recursively finds all `.yaml` files in a directory and deep-merges them into a shared `DataTree`. Multiple directories can be added; later values overwrite earlier ones on the same path.
- **`.env` files** — `readEnv()` loads a `.env` file via `vlucas/phpdotenv` and populates `$_ENV`.
- **Environment variable interpolation** — values retrieved via `getValue()` are post-processed: `$env(KEY)` is replaced with the environment variable `KEY`; `$envJson(KEY)` decodes the variable as JSON; inline `$env(KEY)` tokens within a larger string are replaced in place.
- **Enum and constant resolution** — string values matching `ClassName::CaseName` are resolved to enum cases; values matching a defined constant name are resolved to the constant value.
- **Caching** — the parsed `DataTree` and environment snapshot are stored in the framework cache on the first load so later requests skip the filesystem and YAML parsing overhead.

A console command is included to generate and append new secrets to `.env` files.

## Usage

### Package developer context

Register the package and inject `ConfigManager` wherever configuration values are needed:

```php
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConfigManager\ConfigManager;
use Medas\Core\Attributes\Service;

// Register the package with the framework bootstrapper
ConfigManagerPackage::instance();
```

**Bootstrap — loading YAML and .env files:**

```php
// Typically done once in the application entry point, before the DI container is built
$configManager = new ConfigManager(new ValueProcessor(new EnvValueReplacer()));

$configManager
    ->readEnv(__DIR__)                   // loads .env from the project root
    ->addDirectory(__DIR__ . '/config'); // loads all .yaml files under config/
```

**Reading values from YAML:**

Given `config/database.yaml`:
```yaml
database:
  host: localhost
  port: 3306
  name: my_app
  password: $env(DB_PASSWORD)
```

```php
#[Service]
readonly class DatabaseConnectionFactory
{
    public function __construct(
        private ConfigManager $configManager,
    ) {}

    public function create(): \PDO
    {
        $host     = $this->configManager->getValue('database.host');
        $port     = $this->configManager->getValue('database.port');
        $name     = $this->configManager->getValue('database.name');
        $password = $this->configManager->getValue('database.password'); // resolved from $_ENV['DB_PASSWORD']

        return new \PDO("mysql:host=$host;port=$port;dbname=$name", password: $password);
    }
}
```

**Writing a value at runtime** (e.g., from a console command or migration):

```php
$this->configManager->setValue('feature.new-dashboard', true);
```

**Checking presence before reading:**

```php
if ($this->configManager->hasValue('mail.from')) {
    $from = $this->configManager->getValue('mail.from');
}
```

**Environment variable interpolation syntax:**

```yaml
# Whole value replaced with the env variable (preserves its type after $envJson)
api_key: $env(THIRD_PARTY_API_KEY)

# JSON-encoded env variable decoded to an array/object
allowed_ips: $envJson(ALLOWED_IPS_JSON)

# Inline replacement within a larger string
dsn: "mysql:host=$env(DB_HOST);dbname=$env(DB_NAME)"
```

**Enum and constant resolution in YAML:**

```yaml
# Resolved to the enum case Medas\Logging\LogLevel::Debug
log_level: Medas\Logging\LogLevel::Debug

# Resolved to the value of the PHP_INT_MAX constant
max_items: PHP_INT_MAX
```

### Backend user context

**Creating a secret and appending it to `.env`:**

```bash
php bin/console config-manager:create-env-secret APP_SECRET
# Appends APP_SECRET=<random 32-char alphanumeric string> to .env

# Custom length and target file
php bin/console config-manager:create-env-secret JWT_SECRET --length=64 --file=.env.production
```

The secret is generated with `CodeGenerator` using an alphanumeric character set. If the variable name already exists in the target file, the command throws an error rather than overwriting it.

**Structuring config files:**

Place YAML files anywhere inside your config directory; they are merged recursively. A recommended layout:

```
config/
  app.yaml        # general application settings
  database.yaml   # database connection details
  mail.yaml       # mailer settings
  services/
    payments.yaml # third-party service credentials (values use $env() references)
```

Dot notation is used to access nested keys:

```php
$this->configManager->getValue('services.payments.stripe.public-key');
```
