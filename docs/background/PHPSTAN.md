# PHPStan Static Analysis

PHPStan is configured at **level 6** for static code analysis with Laravel support via Larastan.

## Quick Start

```bash
# Run PHPStan analysis
make phpstan

# Update baseline (after fixing errors)
make phpstan-baseline
```

## Configuration

- **Config File**: `phpstan.neon`
- **Baseline**: `phpstan-baseline.neon` (185 errors baselined)
- **Level**: 6 (good balance of strictness)
- **PHP Version**: 8.4

## Current Baseline

The baseline captures **185 errors** found on initial setup. These are primarily:

### Error Categories:

1. **Missing Iterable Type Hints** (~60%)
   - `array` should be `array<string>`, `array<int, User>`, etc.
   - Example: `public function buildButtons(array $buttons)` → `public function buildButtons(array<array<string, mixed>> $buttons)`

2. **Missing Generic Types on Collections** (~25%)
   - `Collection` should specify `Collection<int, WagerEntry>`
   - Eloquent relationships need `@return` annotations

3. **Dynamic Model Method Calls** (~10%)
   - Methods like `getTelegramService()` not recognized on dynamically retrieved models
   - Can be fixed with `@mixin` annotations or stricter type hints

4. **Nullable Type Issues** (~5%)
   - `Model|null` being passed where specific model expected
   - Relationships returning `Model` instead of specific type

## Improvement Strategy

### Phase 1: Low-Hanging Fruit (Quick Wins)
- Add array type hints to method parameters
- Add `@return` annotations to relationships
- Fix obvious nullable issues

### Phase 2: Collections & Generics
- Add generic types to Collection usage
- Use `@var Collection<TKey, TModel>` annotations

### Phase 3: Advanced
- Add `@mixin` annotations to models for IDE support
- Migrate to native union types where appropriate
- Consider bumping to level 7 or 8

## Benefits

- **Early Bug Detection**: Catch type errors before runtime
- **Better IDE Support**: Improved autocomplete and refactoring
- **Documentation**: Type hints serve as inline documentation
- **Refactoring Safety**: Confident code changes with type checking

## Baseline Philosophy

The baseline allows us to:
1. ✅ **Add PHPStan immediately** without blocking development
2. ✅ **Prevent new errors** from being introduced
3. ✅ **Track progress** as we fix baselined errors
4. ✅ **Incremental improvement** without massive refactoring

New code should aim for **zero PHPStan errors** without baseline exceptions.

## CI Integration (Future)

```yaml
# .github/workflows/phpstan.yml
- name: Run PHPStan
  run: composer phpstan
```

This will catch any NEW errors introduced, while allowing existing baselined errors.
