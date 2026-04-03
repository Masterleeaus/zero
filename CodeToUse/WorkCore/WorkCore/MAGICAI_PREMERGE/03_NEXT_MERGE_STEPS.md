# Next merge steps into MagicAI

1. Create a single `WorkCoreServiceProvider` or Titan equivalent and register only retained feature bindings.
2. Split remaining routes into feature route files instead of standalone app web/api/public stacks.
3. Rename surviving migrations with a feature prefix before import.
4. Replace remaining references to source-auth, source-company, and source-permission models with MagicAI host models.
5. Move feature config into one dedicated config file instead of generic Laravel config files.
6. Review `composer.json` and `package.json` and carry across only feature-specific dependencies.
