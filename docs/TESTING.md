# Backend testing guide

## Are mocks preferred in real systems?

**Use both — they solve different problems.**

| Test type | What it tests | Uses DB? | Speed |
|-----------|---------------|----------|-------|
| **Unit** (services) | Business rules, validation, errors | No — mock repositories | Fast |
| **API** (controllers) | JSON shape, status codes, wiring | No — mock services | Fast |
| **Integration** | SQL, migrations, real MySQL | Yes — test database | Slower |

### When mocks are a good fit (what we use here)

- Service logic: “login fails if password wrong”
- Controllers: “register returns 201 + token field”
- No MySQL required in CI — tests run anywhere

### When mocks are not enough

- Repository SQL correctness
- Full HTTP stack + Apache rewrite rules
- Cross-service contracts

For those, add **integration tests** against a disposable MySQL (Docker or GitHub Actions service container). Do not mock the database for those.

**Rule of thumb:** mock **your** dependencies at the boundary you own (repositories in unit tests, services in API tests). Do not mock PHP itself or the framework unless necessary.

## Run tests

```bash
composer install
composer test
```

Or:

```bash
C:\xampp\php\php.exe vendor\bin\phpunit
```

## Layout

```
tests/
├── Unit/
│   ├── Services/     # AuthService, TodoService + mock repos
│   ├── Http/         # Router
│   └── Support/      # Validator
└── Api/
    ├── AuthApiTest.php      # Controllers + mock AuthService
    ├── TodoApiTest.php      # Controllers + mock TodoService
    └── Middleware/
```

## CI

Run `composer test` locally before pushing changes.
