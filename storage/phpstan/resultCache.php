<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1760538041,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '2.1.31',
  'metaExtensions' => 
  array (
  ),
  'phpVersion' => 80413,
  'projectConfig' => '{conditionalTags: {Larastan\\Larastan\\Rules\\NoEnvCallsOutsideOfConfigRule: {phpstan.rules.rule: %noEnvCallsOutsideOfConfig%}, Larastan\\Larastan\\Rules\\NoModelMakeRule: {phpstan.rules.rule: %noModelMake%}, Larastan\\Larastan\\Rules\\NoUnnecessaryCollectionCallRule: {phpstan.rules.rule: %noUnnecessaryCollectionCall%}, Larastan\\Larastan\\Rules\\NoUnnecessaryEnumerableToArrayCallsRule: {phpstan.rules.rule: %noUnnecessaryEnumerableToArrayCalls%}, Larastan\\Larastan\\Rules\\OctaneCompatibilityRule: {phpstan.rules.rule: %checkOctaneCompatibility%}, Larastan\\Larastan\\Rules\\UnusedViewsRule: {phpstan.rules.rule: %checkUnusedViews%}, Larastan\\Larastan\\Rules\\NoMissingTranslationsRule: {phpstan.rules.rule: %checkMissingTranslations%}, Larastan\\Larastan\\Rules\\ModelAppendsRule: {phpstan.rules.rule: %checkModelAppends%}, Larastan\\Larastan\\Rules\\NoPublicModelScopeAndAccessorRule: {phpstan.rules.rule: %checkModelMethodVisibility%}, Larastan\\Larastan\\Rules\\NoAuthFacadeInRequestScopeRule: {phpstan.rules.rule: %checkAuthCallsWhenInRequestScope%}, Larastan\\Larastan\\Rules\\NoAuthHelperInRequestScopeRule: {phpstan.rules.rule: %checkAuthCallsWhenInRequestScope%}, Larastan\\Larastan\\ReturnTypes\\Helpers\\EnvFunctionDynamicFunctionReturnTypeExtension: {phpstan.broker.dynamicFunctionReturnTypeExtension: %generalizeEnvReturnType%}, Larastan\\Larastan\\ReturnTypes\\Helpers\\ConfigFunctionDynamicFunctionReturnTypeExtension: {phpstan.broker.dynamicFunctionReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\ReturnTypes\\ConfigRepositoryDynamicMethodReturnTypeExtension: {phpstan.broker.dynamicMethodReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\ReturnTypes\\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension: {phpstan.broker.dynamicStaticMethodReturnTypeExtension: %checkConfigTypes%}, Larastan\\Larastan\\Rules\\ConfigCollectionRule: {phpstan.rules.rule: %checkConfigTypes%}}, parameters: {universalObjectCratesClasses: [Illuminate\\Http\\Request, Illuminate\\Support\\Optional], earlyTerminatingFunctionCalls: [abort, dd], mixinExcludeClasses: [Eloquent], bootstrapFiles: [bootstrap.php], checkOctaneCompatibility: false, noEnvCallsOutsideOfConfig: true, noModelMake: true, noUnnecessaryCollectionCall: true, noUnnecessaryCollectionCallOnly: [], noUnnecessaryCollectionCallExcept: [], noUnnecessaryEnumerableToArrayCalls: false, squashedMigrationsPath: [], databaseMigrationsPath: [], disableMigrationScan: false, disableSchemaScan: false, configDirectories: [], viewDirectories: [], translationDirectories: [], checkModelProperties: false, checkUnusedViews: false, checkMissingTranslations: false, checkModelAppends: true, checkModelMethodVisibility: false, generalizeEnvReturnType: false, checkConfigTypes: false, checkAuthCallsWhenInRequestScope: false, paths: [/var/www/html/app, /var/www/html/config, /var/www/html/routes], level: 6, tmpDir: /var/www/html/storage/phpstan, excludePaths: {analyseAndScan: [bootstrap/cache/*, storage/*, vendor/*], analyse: []}, phpVersion: 80400}, rules: [Larastan\\Larastan\\Rules\\UselessConstructs\\NoUselessWithFunctionCallsRule, Larastan\\Larastan\\Rules\\UselessConstructs\\NoUselessValueFunctionCallsRule, Larastan\\Larastan\\Rules\\DeferrableServiceProviderMissingProvidesRule, Larastan\\Larastan\\Rules\\ConsoleCommand\\UndefinedArgumentOrOptionRule], services: [{class: Larastan\\Larastan\\Methods\\RelationForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ModelForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\EloquentBuilderForwardsCallsExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\HigherOrderTapProxyExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\HigherOrderCollectionProxyExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\StorageMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\Extension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ModelFactoryMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\RedirectResponseMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\MacroMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Methods\\ViewWithMethodsClassReflectionExtension, tags: [phpstan.broker.methodsClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\ModelAccessorExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\ModelPropertyExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\Properties\\HigherOrderCollectionProxyPropertyExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\HigherOrderTapProxyExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Contracts\\Container\\Container}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Container\\Container}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerArrayAccessDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {className: Illuminate\\Contracts\\Foundation\\Application}}, {class: Larastan\\Larastan\\Properties\\ModelRelationsExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelOnlyDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelFactoryDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ModelDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AuthExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\GuardDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AuthManagerExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\DateExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\GuardExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestFileExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestRouteExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RequestUserExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\EloquentBuilderExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\RelationCollectionExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TestCaseExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Support\\CollectionHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\AuthExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\CollectExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\NowAndTodayExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ResponseExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ValidatorExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\LiteralExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\CollectionFilterRejectDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\CollectionWhereNotNullDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\NewModelQueryDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\FactoryDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: abort, negate: false}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: abort, negate: true}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: throw, negate: false}}, {class: Larastan\\Larastan\\Types\\AbortIfFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension], arguments: {methodName: throw, negate: true}}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\AppExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ValueExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\StrExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\TapExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\StorageDynamicStaticMethodReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\GenericEloquentCollectionTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Types\\ViewStringTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Rules\\OctaneCompatibilityRule}, {class: Larastan\\Larastan\\Rules\\NoEnvCallsOutsideOfConfigRule, arguments: {configDirectories: %configDirectories%}}, {class: Larastan\\Larastan\\Rules\\NoModelMakeRule}, {class: Larastan\\Larastan\\Rules\\NoUnnecessaryCollectionCallRule, arguments: {onlyMethods: %noUnnecessaryCollectionCallOnly%, excludeMethods: %noUnnecessaryCollectionCallExcept%}}, {class: Larastan\\Larastan\\Rules\\NoUnnecessaryEnumerableToArrayCallsRule}, {class: Larastan\\Larastan\\Rules\\ModelAppendsRule}, {class: Larastan\\Larastan\\Rules\\NoPublicModelScopeAndAccessorRule}, {class: Larastan\\Larastan\\Types\\GenericEloquentBuilderTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {class: Illuminate\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\AppEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension], arguments: {class: Illuminate\\Contracts\\Foundation\\Application}}, {class: Larastan\\Larastan\\ReturnTypes\\AppFacadeEnvironmentReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Types\\ModelProperty\\ModelPropertyTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension], arguments: {active: %checkModelProperties%}}, {class: Larastan\\Larastan\\Types\\CollectionOf\\CollectionOfTypeNodeResolverExtension, tags: [phpstan.phpDoc.typeNodeResolverExtension]}, {class: Larastan\\Larastan\\Properties\\MigrationHelper, arguments: {databaseMigrationPath: %databaseMigrationsPath%, disableMigrationScan: %disableMigrationScan%, parser: @currentPhpVersionSimpleDirectParser, reflectionProvider: @reflectionProvider}}, {class: Larastan\\Larastan\\Properties\\SquashedMigrationHelper, arguments: {schemaPaths: %squashedMigrationsPath%, disableSchemaScan: %disableSchemaScan%}}, {class: Larastan\\Larastan\\Properties\\ModelCastHelper}, {class: Larastan\\Larastan\\Properties\\ModelPropertyHelper}, {class: Larastan\\Larastan\\Rules\\ModelRuleHelper}, {class: Larastan\\Larastan\\Methods\\BuilderHelper, arguments: {checkProperties: %checkModelProperties%}}, {class: Larastan\\Larastan\\Rules\\RelationExistenceRule, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Rules\\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule, arguments: {dispatchableClass: Illuminate\\Foundation\\Bus\\Dispatchable}, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Rules\\CheckDispatchArgumentTypesCompatibleWithClassConstructorRule, arguments: {dispatchableClass: Illuminate\\Foundation\\Events\\Dispatchable}, tags: [phpstan.rules.rule]}, {class: Larastan\\Larastan\\Properties\\Schema\\MySqlDataTypeToPhpTypeConverter}, {class: Larastan\\Larastan\\LarastanStubFilesExtension, tags: [phpstan.stubFilesExtension]}, {class: Larastan\\Larastan\\Rules\\UnusedViewsRule}, {class: Larastan\\Larastan\\Collectors\\UsedViewFunctionCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedEmailViewCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewMakeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewFacadeMakeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedRouteFacadeViewCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedViewInAnotherViewCollector}, {class: Larastan\\Larastan\\Support\\ViewFileHelper, arguments: {viewDirectories: %viewDirectories%}}, {class: Larastan\\Larastan\\Support\\ViewParser, arguments: {parser: @currentPhpVersionSimpleDirectParser}}, {class: Larastan\\Larastan\\Rules\\NoMissingTranslationsRule, arguments: {translationDirectories: %translationDirectories%}}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationFunctionCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationTranslatorCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationFacadeCollector, tags: [phpstan.collector]}, {class: Larastan\\Larastan\\Collectors\\UsedTranslationViewCollector}, {class: Larastan\\Larastan\\ReturnTypes\\ApplicationMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ContainerMakeDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\ArgumentDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\HasArgumentDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\OptionDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\ConsoleCommand\\HasOptionDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TranslatorGetReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\LangGetReturnTypeExtension, tags: [phpstan.broker.dynamicStaticMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\TransHelperReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\DoubleUnderscoreHelperReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: Larastan\\Larastan\\ReturnTypes\\AppMakeHelper}, {class: Larastan\\Larastan\\Internal\\ConsoleApplicationResolver}, {class: Larastan\\Larastan\\Internal\\ConsoleApplicationHelper}, {class: Larastan\\Larastan\\Support\\HigherOrderCollectionProxyHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\ConfigFunctionDynamicFunctionReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\ConfigRepositoryDynamicMethodReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\ConfigFacadeCollectionDynamicStaticMethodReturnTypeExtension}, {class: Larastan\\Larastan\\Support\\ConfigParser, arguments: {parser: @currentPhpVersionSimpleDirectParser, configPaths: %configDirectories%}}, {class: Larastan\\Larastan\\Internal\\ConfigHelper}, {class: Larastan\\Larastan\\ReturnTypes\\Helpers\\EnvFunctionDynamicFunctionReturnTypeExtension}, {class: Larastan\\Larastan\\ReturnTypes\\FormRequestSafeDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: Larastan\\Larastan\\Rules\\NoAuthFacadeInRequestScopeRule}, {class: Larastan\\Larastan\\Rules\\NoAuthHelperInRequestScopeRule}, {class: Larastan\\Larastan\\Rules\\ConfigCollectionRule}, {class: Illuminate\\Filesystem\\Filesystem, autowired: self}]}',
  'analysedPaths' => 
  array (
    0 => '/var/www/html/app',
    1 => '/var/www/html/config',
    2 => '/var/www/html/routes',
  ),
  'scannedFiles' => 
  array (
  ),
  'composerLocks' => 
  array (
    '/var/www/html/composer.lock' => '32e67eb94b34de1d275a7c8a7d8d07e443d5ac02',
  ),
  'composerInstalled' => 
  array (
    '/var/www/html/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'brianium/paratest' => 
        array (
          'pretty_version' => 'v7.8.4',
          'version' => '7.8.4.0',
          'reference' => '130a9bf0e269ee5f5b320108f794ad03e275cad4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../brianium/paratest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'brick/math' => 
        array (
          'pretty_version' => '0.14.0',
          'version' => '0.14.0.0',
          'reference' => '113a8ee2656b882d4c3164fa31aa6e12cbb7aaa2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../brick/math',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'carbonphp/carbon-doctrine-types' => 
        array (
          'pretty_version' => '3.2.0',
          'version' => '3.2.0.0',
          'reference' => '18ba5ddfec8976260ead6e866180bd5d2f71aa1d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../carbonphp/carbon-doctrine-types',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'cordoval/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'davedevelopment/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'dflydev/dot-access-data' => 
        array (
          'pretty_version' => 'v3.0.3',
          'version' => '3.0.3.0',
          'reference' => 'a23a2bf4f31d3518f3ecb38660c95715dfead60f',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../dflydev/dot-access-data',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/deprecations' => 
        array (
          'pretty_version' => '1.1.5',
          'version' => '1.1.5.0',
          'reference' => '459c2f5dd3d6a4633d3b5f46ee2b1c40f57d3f38',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../doctrine/deprecations',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'doctrine/inflector' => 
        array (
          'pretty_version' => '2.1.0',
          'version' => '2.1.0.0',
          'reference' => '6d6c96277ea252fc1304627204c3d5e6e15faa3b',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../doctrine/inflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'doctrine/lexer' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => '31ad66abc0fc9e1a1f2d9bc6a42668d2fbbcd6dd',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../doctrine/lexer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'dragonmantank/cron-expression' => 
        array (
          'pretty_version' => 'v3.4.0',
          'version' => '3.4.0.0',
          'reference' => '8c784d071debd117328803d86b2097615b457500',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../dragonmantank/cron-expression',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'egulias/email-validator' => 
        array (
          'pretty_version' => '4.0.4',
          'version' => '4.0.4.0',
          'reference' => 'd42c8731f0624ad6bdc8d3e5e9a4524f68801cfa',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../egulias/email-validator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'fakerphp/faker' => 
        array (
          'pretty_version' => 'v1.24.1',
          'version' => '1.24.1.0',
          'reference' => 'e0ee18eb1e6dc3cda3ce9fd97e5a0689a88a64b5',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../fakerphp/faker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fidry/cpu-core-counter' => 
        array (
          'pretty_version' => '1.3.0',
          'version' => '1.3.0.0',
          'reference' => 'db9508f7b1474469d9d3c53b86f817e344732678',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../fidry/cpu-core-counter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'filp/whoops' => 
        array (
          'pretty_version' => '2.18.4',
          'version' => '2.18.4.0',
          'reference' => 'd2102955e48b9fd9ab24280a7ad12ed552752c4d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../filp/whoops',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fruitcake/php-cors' => 
        array (
          'pretty_version' => 'v1.3.0',
          'version' => '1.3.0.0',
          'reference' => '3d158f36e7875e2f040f37bc0573956240a5a38b',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../fruitcake/php-cors',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'graham-campbell/result-type' => 
        array (
          'pretty_version' => 'v1.1.3',
          'version' => '1.1.3.0',
          'reference' => '3ba905c11371512af9d9bdd27d99b782216b6945',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../graham-campbell/result-type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/guzzle' => 
        array (
          'pretty_version' => '7.10.0',
          'version' => '7.10.0.0',
          'reference' => 'b51ac707cfa420b7bfd4e4d5e510ba8008e822b4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../guzzlehttp/guzzle',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/promises' => 
        array (
          'pretty_version' => '2.3.0',
          'version' => '2.3.0.0',
          'reference' => '481557b130ef3790cf82b713667b43030dc9c957',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../guzzlehttp/promises',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/psr7' => 
        array (
          'pretty_version' => '2.8.0',
          'version' => '2.8.0.0',
          'reference' => '21dc724a0583619cd1652f673303492272778051',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../guzzlehttp/psr7',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'guzzlehttp/uri-template' => 
        array (
          'pretty_version' => 'v1.0.5',
          'version' => '1.0.5.0',
          'reference' => '4f4bbd4e7172148801e76e3decc1e559bdee34e1',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../guzzlehttp/uri-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'hamcrest/hamcrest-php' => 
        array (
          'pretty_version' => 'v2.1.1',
          'version' => '2.1.1.0',
          'reference' => 'f8b1c0173b22fa6ec77a81fe63e5b01eba7e6487',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../hamcrest/hamcrest-php',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'iamcal/sql-parser' => 
        array (
          'pretty_version' => 'v0.6',
          'version' => '0.6.0.0',
          'reference' => '947083e2dca211a6f12fb1beb67a01e387de9b62',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../iamcal/sql-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'illuminate/auth' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/broadcasting' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/bus' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/cache' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/collections' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/concurrency' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/conditionable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/config' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/console' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/container' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/contracts' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/cookie' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/database' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/encryption' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/events' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/filesystem' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/hashing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/http' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/json-schema' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/log' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/macroable' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/mail' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/notifications' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/pagination' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/pipeline' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/process' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/queue' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/redis' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/routing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/session' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/support' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/testing' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/translation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/validation' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'illuminate/view' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => 'v12.34.0',
          ),
        ),
        'inertiajs/inertia-laravel' => 
        array (
          'pretty_version' => 'v2.0.10',
          'version' => '2.0.10.0',
          'reference' => '07da425d58a3a0e3ace9c296e67bd897a6e47009',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../inertiajs/inertia-laravel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'jean85/pretty-package-versions' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '4d7aa5dab42e2a76d99559706022885de0e18e1a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../jean85/pretty-package-versions',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'kodova/hamcrest-php' => 
        array (
          'dev_requirement' => true,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'larastan/larastan' => 
        array (
          'pretty_version' => 'v3.7.2',
          'version' => '3.7.2.0',
          'reference' => 'a761859a7487bd7d0cb8b662a7538a234d5bb5ae',
          'type' => 'phpstan-extension',
          'install_path' => '/var/www/html/vendor/composer/../larastan/larastan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/framework' => 
        array (
          'pretty_version' => 'v12.34.0',
          'version' => '12.34.0.0',
          'reference' => 'f9ec5a5d88bc8c468f17b59f88e05c8ac3c8d687',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/framework',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/pail' => 
        array (
          'pretty_version' => 'v1.2.3',
          'version' => '1.2.3.0',
          'reference' => '8cc3d575c1f0e57eeb923f366a37528c50d2385a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/pail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/pint' => 
        array (
          'pretty_version' => 'v1.25.1',
          'version' => '1.25.1.0',
          'reference' => '5016e263f95d97670d71b9a987bd8996ade6d8d9',
          'type' => 'project',
          'install_path' => '/var/www/html/vendor/composer/../laravel/pint',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/prompts' => 
        array (
          'pretty_version' => 'v0.3.7',
          'version' => '0.3.7.0',
          'reference' => 'a1891d362714bc40c8d23b0b1d7090f022ea27cc',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/prompts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/sail' => 
        array (
          'pretty_version' => 'v1.46.0',
          'version' => '1.46.0.0',
          'reference' => 'eb90c4f113c4a9637b8fdd16e24cfc64f2b0ae6e',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/sail',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'laravel/serializable-closure' => 
        array (
          'pretty_version' => 'v2.0.6',
          'version' => '2.0.6.0',
          'reference' => '038ce42edee619599a1debb7e81d7b3759492819',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/serializable-closure',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'laravel/tinker' => 
        array (
          'pretty_version' => 'v2.10.1',
          'version' => '2.10.1.0',
          'reference' => '22177cc71807d38f2810c6204d8f7183d88a57d3',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../laravel/tinker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/commonmark' => 
        array (
          'pretty_version' => '2.7.1',
          'version' => '2.7.1.0',
          'reference' => '10732241927d3971d28e7ea7b5712721fa2296ca',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/commonmark',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/config' => 
        array (
          'pretty_version' => 'v1.2.0',
          'version' => '1.2.0.0',
          'reference' => '754b3604fb2984c71f4af4a9cbe7b57f346ec1f3',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/config',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem' => 
        array (
          'pretty_version' => '3.30.0',
          'version' => '3.30.0.0',
          'reference' => '2203e3151755d874bb2943649dae1eb8533ac93e',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/flysystem',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/flysystem-local' => 
        array (
          'pretty_version' => '3.30.0',
          'version' => '3.30.0.0',
          'reference' => '6691915f77c7fb69adfb87dcd550052dc184ee10',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/flysystem-local',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/mime-type-detection' => 
        array (
          'pretty_version' => '1.16.0',
          'version' => '1.16.0.0',
          'reference' => '2d6702ff215bf922936ccc1ad31007edc76451b9',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/mime-type-detection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri' => 
        array (
          'pretty_version' => '7.5.1',
          'version' => '7.5.1.0',
          'reference' => '81fb5145d2644324614cc532b28efd0215bda430',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/uri',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'league/uri-interfaces' => 
        array (
          'pretty_version' => '7.5.0',
          'version' => '7.5.0.0',
          'reference' => '08cfc6c4f3d811584fb09c37e2849e6a7f9b0742',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../league/uri-interfaces',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'mockery/mockery' => 
        array (
          'pretty_version' => '1.6.12',
          'version' => '1.6.12.0',
          'reference' => '1f4efdd7d3beafe9807b08156dfcb176d18f1699',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../mockery/mockery',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'monolog/monolog' => 
        array (
          'pretty_version' => '3.9.0',
          'version' => '3.9.0.0',
          'reference' => '10d85740180ecba7896c87e06a166e0c95a0e3b6',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../monolog/monolog',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'mtdowling/cron-expression' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '^1.0',
          ),
        ),
        'myclabs/deep-copy' => 
        array (
          'pretty_version' => '1.13.4',
          'version' => '1.13.4.0',
          'reference' => '07d290f0c47959fd5eed98c95ee5602db07e0b6a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../myclabs/deep-copy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nesbot/carbon' => 
        array (
          'pretty_version' => '3.10.3',
          'version' => '3.10.3.0',
          'reference' => '8e3643dcd149ae0fe1d2ff4f2c8e4bbfad7c165f',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nesbot/carbon',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/schema' => 
        array (
          'pretty_version' => 'v1.3.2',
          'version' => '1.3.2.0',
          'reference' => 'da801d52f0354f70a638673c4a0f04e16529431d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nette/schema',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nette/utils' => 
        array (
          'pretty_version' => 'v4.0.8',
          'version' => '4.0.8.0',
          'reference' => 'c930ca4e3cf4f17dcfb03037703679d2396d2ede',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nette/utils',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nikic/php-parser' => 
        array (
          'pretty_version' => 'v5.6.1',
          'version' => '5.6.1.0',
          'reference' => 'f103601b29efebd7ff4a1ca7b3eeea9e3336a2a2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nikic/php-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'nunomaduro/collision' => 
        array (
          'pretty_version' => 'v8.8.2',
          'version' => '8.8.2.0',
          'reference' => '60207965f9b7b7a4ce15a0f75d57f9dadb105bdb',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nunomaduro/collision',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nunomaduro/termwind' => 
        array (
          'pretty_version' => 'v2.3.1',
          'version' => '2.3.1.0',
          'reference' => 'dfa08f390e509967a15c22493dc0bac5733d9123',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../nunomaduro/termwind',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'pestphp/pest' => 
        array (
          'pretty_version' => 'v3.8.4',
          'version' => '3.8.4.0',
          'reference' => '72cf695554420e21858cda831d5db193db102574',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../pestphp/pest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin' => 
        array (
          'pretty_version' => 'v3.0.0',
          'version' => '3.0.0.0',
          'reference' => 'e79b26c65bc11c41093b10150c1341cc5cdbea83',
          'type' => 'composer-plugin',
          'install_path' => '/var/www/html/vendor/composer/../pestphp/pest-plugin',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-arch' => 
        array (
          'pretty_version' => 'v3.1.1',
          'version' => '3.1.1.0',
          'reference' => 'db7bd9cb1612b223e16618d85475c6f63b9c8daa',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../pestphp/pest-plugin-arch',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'pestphp/pest-plugin-mutate' => 
        array (
          'pretty_version' => 'v3.0.5',
          'version' => '3.0.5.0',
          'reference' => 'e10dbdc98c9e2f3890095b4fe2144f63a5717e08',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../pestphp/pest-plugin-mutate',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/manifest' => 
        array (
          'pretty_version' => '2.0.4',
          'version' => '2.0.4.0',
          'reference' => '54750ef60c58e43759730615a392c31c80e23176',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phar-io/manifest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/version' => 
        array (
          'pretty_version' => '3.2.1',
          'version' => '3.2.1.0',
          'reference' => '4f7fd7836c6f332bb2933569e566a0d6c4cbed74',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phar-io/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/reflection-common' => 
        array (
          'pretty_version' => '2.2.0',
          'version' => '2.2.0.0',
          'reference' => '1d01c49d4ed62f25aa84a747ad35d5a16924662b',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpdocumentor/reflection-common',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/reflection-docblock' => 
        array (
          'pretty_version' => '5.6.3',
          'version' => '5.6.3.0',
          'reference' => '94f8051919d1b0369a6bcc7931d679a511c03fe9',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpdocumentor/reflection-docblock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpdocumentor/type-resolver' => 
        array (
          'pretty_version' => '1.10.0',
          'version' => '1.10.0.0',
          'reference' => '679e3ce485b99e84c775d28e2e96fade9a7fb50a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpdocumentor/type-resolver',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpoption/phpoption' => 
        array (
          'pretty_version' => '1.9.4',
          'version' => '1.9.4.0',
          'reference' => '638a154f8d4ee6a5cfa96d6a34dfbe0cffa9566d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpoption/phpoption',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'phpstan/phpdoc-parser' => 
        array (
          'pretty_version' => '2.3.0',
          'version' => '2.3.0.0',
          'reference' => '1e0cd5370df5dd2e556a36b9c62f62e555870495',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpstan/phpdoc-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '2.1.31',
          'version' => '2.1.31.0',
          'reference' => 'ead89849d879fe203ce9292c6ef5e7e76f867b96',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-code-coverage' => 
        array (
          'pretty_version' => '11.0.11',
          'version' => '11.0.11.0',
          'reference' => '4f7722aa9a7b76aa775e2d9d4e95d1ea16eeeef4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/php-code-coverage',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-file-iterator' => 
        array (
          'pretty_version' => '5.1.0',
          'version' => '5.1.0.0',
          'reference' => '118cfaaa8bc5aef3287bf315b6060b1174754af6',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/php-file-iterator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-invoker' => 
        array (
          'pretty_version' => '5.0.1',
          'version' => '5.0.1.0',
          'reference' => 'c1ca3814734c07492b3d4c5f794f4b0995333da2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/php-invoker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-text-template' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '3e0404dc6b300e6bf56415467ebcb3fe4f33e964',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/php-text-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-timer' => 
        array (
          'pretty_version' => '7.0.1',
          'version' => '7.0.1.0',
          'reference' => '3b415def83fbcb41f991d9ebf16ae4ad8b7837b3',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/php-timer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/phpunit' => 
        array (
          'pretty_version' => '11.5.33',
          'version' => '11.5.33.0',
          'reference' => '5965e9ff57546cb9137c0ff6aa78cb7442b05cf6',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../phpunit/phpunit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/clock' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'e41a24703d4560fd0acb709162f73b8adfc3aa0d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/clock-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/container' => 
        array (
          'pretty_version' => '2.0.2',
          'version' => '2.0.2.0',
          'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/container',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/container-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.1|2.0',
          ),
        ),
        'psr/event-dispatcher' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-client' => 
        array (
          'pretty_version' => '1.0.3',
          'version' => '1.0.3.0',
          'reference' => 'bb5906edc1c324c9a05aa0873d40117941e5fa90',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/http-client',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-client-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-factory' => 
        array (
          'pretty_version' => '1.1.0',
          'version' => '1.1.0.0',
          'reference' => '2b4765fddfe3b508ac62f829e852b1501d3f6e8a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/http-factory',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-factory-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/http-message' => 
        array (
          'pretty_version' => '2.0',
          'version' => '2.0.0.0',
          'reference' => '402d35bcb92c70c026d1a6a9883f06b2ead23d71',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/http-message',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/http-message-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/log' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/log',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/log-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
            1 => '3.0.0',
          ),
        ),
        'psr/simple-cache' => 
        array (
          'pretty_version' => '3.0.0',
          'version' => '3.0.0.0',
          'reference' => '764e0b3939f5ca87cb904f570ef9be2d78a07865',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psr/simple-cache',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'psr/simple-cache-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
          ),
        ),
        'psy/psysh' => 
        array (
          'pretty_version' => 'v0.12.12',
          'version' => '0.12.12.0',
          'reference' => 'cd23863404a40ccfaf733e3af4db2b459837f7e7',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../psy/psysh',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ralouphie/getallheaders' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => '120b605dfeb996808c31b6477290a714d356e822',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../ralouphie/getallheaders',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/collection' => 
        array (
          'pretty_version' => '2.1.1',
          'version' => '2.1.1.0',
          'reference' => '344572933ad0181accbf4ba763e85a0306a8c5e2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../ramsey/collection',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'ramsey/uuid' => 
        array (
          'pretty_version' => '4.9.1',
          'version' => '4.9.1.0',
          'reference' => '81f941f6f729b1e3ceea61d9d014f8b6c6800440',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../ramsey/uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'rhumsaa/uuid' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '4.9.1',
          ),
        ),
        'sebastian/cli-parser' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => '15c5dd40dc4f38794d383bb95465193f5e0ae180',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/cli-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit' => 
        array (
          'pretty_version' => '3.0.3',
          'version' => '3.0.3.0',
          'reference' => '54391c61e4af8078e5b276ab082b6d3c54c9ad64',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/code-unit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit-reverse-lookup' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '183a9b2632194febd219bb9246eee421dad8d45e',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/code-unit-reverse-lookup',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/comparator' => 
        array (
          'pretty_version' => '6.3.2',
          'version' => '6.3.2.0',
          'reference' => '85c77556683e6eee4323e4c5468641ca0237e2e8',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/comparator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/complexity' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => 'ee41d384ab1906c68852636b6de493846e13e5a0',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/complexity',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/diff' => 
        array (
          'pretty_version' => '6.0.2',
          'version' => '6.0.2.0',
          'reference' => 'b4ccd857127db5d41a5b676f24b51371d76d8544',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/diff',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/environment' => 
        array (
          'pretty_version' => '7.2.1',
          'version' => '7.2.1.0',
          'reference' => 'a5c75038693ad2e8d4b6c15ba2403532647830c4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/environment',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/exporter' => 
        array (
          'pretty_version' => '6.3.2',
          'version' => '6.3.2.0',
          'reference' => '70a298763b40b213ec087c51c739efcaa90bcd74',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/exporter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/global-state' => 
        array (
          'pretty_version' => '7.0.2',
          'version' => '7.0.2.0',
          'reference' => '3be331570a721f9a4b5917f4209773de17f747d7',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/global-state',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/lines-of-code' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => 'd36ad0d782e5756913e42ad87cb2890f4ffe467a',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/lines-of-code',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-enumerator' => 
        array (
          'pretty_version' => '6.0.1',
          'version' => '6.0.1.0',
          'reference' => 'f5b498e631a74204185071eb41f33f38d64608aa',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/object-enumerator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-reflector' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => '6e1a43b411b2ad34146dee7524cb13a068bb35f9',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/object-reflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/recursion-context' => 
        array (
          'pretty_version' => '6.0.3',
          'version' => '6.0.3.0',
          'reference' => 'f6458abbf32a6c8174f8f26261475dc133b3d9dc',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/recursion-context',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/type' => 
        array (
          'pretty_version' => '5.1.3',
          'version' => '5.1.3.0',
          'reference' => 'f77d2d4e78738c98d9a68d2596fe5e8fa380f449',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/version' => 
        array (
          'pretty_version' => '5.0.2',
          'version' => '5.0.2.0',
          'reference' => 'c687e3387b99f5b03b6caa64c74b63e2936ff874',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../sebastian/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'spatie/once' => 
        array (
          'dev_requirement' => false,
          'replaced' => 
          array (
            0 => '*',
          ),
        ),
        'staabm/side-effects-detector' => 
        array (
          'pretty_version' => '1.0.5',
          'version' => '1.0.5.0',
          'reference' => 'd8334211a140ce329c13726d4a715adbddd0a163',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../staabm/side-effects-detector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/clock' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => 'b81435fbd6648ea425d1ee96a2d8e68f4ceacd24',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/clock',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/console' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => '2b9c5fafbac0399a20a2e82429e2bd735dcfb7db',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/console',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/css-selector' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '601a5ce9aaad7bf10797e3663faefce9e26c24e2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/css-selector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/deprecation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '63afe740e99a13ba87ec199bb07bbdee937a5b62',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/deprecation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/error-handler' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => '99f81bc944ab8e5dae4f21b4ca9972698bbad0e4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/error-handler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher' => 
        array (
          'pretty_version' => 'v7.3.3',
          'version' => '7.3.3.0',
          'reference' => 'b7dc69e71de420ac04bc9ab830cf3ffebba48191',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '59eb412e93815df44f05f342958efa9f46b1e586',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/event-dispatcher-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.0|3.0',
          ),
        ),
        'symfony/finder' => 
        array (
          'pretty_version' => 'v7.3.2',
          'version' => '7.3.2.0',
          'reference' => '2a6614966ba1074fa93dae0bc804227422df4dfe',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/finder',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-foundation' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'c061c7c18918b1b64268771aad04b40be41dd2e6',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/http-foundation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/http-kernel' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'b796dffea7821f035047235e076b60ca2446e3cf',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/http-kernel',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mailer' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'ab97ef2f7acf0216955f5845484235113047a31d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/mailer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/mime' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'b1b828f69cbaf887fa835a091869e55df91d0e35',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/mime',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-ctype' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'a3cc8b044a6ea513310cbd48ef7333b384945638',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-ctype',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-grapheme' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '380872130d3a5dd3ace2f4010d95125fde5d5c70',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-intl-grapheme',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-idn' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '9614ac4d8061dc257ecc64cba1b140873dce8ad3',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-intl-idn',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-intl-normalizer' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '3833d7255cc303546435cb650316bff708a1c75c',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-intl-normalizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '6d857f4d76bd4b343eac26d6b539585d2bc56493',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-mbstring',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php80' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '0cc9dd0f17f61d8131e7df6b84bd344899fe2608',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-php80',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php83' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '17f6f9a6b1735c0f163024d959f700cfbc5155e5',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-php83',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php84' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'd8ced4d875142b6a7426000426b8abc631d6b191',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-php84',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-php85' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'd4e5fcd4ab3d998ab16c0db48e6cbb9a01993f91',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-php85',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/polyfill-uuid' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '21533be36c24be3f4b1669c4725c7d1d2bab4ae2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/polyfill-uuid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/process' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'f24f8f316367b30810810d4eb30c543d7003ff3b',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/process',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/routing' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => '8dc648e159e9bac02b703b9fbd937f19ba13d07c',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/routing',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/service-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'f021b05a130d35510bd6b25fe9053c2a8a15d5d4',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/service-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/string' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'f96476035142921000338bad71e5247fbc138872',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/string',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'ec25870502d0c7072d086e8ffba1420c85965174',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/translation',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'df210c7a2573f1913b2d17cc95f90f53a73d8f7d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/translation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/translation-implementation' => 
        array (
          'dev_requirement' => false,
          'provided' => 
          array (
            0 => '2.3|3.0',
          ),
        ),
        'symfony/uid' => 
        array (
          'pretty_version' => 'v7.3.1',
          'version' => '7.3.1.0',
          'reference' => 'a69f69f3159b852651a6bf45a9fdd149520525bb',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/uid',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/var-dumper' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'b8abe7daf2730d07dfd4b2ee1cecbf0dd2fbdabb',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/var-dumper',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'symfony/yaml' => 
        array (
          'pretty_version' => 'v7.3.3',
          'version' => '7.3.3.0',
          'reference' => 'd4f4a66866fe2451f61296924767280ab5732d9d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../symfony/yaml',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'ta-tikoma/phpunit-architecture-test' => 
        array (
          'pretty_version' => '0.8.5',
          'version' => '0.8.5.0',
          'reference' => 'cf6fb197b676ba716837c886baca842e4db29005',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../ta-tikoma/phpunit-architecture-test',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'telegram-bot/api' => 
        array (
          'pretty_version' => 'v2.5.0',
          'version' => '2.5.0.0',
          'reference' => 'eaae3526223db49a1bad76a2dfa501dc287979cf',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../telegram-bot/api',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'theseer/tokenizer' => 
        array (
          'pretty_version' => '1.2.3',
          'version' => '1.2.3.0',
          'reference' => '737eda637ed5e28c3413cb1ebe8bb52cbf1ca7a2',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../theseer/tokenizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'tightenco/ziggy' => 
        array (
          'pretty_version' => 'v2.6.0',
          'version' => '2.6.0.0',
          'reference' => 'cccc6035c109daab03a33926b3a8499bedbed01f',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../tightenco/ziggy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'tijsverkoyen/css-to-inline-styles' => 
        array (
          'pretty_version' => 'v2.3.0',
          'version' => '2.3.0.0',
          'reference' => '0d72ac1c00084279c1816675284073c5a337c20d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../tijsverkoyen/css-to-inline-styles',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'vlucas/phpdotenv' => 
        array (
          'pretty_version' => 'v5.6.2',
          'version' => '5.6.2.0',
          'reference' => '24ac4c74f91ee2c193fa1aaa5c249cb0822809af',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../vlucas/phpdotenv',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'voku/portable-ascii' => 
        array (
          'pretty_version' => '2.0.3',
          'version' => '2.0.3.0',
          'reference' => 'b1d923f88091c6bf09699efcd7c8a1b1bfd7351d',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../voku/portable-ascii',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
        'webmozart/assert' => 
        array (
          'pretty_version' => '1.11.0',
          'version' => '1.11.0.0',
          'reference' => '11cb2199493b2f8a3b53e7f19068fc6aac760991',
          'type' => 'library',
          'install_path' => '/var/www/html/vendor/composer/../webmozart/assert',
          'aliases' => 
          array (
          ),
          'dev_requirement' => false,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    '/var/www/html/vendor/larastan/larastan/bootstrap.php' => '28392079817075879815f110287690e80398fe5e',
    'phar:///var/www/html/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute85.php' => '123dcd45f03f2463904087a66bfe2bc139760df0',
    'phar:///var/www/html/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar:///var/www/html/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar:///var/www/html/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'PDO',
    2 => 'Phar',
    3 => 'Reflection',
    4 => 'SPL',
    5 => 'SimpleXML',
    6 => 'Zend OPcache',
    7 => 'bcmath',
    8 => 'ctype',
    9 => 'curl',
    10 => 'date',
    11 => 'dom',
    12 => 'exif',
    13 => 'fileinfo',
    14 => 'filter',
    15 => 'gd',
    16 => 'hash',
    17 => 'iconv',
    18 => 'json',
    19 => 'libxml',
    20 => 'mbstring',
    21 => 'mysqlnd',
    22 => 'openssl',
    23 => 'pcntl',
    24 => 'pcre',
    25 => 'pdo_pgsql',
    26 => 'pdo_sqlite',
    27 => 'pgsql',
    28 => 'posix',
    29 => 'random',
    30 => 'readline',
    31 => 'redis',
    32 => 'session',
    33 => 'sodium',
    34 => 'sqlite3',
    35 => 'standard',
    36 => 'tokenizer',
    37 => 'xml',
    38 => 'xmlreader',
    39 => 'xmlwriter',
    40 => 'zip',
    41 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '6',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  '/var/www/html/app/Contracts/MessengerInterface.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Contracts\\MessengerInterface::buildButtons() has parameter $buttons with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Contracts/MessengerInterface.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Contracts/MessengerInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 34,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/DTOs/Message.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\DTOs\\Message::__construct() has parameter $variables with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/DTOs/Message.php',
       'line' => 16,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/DTOs/Message.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 16,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidAnswerException.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Exceptions\\InvalidAnswerException::forMultipleChoice() has parameter $validOptions with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Exceptions/InvalidAnswerException.php',
       'line' => 35,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Exceptions/InvalidAnswerException.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 35,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidWagerStateException.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Exceptions\\InvalidWagerStateException::__construct() has parameter $validStatuses with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
       'line' => 12,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 12,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Exceptions\\InvalidWagerStateException::getValidStatuses() return type has no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 34,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $userId of static method App\\Models\\OneTimeToken::generate() expects string|null, int given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 365,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 358,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method getChat() on string.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 411,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 411,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Relations\\Pivot::$points.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 476,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 476,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$pivot.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 491,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 491,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 492,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 492,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $userId of static method App\\Models\\OneTimeToken::generate() expects string|null, int given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 532,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 524,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 616,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 614,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 645,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 643,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 674,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 672,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 683,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 681,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 692,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 690,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Database\\Eloquent\\Model::users().',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 712,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 712,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method Illuminate\\Database\\Eloquent\\Model::users().',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 713,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 713,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 728,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 726,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|false> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 740,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 738,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 752,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 750,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $text of method TelegramBot\\Api\\BotApi::answerCallbackQuery() expects string|null, array<string, string|true> given.',
       'file' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'line' => 763,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 761,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Http/Controllers/DashboardController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 33,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 33,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 33,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 33,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 34,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 35,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 35,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$pivot.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$pivot.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 63,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 63,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 71,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 72,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 72,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Wager>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 82,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Wager>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 82,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 89,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 89,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 92,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 92,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 97,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 97,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 98,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 98,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Wager>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 103,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 103,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Wager>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 103,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 121,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 121,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 121,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 121,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 126,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 126,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 127,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 127,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$is_winner.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 128,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 128,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$payout_amount.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 129,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 129,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Transaction>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,App\\Models\\Transaction>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 145,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 152,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 152,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 152,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 152,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$title.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 153,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 153,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 184,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 184,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\DashboardController::updateProfile() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'line' => 208,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/DashboardController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 208,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Http/Controllers/ShortUrlController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\ShortUrlController::redirect() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/ShortUrlController.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/ShortUrlController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Http/Controllers/WagerController.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 60,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 61,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 61,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$platform_chat_title.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 61,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 61,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Left side of && is always true.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 70,
       'nodeType' => 'PHPStan\\Node\\BooleanAndNode',
       'identifier' => 'booleanAnd.leftAlwaysTrue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$pivot.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Support\\Collection<int,array<string, mixed>>::sortBy() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Support\\Collection<int,array<string, mixed>>::values() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Ternary operator condition is always true.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 84,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 84,
       'nodeType' => 'PhpParser\\Node\\Expr\\Ternary',
       'identifier' => 'ternary.alwaysTrue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::store() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::postWagerToTelegram() has parameter $group with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 192,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 192,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::postWagerToTelegram() has parameter $wager with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 192,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 192,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::success() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 210,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 210,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::success() has parameter $wagerId with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 210,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 210,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 220,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 220,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::showSettlementForm() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 228,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 228,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 244,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 244,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$title.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 245,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 245,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$description.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 246,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 246,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 247,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 247,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$deadline.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 248,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$stake_amount.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 249,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 249,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$total_points_wagered.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 250,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 250,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$participants_count.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 251,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 251,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$creator.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 253,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 253,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$creator.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 254,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 254,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$group.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 257,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 257,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$group.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 258,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 258,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$entries.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 260,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 260,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$group_id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 263,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 263,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$options.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 267,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 267,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 267,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 267,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::settle() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 275,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 275,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $wager of method App\\Services\\WagerService::settleWager() expects App\\Models\\Wager, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 293,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 292,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::settlementSuccess() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 310,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 310,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::settlementSuccess() has parameter $wagerId with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 310,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 310,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Array has 2 duplicate keys with value \'settled_at\' (\'settled_at\', \'settled_at\').',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 320,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 315,
       'nodeType' => 'PHPStan\\Node\\LiteralArrayNode',
       'identifier' => 'array.duplicateKey',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 320,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 320,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 322,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 322,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 323,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 323,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 327,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 327,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::postSettlementToTelegram() has parameter $wager with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 336,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 336,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::show() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 380,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 380,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::show() has parameter $wagerId with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 380,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 380,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 400,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 400,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method toIso8601String() on string.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 407,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 407,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 409,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 409,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 410,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 410,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    53 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 413,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 413,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    54 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 414,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 414,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    55 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 417,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 417,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    56 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$name.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 418,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 418,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    57 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $callback of method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 420,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 420,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.unresolvableType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    58 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Return type of call to method Illuminate\\Database\\Eloquent\\Collection<int,Illuminate\\Database\\Eloquent\\Model>::map() contains unresolvable type.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 420,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 420,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.unresolvableReturnType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    59 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 421,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 421,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    60 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$user.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 422,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 422,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    61 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$user.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 423,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 423,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    62 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 424,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 424,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    63 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 425,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 425,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    64 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$is_winner.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 426,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 426,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    65 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_won.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 427,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 427,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    66 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::settleFromShow() has no return type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 444,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 444,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    67 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Controllers\\WagerController::settleFromShow() has parameter $wagerId with no type specified.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 444,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 444,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    68 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #4 $settlerId of method App\\Services\\WagerService::settleWager() expects string|null, int given.',
       'file' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'line' => 472,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Controllers/WagerController.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 468,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Middleware\\AuthenticateFromSignedUrl::findUserByIdentifier() should return App\\Models\\User|null but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
       'line' => 124,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 124,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Http\\Middleware\\AuthenticateFromSignedUrl::findUserByIdentifier() should return App\\Models\\User|null but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 137,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/AuditLog.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::actor() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::auditable() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\MorphTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 49,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeAction() has no return type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 57,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeAction() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 57,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeByActor() has no return type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeByActor() has parameter $actorId with no type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeByActor() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeRecent() has no return type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 73,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\AuditLog::scopeRecent() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/AuditLog.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/AuditLog.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 73,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/Group.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\Group uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Group::users() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany does not specify its types: TRelatedModel, TDeclaringModel, TPivotModel, TAccessor (2-4 required)',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Group::wagers() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 58,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Group::wagerTemplates() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 63,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Group::transactions() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 68,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Group::getChatId() should return string but returns int.',
       'file' => '/var/www/html/app/Models/Group.php',
       'line' => 86,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Group.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 86,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/MessengerService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::user() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 31,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeTelegram() has no return type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeTelegram() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeDiscord() has no return type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeDiscord() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeSlack() has no return type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\MessengerService::scopeSlack() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/MessengerService.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/MessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/OneTimeToken.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\OneTimeToken uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/OneTimeToken.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/OneTimeToken.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\OneTimeToken::user() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/OneTimeToken.php',
       'line' => 35,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/OneTimeToken.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 35,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\OneTimeToken::generate() has parameter $context with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Models/OneTimeToken.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/OneTimeToken.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 43,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/ShortUrl.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\ShortUrl::scopeActive() has no return type specified.',
       'file' => '/var/www/html/app/Models/ShortUrl.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/ShortUrl.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\ShortUrl::scopeActive() has parameter $query with no type specified.',
       'file' => '/var/www/html/app/Models/ShortUrl.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/ShortUrl.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/Transaction.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\Transaction uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/Transaction.php',
       'line' => 14,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Transaction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 14,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Transaction::user() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Transaction.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Transaction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 39,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Transaction::group() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Transaction.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Transaction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 44,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Transaction::wager() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Transaction.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Transaction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 49,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Transaction::wagerEntry() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Transaction.php',
       'line' => 54,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Transaction.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 54,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/User.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\User uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 16,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 16,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::groups() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany does not specify its types: TRelatedModel, TDeclaringModel, TPivotModel, TAccessor (2-4 required)',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 40,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 40,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::wagers() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::wagerEntries() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::transactions() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::messengerServices() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 70,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\User::getMessengerService() should return App\\Models\\MessengerService|null but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/var/www/html/app/Models/User.php',
       'line' => 78,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/User.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 78,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/UserGroup.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\UserGroup::user() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/UserGroup.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/UserGroup.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\UserGroup::group() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/UserGroup.php',
       'line' => 48,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/UserGroup.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 48,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/Wager.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\Wager uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::group() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::creator() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::settler() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 70,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::entries() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 75,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 75,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::transactions() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 80,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::oneTimeTokens() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\HasMany does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 85,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 85,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\Wager::getDisplayOptions() return type has no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 125,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 125,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Match arm comparison between \'date\' and \'date\' is always true.',
       'file' => '/var/www/html/app/Models/Wager.php',
       'line' => 131,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/Wager.php',
       'traitFilePath' => NULL,
       'tip' => 'Remove remaining cases below this one and this error will disappear too.',
       'nodeLine' => 127,
       'nodeType' => 'PHPStan\\Node\\MatchExpressionNode',
       'identifier' => 'match.alwaysTrue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/WagerEntry.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\WagerEntry uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/WagerEntry.php',
       'line' => 14,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerEntry.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 14,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerEntry::wager() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerEntry.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerEntry.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerEntry::user() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerEntry.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerEntry.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 46,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerEntry::group() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerEntry.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerEntry.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$type.',
       'file' => '/var/www/html/app/Models/WagerEntry.php',
       'line' => 63,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerEntry.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 63,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/WagerSettlementToken.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerSettlementToken::wager() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerSettlementToken.php',
       'line' => 32,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerSettlementToken.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 32,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerSettlementToken::creator() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerSettlementToken.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerSettlementToken.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 37,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Models/WagerTemplate.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class App\\Models\\WagerTemplate uses generic trait Illuminate\\Database\\Eloquent\\Factories\\HasFactory but does not specify its types: TFactory',
       'file' => '/var/www/html/app/Models/WagerTemplate.php',
       'line' => 14,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerTemplate.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 14,
       'nodeType' => 'PhpParser\\Node\\Stmt\\TraitUse',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerTemplate::group() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerTemplate.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerTemplate.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Models\\WagerTemplate::creator() return type with generic class Illuminate\\Database\\Eloquent\\Relations\\BelongsTo does not specify its types: TRelatedModel, TDeclaringModel',
       'file' => '/var/www/html/app/Models/WagerTemplate.php',
       'line' => 41,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Models/WagerTemplate.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 41,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/AuditService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\AuditService::log() has parameter $metadata with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/AuditService.php',
       'line' => 24,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 24,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$id.',
       'file' => '/var/www/html/app/Services/AuditService.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\AuditService::logFromRequest() has parameter $metadata with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/AuditService.php',
       'line' => 48,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 48,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\AuditService::logSystem() has parameter $metadata with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/AuditService.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/AuditService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 67,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/MessageService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 34,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\MessageService::settlementResult() has parameter $winners with generic class Illuminate\\Support\\Collection but does not specify its types: TKey, TValue',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 59,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 59,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\MessageService::buildWagerButtons() return type has no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 163,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 163,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $value of function collect expects Illuminate\\Contracts\\Support\\Arrayable<(int|string), mixed>|iterable<(int|string), mixed>|null, string|null given.',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Unable to resolve the template type TKey in call to function collect',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-error-unable-to-resolve-template-type',
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.templateType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Unable to resolve the template type TValue in call to function collect',
       'file' => '/var/www/html/app/Services/MessageService.php',
       'line' => 178,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/MessageService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-error-unable-to-resolve-template-type',
       'nodeLine' => 178,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.templateType',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/Messengers/TelegramMessenger.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset 0 on non-empty-list<string> in isset() always exists and is not nullable.',
       'file' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 65,
       'nodeType' => 'PhpParser\\Node\\Expr\\Isset_',
       'identifier' => 'isset.offset',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\Messengers\\TelegramMessenger::buildButtons() has parameter $buttons with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Match expression does not handle remaining value: mixed',
       'file' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 94,
       'nodeType' => 'PHPStan\\Node\\MatchExpressionNode',
       'identifier' => 'match.unhandled',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/PointService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe property access "?->points" on left side of ?? is unnecessary. Use -> instead.',
       'file' => '/var/www/html/app/Services/PointService.php',
       'line' => 22,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/PointService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 22,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Relations\\Pivot::$created_at.',
       'file' => '/var/www/html/app/Services/PointService.php',
       'line' => 199,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/PointService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 199,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Relations\\Pivot::$points.',
       'file' => '/var/www/html/app/Services/PointService.php',
       'line' => 206,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/PointService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 206,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/TokenService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $userId of static method App\\Models\\OneTimeToken::generate() expects string|null, int given.',
       'file' => '/var/www/html/app/Services/TokenService.php',
       'line' => 38,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/TokenService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 31,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $userId of static method App\\Models\\OneTimeToken::generate() expects string|null, int given.',
       'file' => '/var/www/html/app/Services/TokenService.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/TokenService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 51,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\TokenService::getActiveTokensForWager() return type with generic class Illuminate\\Database\\Eloquent\\Collection does not specify its types: TKey, TModel',
       'file' => '/var/www/html/app/Services/TokenService.php',
       'line' => 95,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/TokenService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 95,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/UserMessengerService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\UserMessengerService::findOrCreate() has parameter $userData with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/UserMessengerService.php',
       'line' => 25,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/UserMessengerService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 25,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\UserMessengerService::findOrCreate() should return App\\Models\\User but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/var/www/html/app/Services/UserMessengerService.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/UserMessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 30,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\UserMessengerService::generateUserName() has parameter $userData with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/UserMessengerService.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/UserMessengerService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 73,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\UserMessengerService::findByPlatform() should return App\\Models\\User|null but returns Illuminate\\Database\\Eloquent\\Model|null.',
       'file' => '/var/www/html/app/Services/UserMessengerService.php',
       'line' => 97,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/UserMessengerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 97,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
  '/var/www/html/app/Services/WagerService.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\WagerService::createWager() has parameter $data with no value type specified in iterable type array.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 28,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Match expression does not handle remaining value: mixed',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 47,
       'nodeType' => 'PHPStan\\Node\\MatchExpressionNode',
       'identifier' => 'match.unhandled',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $group of method App\\Services\\PointService::deductPoints() expects App\\Models\\Group, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 113,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 113,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $haystack of function in_array expects array, string|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 166,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 166,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $validOptions of static method App\\Exceptions\\InvalidAnswerException::forMultipleChoice() expects array, string|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 167,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 167,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 195,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 195,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 196,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 203,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe method call on non-nullable type string. Use -> instead.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 203,
       'nodeType' => 'PhpParser\\Node\\Expr\\NullsafeMethodCall',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 204,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 204,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 211,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 211,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Cannot call method format() on string.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 212,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 212,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.nonObject',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Using nullsafe method call on non-nullable type string. Use -> instead.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 212,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 212,
       'nodeType' => 'PhpParser\\Node\\Expr\\NullsafeMethodCall',
       'identifier' => 'nullsafe.neverNull',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\WagerService::settleCategoricalWager() has parameter $entries with generic class Illuminate\\Database\\Eloquent\\Collection but does not specify its types: TKey, TModel',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 294,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 294,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::refundEntry() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 302,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 302,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 311,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 311,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::awardWinner() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 312,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 312,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::recordLoss() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 316,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 316,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\WagerService::settleNumericWager() has parameter $entries with generic class Illuminate\\Database\\Eloquent\\Collection but does not specify its types: TKey, TModel',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 323,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 323,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 327,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 327,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::refundEntry() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 343,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 343,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 352,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 352,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::awardWinner() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 353,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 353,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::recordLoss() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 358,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 358,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method App\\Services\\WagerService::settleDateWager() has parameter $entries with generic class Illuminate\\Database\\Eloquent\\Collection but does not specify its types: TKey, TModel',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 365,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 365,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$answer_value.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 371,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 371,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::refundEntry() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 388,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 388,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property Illuminate\\Database\\Eloquent\\Model::$points_wagered.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 397,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 397,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::awardWinner() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 398,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 398,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::recordLoss() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 403,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 403,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Services\\PointService::awardPoints() expects App\\Models\\User, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 416,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 415,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $group of method App\\Services\\PointService::awardPoints() expects App\\Models\\Group, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 417,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 415,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #5 $wager of method App\\Services\\PointService::awardPoints() expects App\\Models\\Wager|null, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 420,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 415,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Services\\PointService::recordLoss() expects App\\Models\\User, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 433,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 432,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $group of method App\\Services\\PointService::recordLoss() expects App\\Models\\Group, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 434,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 432,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #4 $wager of method App\\Services\\PointService::recordLoss() expects App\\Models\\Wager, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 436,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 432,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $user of method App\\Services\\PointService::refundPoints() expects App\\Models\\User, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 446,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 445,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $group of method App\\Services\\PointService::refundPoints() expects App\\Models\\Group, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 447,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 445,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #4 $wager of method App\\Services\\PointService::refundPoints() expects App\\Models\\Wager, Illuminate\\Database\\Eloquent\\Model|null given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 449,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 445,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $entry of method App\\Services\\WagerService::refundEntry() expects App\\Models\\WagerEntry, Illuminate\\Database\\Eloquent\\Model given.',
       'file' => '/var/www/html/app/Services/WagerService.php',
       'line' => 468,
       'canBeIgnored' => true,
       'filePath' => '/var/www/html/app/Services/WagerService.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 468,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
  '/var/www/html/app/Console/Commands/SendSettlementReminders.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 68,
      ),
    ),
  ),
  '/var/www/html/app/DTOs/Button.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\DTOs\\Button',
    ),
  ),
  '/var/www/html/app/DTOs/Message.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\DTOs\\Message',
    ),
  ),
  '/var/www/html/app/Exceptions/BeatWagerException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\BeatWagerException',
        1 => 'getStatusCode',
        2 => 'App\\Exceptions\\BeatWagerException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\BeatWagerException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\BeatWagerException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/InsufficientPointsException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\InsufficientPointsException',
        1 => 'getRequired',
        2 => 'App\\Exceptions\\InsufficientPointsException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\InsufficientPointsException',
        1 => 'getAvailable',
        2 => 'App\\Exceptions\\InsufficientPointsException',
      ),
      2 => 
      array (
        0 => 'App\\Exceptions\\InsufficientPointsException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\InsufficientPointsException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidAnswerException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\InvalidAnswerException',
        1 => 'getWagerType',
        2 => 'App\\Exceptions\\InvalidAnswerException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\InvalidAnswerException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\InvalidAnswerException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidStakeException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\InvalidStakeException',
        1 => 'getProvided',
        2 => 'App\\Exceptions\\InvalidStakeException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\InvalidStakeException',
        1 => 'getRequired',
        2 => 'App\\Exceptions\\InvalidStakeException',
      ),
      2 => 
      array (
        0 => 'App\\Exceptions\\InvalidStakeException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\InvalidStakeException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidTokenException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\InvalidTokenException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\InvalidTokenException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\InvalidTokenException',
        1 => 'getStatusCode',
        2 => 'App\\Exceptions\\InvalidTokenException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidWagerStateException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\InvalidWagerStateException',
        1 => 'getWager',
        2 => 'App\\Exceptions\\InvalidWagerStateException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\InvalidWagerStateException',
        1 => 'getAttemptedAction',
        2 => 'App\\Exceptions\\InvalidWagerStateException',
      ),
      2 => 
      array (
        0 => 'App\\Exceptions\\InvalidWagerStateException',
        1 => 'getValidStatuses',
        2 => 'App\\Exceptions\\InvalidWagerStateException',
      ),
      3 => 
      array (
        0 => 'App\\Exceptions\\InvalidWagerStateException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\InvalidWagerStateException',
      ),
      4 => 
      array (
        0 => 'App\\Exceptions\\InvalidWagerStateException',
        1 => 'getStatusCode',
        2 => 'App\\Exceptions\\InvalidWagerStateException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\UserAlreadyJoinedException',
        1 => 'getUser',
        2 => 'App\\Exceptions\\UserAlreadyJoinedException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\UserAlreadyJoinedException',
        1 => 'getWager',
        2 => 'App\\Exceptions\\UserAlreadyJoinedException',
      ),
      2 => 
      array (
        0 => 'App\\Exceptions\\UserAlreadyJoinedException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\UserAlreadyJoinedException',
      ),
      3 => 
      array (
        0 => 'App\\Exceptions\\UserAlreadyJoinedException',
        1 => 'getStatusCode',
        2 => 'App\\Exceptions\\UserAlreadyJoinedException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/WagerAlreadySettledException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\WagerAlreadySettledException',
        1 => 'getWager',
        2 => 'App\\Exceptions\\WagerAlreadySettledException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\WagerAlreadySettledException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\WagerAlreadySettledException',
      ),
      2 => 
      array (
        0 => 'App\\Exceptions\\WagerAlreadySettledException',
        1 => 'getStatusCode',
        2 => 'App\\Exceptions\\WagerAlreadySettledException',
      ),
    ),
  ),
  '/var/www/html/app/Exceptions/WagerNotOpenException.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Exceptions\\WagerNotOpenException',
        1 => 'getWager',
        2 => 'App\\Exceptions\\WagerNotOpenException',
      ),
      1 => 
      array (
        0 => 'App\\Exceptions\\WagerNotOpenException',
        1 => 'getUserMessage',
        2 => 'App\\Exceptions\\WagerNotOpenException',
      ),
    ),
  ),
  '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 194,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 371,
      ),
      2 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 538,
      ),
    ),
  ),
  '/var/www/html/app/Http/Controllers/ShortUrlController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 20,
      ),
    ),
  ),
  '/var/www/html/app/Http/Controllers/WagerController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Http\\Controllers\\WagerController',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'abort',
        1 => 31,
      ),
      1 => 
      array (
        0 => 'abort',
        1 => 39,
      ),
      2 => 
      array (
        0 => 'abort',
        1 => 43,
      ),
      3 => 
      array (
        0 => 'abort',
        1 => 119,
      ),
      4 => 
      array (
        0 => 'abort',
        1 => 235,
      ),
      5 => 
      array (
        0 => 'abort',
        1 => 286,
      ),
      6 => 
      array (
        0 => 'abort',
        1 => 457,
      ),
      7 => 
      array (
        0 => 'abort',
        1 => 461,
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureMethodCallCollector' => 
    array (
      0 => 
      array (
        0 => 
        array (
          0 => 'TelegramBot\\Api\\BotApi',
        ),
        1 => 'sendMessage',
        2 => 362,
      ),
    ),
  ),
  '/var/www/html/app/Models/AuditLog.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\AuditLog',
        1 => 'casts',
        2 => 'App\\Models\\AuditLog',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/Group.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\Group',
        1 => 'casts',
        2 => 'App\\Models\\Group',
      ),
      1 => 
      array (
        0 => 'App\\Models\\Group',
        1 => 'getChatId',
        2 => 'App\\Models\\Group',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/MessengerService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\MessengerService',
        1 => 'casts',
        2 => 'App\\Models\\MessengerService',
      ),
      1 => 
      array (
        0 => 'App\\Models\\MessengerService',
        1 => 'getDisplayNameAttribute',
        2 => 'App\\Models\\MessengerService',
      ),
    ),
  ),
  '/var/www/html/app/Models/OneTimeToken.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\OneTimeToken',
        1 => 'casts',
        2 => 'App\\Models\\OneTimeToken',
      ),
      1 => 
      array (
        0 => 'App\\Models\\OneTimeToken',
        1 => 'isUsed',
        2 => 'App\\Models\\OneTimeToken',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/Transaction.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\Transaction',
        1 => 'casts',
        2 => 'App\\Models\\Transaction',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/User.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\User',
        1 => 'casts',
        2 => 'App\\Models\\User',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
        2 => 'Illuminate\\Notifications\\Notifiable',
      ),
    ),
  ),
  '/var/www/html/app/Models/UserGroup.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\UserGroup',
        1 => 'casts',
        2 => 'App\\Models\\UserGroup',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/Wager.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'casts',
        2 => 'App\\Models\\Wager',
      ),
      1 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'isBinary',
        2 => 'App\\Models\\Wager',
      ),
      2 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'isMultipleChoice',
        2 => 'App\\Models\\Wager',
      ),
      3 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'isNumeric',
        2 => 'App\\Models\\Wager',
      ),
      4 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'isDate',
        2 => 'App\\Models\\Wager',
      ),
      5 => 
      array (
        0 => 'App\\Models\\Wager',
        1 => 'getDisplayOptions',
        2 => 'App\\Models\\Wager',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/WagerEntry.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\WagerEntry',
        1 => 'casts',
        2 => 'App\\Models\\WagerEntry',
      ),
      1 => 
      array (
        0 => 'App\\Models\\WagerEntry',
        1 => 'getFormattedAnswer',
        2 => 'App\\Models\\WagerEntry',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/WagerSettlementToken.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\WagerSettlementToken',
        1 => 'casts',
        2 => 'App\\Models\\WagerSettlementToken',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Models/WagerTemplate.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Models\\WagerTemplate',
        1 => 'casts',
        2 => 'App\\Models\\WagerTemplate',
      ),
    ),
    'PHPStan\\Rules\\Traits\\TraitUseCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
    ),
  ),
  '/var/www/html/app/Services/MessageService.php' => 
  array (
    'Larastan\\Larastan\\Collectors\\UsedTranslationFunctionCollector' => 
    array (
      0 => 
      array (
        0 => 'messages.wager.announced',
        1 => 27,
      ),
      1 => 
      array (
        0 => 'messages.buttons.view_progress',
        1 => 42,
      ),
      2 => 
      array (
        0 => 'messages.wager.settled',
        1 => 61,
      ),
      3 => 
      array (
        0 => 'messages.winners.header',
        1 => 70,
      ),
      4 => 
      array (
        0 => 'messages.winners.single',
        1 => 76,
      ),
      5 => 
      array (
        0 => 'messages.winners.none',
        1 => 80,
      ),
      6 => 
      array (
        0 => 'messages.wager.reminder',
        1 => 98,
      ),
      7 => 
      array (
        0 => 'messages.buttons.settle_wager',
        1 => 106,
      ),
      8 => 
      array (
        0 => 'messages.progress.dm_title',
        1 => 126,
      ),
      9 => 
      array (
        0 => 'messages.progress.dm_body',
        1 => 126,
      ),
      10 => 
      array (
        0 => 'messages.buttons.open_wager_page',
        1 => 134,
      ),
      11 => 
      array (
        0 => 'messages.wager.joined',
        1 => 155,
      ),
      12 => 
      array (
        0 => 'messages.buttons.yes',
        1 => 168,
      ),
      13 => 
      array (
        0 => 'messages.buttons.no',
        1 => 173,
      ),
    ),
  ),
  '/var/www/html/app/Services/PointService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Connection',
        1 => 'transaction',
        2 => 162,
      ),
      1 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 173,
      ),
    ),
  ),
  '/var/www/html/app/Services/UserMessengerService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'create',
        2 => 54,
      ),
    ),
  ),
  '/var/www/html/app/Services/WagerService.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'App\\Services\\WagerService',
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'App\\Services\\AuditService',
        1 => 'log',
        2 => 69,
      ),
      1 => 
      array (
        0 => 'App\\Services\\AuditService',
        1 => 'log',
        2 => 129,
      ),
      2 => 
      array (
        0 => 'App\\Services\\AuditService',
        1 => 'log',
        2 => 275,
      ),
    ),
  ),
  '/var/www/html/routes/web.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'Illuminate\\Support\\Facades\\Route',
        1 => 'get',
        2 => 9,
      ),
    ),
  ),
); },
	'dependencies' => array (
  '/var/www/html/app/Console/Commands/SendSettlementReminders.php' => 
  array (
    'fileHash' => 'bcc4f4bee00c35a4b2c12baab687d0c803a023df',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Console/Commands/Telegram/SetWebhook.php' => 
  array (
    'fileHash' => 'c98449310d0be84d420ad69f8473386b570c8675',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Console/Commands/Telegram/WebhookInfo.php' => 
  array (
    'fileHash' => '14157d435bf778eb555bf100b5886ee7fc621d32',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Contracts/MessengerInterface.php' => 
  array (
    'fileHash' => '9ccebdb74f43d0feb232f08fbf9151283c0af1d2',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Models/Group.php',
      2 => '/var/www/html/app/Services/MessengerFactory.php',
      3 => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
    ),
  ),
  '/var/www/html/app/DTOs/Button.php' => 
  array (
    'fileHash' => '218cb8c5614ceab22cc304e92d975ebbe926f969',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/DTOs/Message.php',
      1 => '/var/www/html/app/Services/MessageService.php',
      2 => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
    ),
  ),
  '/var/www/html/app/DTOs/Message.php' => 
  array (
    'fileHash' => 'ad728390e73924109605c2da0e6c1283ecd1b4f7',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Contracts/MessengerInterface.php',
      2 => '/var/www/html/app/Http/Controllers/WagerController.php',
      3 => '/var/www/html/app/Models/Group.php',
      4 => '/var/www/html/app/Services/MessageService.php',
      5 => '/var/www/html/app/Services/Messengers/TelegramMessenger.php',
    ),
  ),
  '/var/www/html/app/DTOs/MessageType.php' => 
  array (
    'fileHash' => '182f832b45e33401ffc3dbbc4f6333d50450625e',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/DTOs/Message.php',
      1 => '/var/www/html/app/Services/MessageService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/BeatWagerException.php' => 
  array (
    'fileHash' => '71d5d79dc930f4dcd314a3b67120f32b1822669d',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Exceptions/InsufficientPointsException.php',
      1 => '/var/www/html/app/Exceptions/InvalidAnswerException.php',
      2 => '/var/www/html/app/Exceptions/InvalidStakeException.php',
      3 => '/var/www/html/app/Exceptions/InvalidTokenException.php',
      4 => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
      5 => '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php',
      6 => '/var/www/html/app/Exceptions/WagerAlreadySettledException.php',
      7 => '/var/www/html/app/Exceptions/WagerNotOpenException.php',
      8 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      9 => '/var/www/html/app/Services/PointService.php',
      10 => '/var/www/html/app/Services/TokenService.php',
      11 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/InsufficientPointsException.php' => 
  array (
    'fileHash' => 'da4abd0cf8b1632c504f28138f67ef93847eff13',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Services/PointService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidAnswerException.php' => 
  array (
    'fileHash' => 'f85c1727b2d2687707ec62492ba5c890ece404ef',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/TokenService.php',
      1 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidStakeException.php' => 
  array (
    'fileHash' => '155da986cdc7a0f000f77112c0b057f5a89a2d82',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidTokenException.php' => 
  array (
    'fileHash' => '0642e4d54f7f91d73229bb2e117eca2520d030a9',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/TokenService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/InvalidWagerStateException.php' => 
  array (
    'fileHash' => 'db76590ba0bc063e18983cbb0c39298b4f28d9f4',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php' => 
  array (
    'fileHash' => 'a0d70f057b497d98dad08e520804614cbc7636c0',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Exceptions/WagerAlreadySettledException.php' => 
  array (
    'fileHash' => 'a167a684cfe56a1a4487b042a9c13a41e166e98e',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Exceptions/WagerNotOpenException.php' => 
  array (
    'fileHash' => '19a581231950a943e0184083f7af9916201e2340',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php' => 
  array (
    'fileHash' => '7712b935238f8d40f51e30bfe4046d6cbc4fc7ee',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/routes/api.php',
    ),
  ),
  '/var/www/html/app/Http/Controllers/Controller.php' => 
  array (
    'fileHash' => '75cadca8afa5982965d1ac316df3c693271b4902',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/DashboardController.php',
      2 => '/var/www/html/app/Http/Controllers/ShortUrlController.php',
      3 => '/var/www/html/app/Http/Controllers/WagerController.php',
      4 => '/var/www/html/routes/api.php',
      5 => '/var/www/html/routes/web.php',
    ),
  ),
  '/var/www/html/app/Http/Controllers/DashboardController.php' => 
  array (
    'fileHash' => 'ba45b7083c6b4c4ad530e897655894555b8f8962',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/routes/web.php',
    ),
  ),
  '/var/www/html/app/Http/Controllers/ShortUrlController.php' => 
  array (
    'fileHash' => 'ae97efe2bfd3328b9b884d3f86a03eac75ce1f89',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/routes/web.php',
    ),
  ),
  '/var/www/html/app/Http/Controllers/WagerController.php' => 
  array (
    'fileHash' => 'ead27232a74bf75f8a6fa4ba968efd92f01ded07',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/routes/web.php',
    ),
  ),
  '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php' => 
  array (
    'fileHash' => 'b763917b507b475925cb42dfe9486da5682288a4',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    'fileHash' => 'c10d6f9b0bd0ba0d04d3742d95b82bea776de840',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Models/AuditLog.php' => 
  array (
    'fileHash' => 'bf15226777deb315c73061a8b939302729f70793',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/AuditService.php',
      1 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Models/Group.php' => 
  array (
    'fileHash' => 'd3781a9c67101f884b35513c9ff7edee72f25aa7',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/WagerController.php',
      2 => '/var/www/html/app/Models/Transaction.php',
      3 => '/var/www/html/app/Models/User.php',
      4 => '/var/www/html/app/Models/UserGroup.php',
      5 => '/var/www/html/app/Models/Wager.php',
      6 => '/var/www/html/app/Models/WagerEntry.php',
      7 => '/var/www/html/app/Models/WagerTemplate.php',
      8 => '/var/www/html/app/Services/MessengerFactory.php',
      9 => '/var/www/html/app/Services/PointService.php',
      10 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Models/MessengerService.php' => 
  array (
    'fileHash' => 'fdcd415d03b982105f4d7c99582edb98ececfbdd',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Http/Controllers/DashboardController.php',
      2 => '/var/www/html/app/Http/Controllers/WagerController.php',
      3 => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
      4 => '/var/www/html/app/Models/User.php',
      5 => '/var/www/html/app/Services/UserMessengerService.php',
    ),
  ),
  '/var/www/html/app/Models/OneTimeToken.php' => 
  array (
    'fileHash' => '6845515c04c4ddc80a510bb7218b3951bf160bad',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/WagerController.php',
      2 => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
      3 => '/var/www/html/app/Models/Wager.php',
      4 => '/var/www/html/app/Services/TokenService.php',
    ),
  ),
  '/var/www/html/app/Models/ShortUrl.php' => 
  array (
    'fileHash' => '0c3a77039ab65a093129d20400cd9d8893ef4777',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      2 => '/var/www/html/app/Http/Controllers/ShortUrlController.php',
    ),
  ),
  '/var/www/html/app/Models/Transaction.php' => 
  array (
    'fileHash' => 'ac437540d2e18c6cd004f8ff8b3ac98af285f1fb',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/DashboardController.php',
      2 => '/var/www/html/app/Models/Group.php',
      3 => '/var/www/html/app/Models/User.php',
      4 => '/var/www/html/app/Models/Wager.php',
      5 => '/var/www/html/app/Services/PointService.php',
      6 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Models/User.php' => 
  array (
    'fileHash' => 'd56f42abae66ac0e8a2dc1ebd8cac6dbfe4f0f6a',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php',
      2 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      3 => '/var/www/html/app/Http/Controllers/DashboardController.php',
      4 => '/var/www/html/app/Http/Controllers/WagerController.php',
      5 => '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php',
      6 => '/var/www/html/app/Models/AuditLog.php',
      7 => '/var/www/html/app/Models/Group.php',
      8 => '/var/www/html/app/Models/MessengerService.php',
      9 => '/var/www/html/app/Models/OneTimeToken.php',
      10 => '/var/www/html/app/Models/Transaction.php',
      11 => '/var/www/html/app/Models/UserGroup.php',
      12 => '/var/www/html/app/Models/Wager.php',
      13 => '/var/www/html/app/Models/WagerEntry.php',
      14 => '/var/www/html/app/Models/WagerSettlementToken.php',
      15 => '/var/www/html/app/Models/WagerTemplate.php',
      16 => '/var/www/html/app/Services/AuditService.php',
      17 => '/var/www/html/app/Services/PointService.php',
      18 => '/var/www/html/app/Services/TokenService.php',
      19 => '/var/www/html/app/Services/UserMessengerService.php',
      20 => '/var/www/html/app/Services/WagerService.php',
      21 => '/var/www/html/config/auth.php',
    ),
  ),
  '/var/www/html/app/Models/UserGroup.php' => 
  array (
    'fileHash' => '89d94d5cea726600681e0759a39352b0a6525956',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Models/Group.php',
      1 => '/var/www/html/app/Models/User.php',
    ),
  ),
  '/var/www/html/app/Models/Wager.php' => 
  array (
    'fileHash' => '858eb1e97c2076c4e3b745cc8b50160d4dfeaea9',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Exceptions/InvalidWagerStateException.php',
      2 => '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php',
      3 => '/var/www/html/app/Exceptions/WagerAlreadySettledException.php',
      4 => '/var/www/html/app/Exceptions/WagerNotOpenException.php',
      5 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      6 => '/var/www/html/app/Http/Controllers/DashboardController.php',
      7 => '/var/www/html/app/Http/Controllers/WagerController.php',
      8 => '/var/www/html/app/Models/Group.php',
      9 => '/var/www/html/app/Models/Transaction.php',
      10 => '/var/www/html/app/Models/User.php',
      11 => '/var/www/html/app/Models/WagerEntry.php',
      12 => '/var/www/html/app/Models/WagerSettlementToken.php',
      13 => '/var/www/html/app/Services/MessageService.php',
      14 => '/var/www/html/app/Services/PointService.php',
      15 => '/var/www/html/app/Services/TokenService.php',
      16 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Models/WagerEntry.php' => 
  array (
    'fileHash' => '8e80f8d2f0b4a22fa5cf5189e73d8a02bbb4abc6',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Models/Transaction.php',
      2 => '/var/www/html/app/Models/User.php',
      3 => '/var/www/html/app/Models/Wager.php',
      4 => '/var/www/html/app/Services/PointService.php',
      5 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Models/WagerSettlementToken.php' => 
  array (
    'fileHash' => '1b731e0d724a107bc5a0a5a3d5e22124c21637db',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/WagerController.php',
    ),
  ),
  '/var/www/html/app/Models/WagerTemplate.php' => 
  array (
    'fileHash' => '9cfc40c926712e0b86d234ada5e07ce554836b38',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Models/Group.php',
    ),
  ),
  '/var/www/html/app/Providers/AppServiceProvider.php' => 
  array (
    'fileHash' => '5198c332f4523c8baad66f18b5fcfb4c9457b898',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Providers/HttpsServiceProvider.php' => 
  array (
    'fileHash' => '00c54ec9ab2a6a301233722ed0c9b7af6f4ef18d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Services/AuditService.php' => 
  array (
    'fileHash' => 'df8c1814585cf42e31c8172a7dd063c7567b25cb',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Services/MessageService.php' => 
  array (
    'fileHash' => '9c8d569f21049229fe0509e561789458aa6f4048',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Http/Controllers/WagerController.php',
    ),
  ),
  '/var/www/html/app/Services/MessengerFactory.php' => 
  array (
    'fileHash' => 'cd4b3a377c5434d0875ccb2a484df16beb11cffd',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Models/Group.php',
    ),
  ),
  '/var/www/html/app/Services/Messengers/TelegramMessenger.php' => 
  array (
    'fileHash' => 'dcad92a089931da011c421f95afd8695d1cdfb55',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Console/Commands/SendSettlementReminders.php',
      1 => '/var/www/html/app/Services/MessengerFactory.php',
    ),
  ),
  '/var/www/html/app/Services/PointService.php' => 
  array (
    'fileHash' => 'a3d9d2ef349af6b9155547f11b5eabbbe6335f79',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Services/WagerService.php',
    ),
  ),
  '/var/www/html/app/Services/TokenService.php' => 
  array (
    'fileHash' => 'aec6f3a8af7a23e88be951a53ed2fd4a19df6044',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/app/Services/UserMessengerService.php' => 
  array (
    'fileHash' => 'e88979c4d2ac41c985021d161a6b96d9f0f53245',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/WagerController.php',
    ),
  ),
  '/var/www/html/app/Services/WagerService.php' => 
  array (
    'fileHash' => 'dd034de8d5fc28f42aaaef6d37b2d74821522fff',
    'dependentFiles' => 
    array (
      0 => '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php',
      1 => '/var/www/html/app/Http/Controllers/WagerController.php',
    ),
  ),
  '/var/www/html/config/app.php' => 
  array (
    'fileHash' => '248b42420be2f4010a1597761cd348f374a5acd0',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/auth.php' => 
  array (
    'fileHash' => 'd14c6ca41850324dcf3bde4b8c4fe4635d21b02e',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/cache.php' => 
  array (
    'fileHash' => '740a310b2e153d013bba8733eef5a96d9ab38024',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/database.php' => 
  array (
    'fileHash' => '4d7bb78ce43539e75ede1418a37fd5b9cecbb718',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/filesystems.php' => 
  array (
    'fileHash' => '6e1e66753542ecbccfe730cfee0d623723be2986',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/logging.php' => 
  array (
    'fileHash' => 'f163e17e3d43b2aa18f20994b2d26c2ccabd5abc',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/mail.php' => 
  array (
    'fileHash' => '55990e37cb337eee513173e5c48479cbb1e5202e',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/queue.php' => 
  array (
    'fileHash' => '258c42a365b1b4bee36b69053966a3fd836a9394',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/services.php' => 
  array (
    'fileHash' => 'e5d2f1a1f6f4d2ebf16e796ab0ac542c572f43bf',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/session.php' => 
  array (
    'fileHash' => 'a0ce1b173c09908a3d698b26372566c604844a94',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/config/telegram.php' => 
  array (
    'fileHash' => 'dd0f8541661bce902781fec7023567ed31f157fe',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/routes/api.php' => 
  array (
    'fileHash' => '3ff2821934493e23c9de6c609d7c76f48260cdbd',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/routes/console.php' => 
  array (
    'fileHash' => '529fd80a9fd20c84c433f09cf0d35963c72e52a6',
    'dependentFiles' => 
    array (
    ),
  ),
  '/var/www/html/routes/web.php' => 
  array (
    'fileHash' => 'f4bacf231ac7751e058ff4580478cb67be84d895',
    'dependentFiles' => 
    array (
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  '/var/www/html/app/Console/Commands/SendSettlementReminders.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Console\\Commands\\SendSettlementReminders',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Console\\Command',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'signature',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The name and signature of the console command.
     */',
             'namespace' => 'App\\Console\\Commands',
             'uses' => 
            array (
              'shorturl' => 'App\\Models\\ShortUrl',
              'wager' => 'App\\Models\\Wager',
              'messageservice' => 'App\\Services\\MessageService',
              'telegrammessenger' => 'App\\Services\\Messengers\\TelegramMessenger',
              'command' => 'Illuminate\\Console\\Command',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'description',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The console command description.
     */',
             'namespace' => 'App\\Console\\Commands',
             'uses' => 
            array (
              'shorturl' => 'App\\Models\\ShortUrl',
              'wager' => 'App\\Models\\Wager',
              'messageservice' => 'App\\Services\\MessageService',
              'telegrammessenger' => 'App\\Services\\Messengers\\TelegramMessenger',
              'command' => 'Illuminate\\Console\\Command',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Execute the console command.
     */',
             'namespace' => 'App\\Console\\Commands',
             'uses' => 
            array (
              'shorturl' => 'App\\Models\\ShortUrl',
              'wager' => 'App\\Models\\Wager',
              'messageservice' => 'App\\Services\\MessageService',
              'telegrammessenger' => 'App\\Services\\Messengers\\TelegramMessenger',
              'command' => 'Illuminate\\Console\\Command',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'messageService',
               'type' => 'App\\Services\\MessageService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'messenger',
               'type' => 'App\\Services\\Messengers\\TelegramMessenger',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Console/Commands/Telegram/SetWebhook.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Console\\Commands\\Telegram\\SetWebhook',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Console\\Command',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'signature',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'description',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Console/Commands/Telegram/WebhookInfo.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Console\\Commands\\Telegram\\WebhookInfo',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Console\\Command',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'signature',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'description',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Contracts/MessengerInterface.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::__set_state(array(
       'name' => 'App\\Contracts\\MessengerInterface',
       'phpDoc' => NULL,
       'extends' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'send',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send a message to a chat/channel
     *
     * @param Message $message The platform-agnostic message
     * @param string $chatId Platform-specific chat identifier
     * @return void
     */',
             'namespace' => 'App\\Contracts',
             'uses' => 
            array (
              'message' => 'App\\DTOs\\Message',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'App\\DTOs\\Message',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'chatId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'formatMessage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Format message content for the specific platform
     *
     * @param Message $message The platform-agnostic message
     * @return string Formatted message (HTML, Markdown, etc.)
     */',
             'namespace' => 'App\\Contracts',
             'uses' => 
            array (
              'message' => 'App\\DTOs\\Message',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'App\\DTOs\\Message',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'buildButtons',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Build platform-specific buttons/keyboard
     *
     * @param array $buttons Array of Button DTOs
     * @return mixed Platform-specific keyboard object
     */',
             'namespace' => 'App\\Contracts',
             'uses' => 
            array (
              'message' => 'App\\DTOs\\Message',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'mixed',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'buttons',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
    )),
  ),
  '/var/www/html/app/DTOs/Button.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\DTOs\\Button',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'label',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'App\\DTOs\\ButtonAction',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'style',
               'type' => 'App\\DTOs\\ButtonStyle',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'App\\DTOs\\ButtonAction',
       'scalarType' => 'string',
       'phpDoc' => NULL,
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Callback',
           'value' => '\'callback\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Url',
           'value' => '\'url\'',
           'phpDoc' => NULL,
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'App\\DTOs\\ButtonStyle',
       'scalarType' => 'string',
       'phpDoc' => NULL,
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Default',
           'value' => '\'default\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Primary',
           'value' => '\'primary\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Danger',
           'value' => '\'danger\'',
           'phpDoc' => NULL,
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/DTOs/Message.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\DTOs\\Message',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param string $content Plain text content with {placeholders}
     * @param MessageType $type Type of message
     * @param array $variables Key-value pairs for placeholder replacement
     * @param Button[] $buttons Array of generic button definitions
     * @param object|null $context Additional context (Wager, User, etc.)
     */',
             'namespace' => 'App\\DTOs',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'type',
               'type' => 'App\\DTOs\\MessageType',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'variables',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'buttons',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'context',
               'type' => '?object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getFormattedContent',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Replace placeholders in content with variables
     */',
             'namespace' => 'App\\DTOs',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/DTOs/MessageType.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'App\\DTOs\\MessageType',
       'scalarType' => 'string',
       'phpDoc' => NULL,
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Announcement',
           'value' => '\'announcement\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Confirmation',
           'value' => '\'confirmation\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Reminder',
           'value' => '\'reminder\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Error',
           'value' => '\'error\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Result',
           'value' => '\'result\'',
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'Info',
           'value' => '\'info\'',
           'phpDoc' => NULL,
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/BeatWagerException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\BeatWagerException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Base exception for all BeatWager application exceptions
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
          'exception' => 'Exception',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => true,
       'final' => false,
       'extends' => 'Exception',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusCode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get HTTP status code for this exception
     */',
             'namespace' => 'App\\Exceptions',
             'uses' => 
            array (
              'exception' => 'Exception',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get user-friendly error message
     */',
             'namespace' => 'App\\Exceptions',
             'uses' => 
            array (
              'exception' => 'Exception',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/InsufficientPointsException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\InsufficientPointsException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when a user attempts to wager more points than they have available
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'required',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'available',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getRequired',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAvailable',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidAnswerException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\InvalidAnswerException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when an answer doesn\'t match the wager type requirements
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerType',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWagerType',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'forBinary',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provided',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'forMultipleChoice',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provided',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validOptions',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'forNumeric',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provided',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'min',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'max',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'forDate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provided',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'min',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'max',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidStakeException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\InvalidStakeException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when stake amount doesn\'t match wager requirements
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'provided',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'required',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getProvided',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getRequired',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidTokenException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\InvalidTokenException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when a token is invalid, expired, or already used
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'reason',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusCode',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'expired',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'alreadyUsed',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'notFound',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/InvalidWagerStateException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\InvalidWagerStateException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when attempting an operation on a wager in an invalid state
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
          'wager' => 'App\\Models\\Wager',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attemptedAction',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'validStatuses',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAttemptedAction',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getValidStatuses',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusCode',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/UserAlreadyJoinedException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\UserAlreadyJoinedException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when a user attempts to join a wager they\'ve already joined
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
          'user' => 'App\\Models\\User',
          'wager' => 'App\\Models\\Wager',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUser',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\User',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusCode',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/WagerAlreadySettledException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\WagerAlreadySettledException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when attempting to settle an already settled wager
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
          'wager' => 'App\\Models\\Wager',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusCode',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Exceptions/WagerNotOpenException.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Exceptions\\WagerNotOpenException',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Thrown when attempting to join a wager that is not in \'open\' status
 */',
         'namespace' => 'App\\Exceptions',
         'uses' => 
        array (
          'wager' => 'App\\Models\\Wager',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Exceptions\\BeatWagerException',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserMessage',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Controllers/Api/TelegramWebhookController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Api\\TelegramWebhookController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Handles incoming webhooks from Telegram Bot API
 *
 * NOTE: This controller is not unit tested due to direct BotApi instantiation.
 * Webhook functionality should be verified through:
 * - Manual testing with a Telegram test bot
 * - Monitoring production logs for webhook errors
 * - E2E tests if webhook testing becomes critical
 *
 * TODO: Consider refactoring to use dependency injection for BotApi to enable mocking
 */',
         'namespace' => 'App\\Http\\Controllers\\Api',
         'uses' => 
        array (
          'controller' => 'App\\Http\\Controllers\\Controller',
          'usermessengerservice' => 'App\\Services\\UserMessengerService',
          'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
          'request' => 'Illuminate\\Http\\Request',
          'log' => 'Illuminate\\Support\\Facades\\Log',
          'botapi' => 'TelegramBot\\Api\\BotApi',
          'update' => 'TelegramBot\\Api\\Types\\Update',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle incoming webhook from Telegram
     */',
             'namespace' => 'App\\Http\\Controllers\\Api',
             'uses' => 
            array (
              'controller' => 'App\\Http\\Controllers\\Controller',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'jsonresponse' => 'Illuminate\\Http\\JsonResponse',
              'request' => 'Illuminate\\Http\\Request',
              'log' => 'Illuminate\\Support\\Facades\\Log',
              'botapi' => 'TelegramBot\\Api\\BotApi',
              'update' => 'TelegramBot\\Api\\Types\\Update',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Http\\JsonResponse',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Controllers/Controller.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\Controller',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Controllers/DashboardController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\DashboardController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the user\'s dashboard
     *
     * Authentication handled by \'signed.auth\' middleware - user is already authenticated via session
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateProfile',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update user profile settings
     *
     * Authentication handled by \'signed.auth\' middleware - user is already authenticated via session
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Controllers/ShortUrlController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\ShortUrlController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'redirect',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Redirect from short URL to target URL
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'shorturl' => 'App\\Models\\ShortUrl',
              'request' => 'Illuminate\\Http\\Request',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'code',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Controllers/WagerController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Controllers\\WagerController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'App\\Http\\Controllers\\Controller',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerService',
               'type' => 'App\\Services\\WagerService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'create',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show the wager creation form
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Inertia\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'store',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Store a new wager
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'success',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show success page
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerId',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'showSettlementForm',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show settlement form
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process settlement
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settlementSuccess',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show settlement success page
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerId',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'show',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Show wager landing page with progress, stats, and conditional settlement
     *
     * Authentication handled by \'signed.auth\' middleware - user is already authenticated via session
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerId',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settleFromShow',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process settlement from the wager show page
     */',
             'namespace' => 'App\\Http\\Controllers',
             'uses' => 
            array (
              'group' => 'App\\Models\\Group',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'usermessengerservice' => 'App\\Services\\UserMessengerService',
              'wagerservice' => 'App\\Services\\WagerService',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'inertia' => 'Inertia\\Inertia',
              'response' => 'Inertia\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerId',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Middleware/AuthenticateFromSignedUrl.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\AuthenticateFromSignedUrl',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Platform-agnostic authentication middleware
 *
 * Handles authentication from signed URLs (Telegram, Discord, Slack, etc.)
 * and establishes Laravel session for subsequent requests.
 *
 * Flow:
 * 1. If user already has session → Continue (business as usual)
 * 2. If valid signed URL with user identifier → Authenticate & establish session
 * 3. Otherwise → Return 401 Unauthorized
 */',
         'namespace' => 'App\\Http\\Middleware',
         'uses' => 
        array (
          'messengerservice' => 'App\\Models\\MessengerService',
          'onetimetoken' => 'App\\Models\\OneTimeToken',
          'user' => 'App\\Models\\User',
          'closure' => 'Closure',
          'request' => 'Illuminate\\Http\\Request',
          'auth' => 'Illuminate\\Support\\Facades\\Auth',
          'response' => 'Symfony\\Component\\HttpFoundation\\Response',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handle',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle an incoming request.
     *
     * Priority:
     * 1. Check if user is already authenticated via session
     * 2. Check for OneTimeToken (from bot commands like /mybets, /mybalance)
     * 3. Check for valid signed URL with user identifier (from wager links)
     * 4. Otherwise, return 401 Unauthorized
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'messengerservice' => 'App\\Models\\MessengerService',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'closure' => 'Closure',
              'request' => 'Illuminate\\Http\\Request',
              'auth' => 'Illuminate\\Support\\Facades\\Auth',
              'response' => 'Symfony\\Component\\HttpFoundation\\Response',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Symfony\\Component\\HttpFoundation\\Response',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'next',
               'type' => 'Closure',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Http/Middleware/HandleInertiaRequests.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Http\\Middleware\\HandleInertiaRequests',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Inertia\\Middleware',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'rootView',
          ),
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * The root template that\'s loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
              'ziggy' => 'Tighten\\Ziggy\\Ziggy',
            ),
             'constUses' => 
            array (
            ),
          )),
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'version',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
              'ziggy' => 'Tighten\\Ziggy\\Ziggy',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'share',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */',
             'namespace' => 'App\\Http\\Middleware',
             'uses' => 
            array (
              'request' => 'Illuminate\\Http\\Request',
              'middleware' => 'Inertia\\Middleware',
              'ziggy' => 'Tighten\\Ziggy\\Ziggy',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'Illuminate\\Http\\Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/AuditLog.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\AuditLog',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'timestamps',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'actor',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the actor (user who performed the action)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'morphto' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'auditable',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the auditable entity (polymorphic)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'morphto' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeAction',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope for filtering by action
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'morphto' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeByActor',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope for filtering by actor
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'morphto' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'actorId',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeRecent',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope for recent logs
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'morphto' => 'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hours',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/Group.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\Group',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'users',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagers',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagerTemplates',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'transactions',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sendMessage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send a message to this group via its platform messenger
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'App\\DTOs\\Message',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getChatId',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the platform-specific chat ID for this group
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/MessengerService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\MessengerService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDisplayNameAttribute',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeTelegram',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeDiscord',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeSlack',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findByPlatform',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => '?self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platform',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platformUserId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/OneTimeToken.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\OneTimeToken',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a unique token
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'type',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'context',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiresInMinutes',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isValid',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if token is valid
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isExpired',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if token is expired
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isUsed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if token has been used
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'markAsUsed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark token as used
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getContext',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get context value
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'mixed',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => '?mixed',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/ShortUrl.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\ShortUrl',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'casts',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateUniqueCode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a unique short code
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'length',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isExpired',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if the short URL has expired
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scopeActive',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Scope to get only active (non-expired) short URLs
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/Transaction.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\Transaction',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'group',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagerEntry',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/User.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\User',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Foundation\\Auth\\User',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
        2 => 'Illuminate\\Notifications\\Notifiable',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'hidden',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'groups',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagers',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagerEntries',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'transactions',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'messengerServices',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getMessengerService',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?App\\Models\\MessengerService',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platform',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTelegramService',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?App\\Models\\MessengerService',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/UserGroup.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\UserGroup',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Relations\\Pivot',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'table',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'incrementing',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'keyType',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'group',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/Wager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\Wager',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'group',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'creator',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settler',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'entries',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'transactions',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'oneTimeTokens',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isBinary',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if wager is binary (yes/no)
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isMultipleChoice',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if wager is multiple choice
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isNumeric',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if wager is numeric
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isDate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if wager is date-based
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDisplayOptions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get display options for the wager
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'hasmany' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/WagerEntry.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\WagerEntry',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'user',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'group',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getFormattedAnswer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the formatted answer based on wager type
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'hasfactory' => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/WagerSettlementToken.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\WagerSettlementToken',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'creator',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a unique settlement token for a wager
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiresInHours',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isValid',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if token is valid
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isExpired',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if token is expired
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'markAsUsed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark token as used
     */',
             'namespace' => 'App\\Models',
             'uses' => 
            array (
              'hasuuids' => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
              'belongsto' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Models/WagerTemplate.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Models\\WagerTemplate',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Database\\Eloquent\\Model',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
        1 => 'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'fillable',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'abstract' => false,
           'final' => false,
           'publicSet' => false,
           'protectedSet' => false,
           'privateSet' => false,
           'virtual' => false,
           'attributes' => 
          array (
          ),
           'hooks' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'casts',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'group',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'creator',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Providers/AppServiceProvider.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Providers\\AppServiceProvider',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Support\\ServiceProvider',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register any application services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Bootstrap any application services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Providers/HttpsServiceProvider.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Providers\\HttpsServiceProvider',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'Illuminate\\Support\\ServiceProvider',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'url' => 'Illuminate\\Support\\Facades\\URL',
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Bootstrap services.
     */',
             'namespace' => 'App\\Providers',
             'uses' => 
            array (
              'url' => 'Illuminate\\Support\\Facades\\URL',
              'serviceprovider' => 'Illuminate\\Support\\ServiceProvider',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/AuditService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\AuditService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'log',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log an action to the audit trail
     *
     * @param string $action The action being performed (e.g., \'wager.created\')
     * @param Model|null $auditable The entity being acted upon
     * @param array $metadata Additional context data
     * @param User|null $actor The user performing the action (null = system)
     * @param string|null $ipAddress IP address of the actor
     * @param string|null $userAgent User agent string
     * @return AuditLog
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'auditlog' => 'App\\Models\\AuditLog',
              'user' => 'App\\Models\\User',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'App\\Models\\AuditLog',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditable',
               'type' => '?Illuminate\\Database\\Eloquent\\Model',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'actor',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'ipAddress',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userAgent',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logFromRequest',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log from a request context (auto-captures IP and user agent)
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'auditlog' => 'App\\Models\\AuditLog',
              'user' => 'App\\Models\\User',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'App\\Models\\AuditLog',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditable',
               'type' => '?Illuminate\\Database\\Eloquent\\Model',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'actor',
               'type' => '?App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logSystem',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log a system action (no actor)
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'auditlog' => 'App\\Models\\AuditLog',
              'user' => 'App\\Models\\User',
              'model' => 'Illuminate\\Database\\Eloquent\\Model',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'App\\Models\\AuditLog',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'auditable',
               'type' => '?Illuminate\\Database\\Eloquent\\Model',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/MessageService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\MessageService',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Platform-agnostic message builder
 * 
 * Generates Message DTOs from templates for any messenger platform.
 * Does NOT contain platform-specific formatting (HTML, Markdown, etc.)
 */',
         'namespace' => 'App\\Services',
         'uses' => 
        array (
          'button' => 'App\\DTOs\\Button',
          'buttonaction' => 'App\\DTOs\\ButtonAction',
          'message' => 'App\\DTOs\\Message',
          'messagetype' => 'App\\DTOs\\MessageType',
          'wager' => 'App\\Models\\Wager',
          'collection' => 'Illuminate\\Support\\Collection',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'wagerAnnouncement',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create wager announcement message
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'messagetype' => 'App\\DTOs\\MessageType',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Support\\Collection',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\DTOs\\Message',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settlementResult',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create settlement result message
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'messagetype' => 'App\\DTOs\\MessageType',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Support\\Collection',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\DTOs\\Message',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'winners',
               'type' => 'Illuminate\\Support\\Collection',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settlementReminder',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create settlement reminder message
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'messagetype' => 'App\\DTOs\\MessageType',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Support\\Collection',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\DTOs\\Message',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'viewUrl',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'viewProgressDM',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create view progress DM message
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'messagetype' => 'App\\DTOs\\MessageType',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Support\\Collection',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\DTOs\\Message',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'shortUrl',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'joinConfirmation',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create join confirmation message
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'messagetype' => 'App\\DTOs\\MessageType',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Support\\Collection',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\DTOs\\Message',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/MessengerFactory.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\MessengerFactory',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Factory for resolving the appropriate messenger based on group platform
 */',
         'namespace' => 'App\\Services',
         'uses' => 
        array (
          'messengerinterface' => 'App\\Contracts\\MessengerInterface',
          'group' => 'App\\Models\\Group',
          'telegrammessenger' => 'App\\Services\\Messengers\\TelegramMessenger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'for',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the appropriate messenger for a group\'s platform
     *
     * @param Group $group
     * @return MessengerInterface
     * @throws \\Exception
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'messengerinterface' => 'App\\Contracts\\MessengerInterface',
              'group' => 'App\\Models\\Group',
              'telegrammessenger' => 'App\\Services\\Messengers\\TelegramMessenger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'App\\Contracts\\MessengerInterface',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/Messengers/TelegramMessenger.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\Messengers\\TelegramMessenger',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Telegram messenger adapter
 * 
 * Formats platform-agnostic messages for Telegram and sends them.
 */',
         'namespace' => 'App\\Services\\Messengers',
         'uses' => 
        array (
          'messengerinterface' => 'App\\Contracts\\MessengerInterface',
          'button' => 'App\\DTOs\\Button',
          'buttonaction' => 'App\\DTOs\\ButtonAction',
          'message' => 'App\\DTOs\\Message',
          'botapi' => 'TelegramBot\\Api\\BotApi',
          'inlinekeyboardmarkup' => 'TelegramBot\\Api\\Types\\Inline\\InlineKeyboardMarkup',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'App\\Contracts\\MessengerInterface',
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'send',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send message to Telegram chat
     */',
             'namespace' => 'App\\Services\\Messengers',
             'uses' => 
            array (
              'messengerinterface' => 'App\\Contracts\\MessengerInterface',
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'botapi' => 'TelegramBot\\Api\\BotApi',
              'inlinekeyboardmarkup' => 'TelegramBot\\Api\\Types\\Inline\\InlineKeyboardMarkup',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'App\\DTOs\\Message',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'chatId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'formatMessage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Format message content as Telegram HTML
     */',
             'namespace' => 'App\\Services\\Messengers',
             'uses' => 
            array (
              'messengerinterface' => 'App\\Contracts\\MessengerInterface',
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'botapi' => 'TelegramBot\\Api\\BotApi',
              'inlinekeyboardmarkup' => 'TelegramBot\\Api\\Types\\Inline\\InlineKeyboardMarkup',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'App\\DTOs\\Message',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'buildButtons',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Build Telegram inline keyboard from Button DTOs
     */',
             'namespace' => 'App\\Services\\Messengers',
             'uses' => 
            array (
              'messengerinterface' => 'App\\Contracts\\MessengerInterface',
              'button' => 'App\\DTOs\\Button',
              'buttonaction' => 'App\\DTOs\\ButtonAction',
              'message' => 'App\\DTOs\\Message',
              'botapi' => 'TelegramBot\\Api\\BotApi',
              'inlinekeyboardmarkup' => 'TelegramBot\\Api\\Types\\Inline\\InlineKeyboardMarkup',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?TelegramBot\\Api\\Types\\Inline\\InlineKeyboardMarkup',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'buttons',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/PointService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\PointService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getBalance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get user\'s current point balance in a group
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deductPoints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Deduct points from user\'s balance
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Transaction',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'type',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => '?App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerEntry',
               'type' => '?App\\Models\\WagerEntry',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'awardPoints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Award points to user\'s balance
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Transaction',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'type',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => '?App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerEntry',
               'type' => '?App\\Models\\WagerEntry',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'recordLoss',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Record a loss (points already deducted)
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Transaction',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerEntry',
               'type' => 'App\\Models\\WagerEntry',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'refundPoints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Refund points to user
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Transaction',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wagerEntry',
               'type' => 'App\\Models\\WagerEntry',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'initializeUserPoints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Initialize user\'s points in a group
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'applyPointDecay',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Apply point decay to inactive users
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'insufficientpointsexception' => 'App\\Exceptions\\InsufficientPointsException',
              'group' => 'App\\Models\\Group',
              'transaction' => 'App\\Models\\Transaction',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?App\\Models\\Transaction',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/TokenService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\TokenService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateSettlementToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a settlement token for a wager
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\OneTimeToken',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'creator',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'outcome',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settlementNote',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiresInHours',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateDisputeToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate a dispute token for a wager
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\OneTimeToken',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'creator',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiresInHours',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'verifyToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verify and consume a token
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?App\\Models\\OneTimeToken',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'token',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'consumeToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark token as used
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\OneTimeToken',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'token',
               'type' => 'App\\Models\\OneTimeToken',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getActiveTokensForWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all active tokens for a wager
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'Illuminate\\Database\\Eloquent\\Collection',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'invalidateTokensForWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Invalidate all tokens for a wager
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidtokenexception' => 'App\\Exceptions\\InvalidTokenException',
              'onetimetoken' => 'App\\Models\\OneTimeToken',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'str' => 'Illuminate\\Support\\Str',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/UserMessengerService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\UserMessengerService',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Service for managing Users and their MessengerService connections
 * in a platform-agnostic way (Telegram, Discord, Slack, etc.)
 */',
         'namespace' => 'App\\Services',
         'uses' => 
        array (
          'messengerservice' => 'App\\Models\\MessengerService',
          'user' => 'App\\Models\\User',
          'db' => 'Illuminate\\Support\\Facades\\DB',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findOrCreate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find or create a user and their messenger service connection
     *
     * @param string $platform Platform name (telegram, discord, slack)
     * @param string $platformUserId Platform-specific user ID
     * @param array $userData Additional user data (username, first_name, last_name, photo_url)
     * @return User The user with messenger service loaded
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'messengerservice' => 'App\\Models\\MessengerService',
              'user' => 'App\\Models\\User',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'App\\Models\\User',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platform',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platformUserId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userData',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findByPlatform',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find user by platform and platform user ID
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'messengerservice' => 'App\\Models\\MessengerService',
              'user' => 'App\\Models\\User',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => '?App\\Models\\User',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platform',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'platformUserId',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/var/www/html/app/Services/WagerService.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'App\\Services\\WagerService',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pointService',
               'type' => 'App\\Services\\PointService',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'createWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create a new wager in a group
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidstakeexception' => 'App\\Exceptions\\InvalidStakeException',
              'invalidwagerstateexception' => 'App\\Exceptions\\InvalidWagerStateException',
              'useralreadyjoinedexception' => 'App\\Exceptions\\UserAlreadyJoinedException',
              'wagernotopenexception' => 'App\\Exceptions\\WagerNotOpenException',
              'group' => 'App\\Models\\Group',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'group',
               'type' => 'App\\Models\\Group',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'creator',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'placeWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Place a wager entry for a user
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidstakeexception' => 'App\\Exceptions\\InvalidStakeException',
              'invalidwagerstateexception' => 'App\\Exceptions\\InvalidWagerStateException',
              'useralreadyjoinedexception' => 'App\\Exceptions\\UserAlreadyJoinedException',
              'wagernotopenexception' => 'App\\Exceptions\\WagerNotOpenException',
              'group' => 'App\\Models\\Group',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\WagerEntry',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user',
               'type' => 'App\\Models\\User',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'answerValue',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'points',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'lockWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Lock a wager (no more entries allowed)
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidstakeexception' => 'App\\Exceptions\\InvalidStakeException',
              'invalidwagerstateexception' => 'App\\Exceptions\\InvalidWagerStateException',
              'useralreadyjoinedexception' => 'App\\Exceptions\\UserAlreadyJoinedException',
              'wagernotopenexception' => 'App\\Exceptions\\WagerNotOpenException',
              'group' => 'App\\Models\\Group',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settleWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Settle a wager with the outcome
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidstakeexception' => 'App\\Exceptions\\InvalidStakeException',
              'invalidwagerstateexception' => 'App\\Exceptions\\InvalidWagerStateException',
              'useralreadyjoinedexception' => 'App\\Exceptions\\UserAlreadyJoinedException',
              'wagernotopenexception' => 'App\\Exceptions\\WagerNotOpenException',
              'group' => 'App\\Models\\Group',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'outcomeValue',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settlementNote',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settlerId',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cancelWager',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Cancel a wager and refund all entries
     */',
             'namespace' => 'App\\Services',
             'uses' => 
            array (
              'invalidanswerexception' => 'App\\Exceptions\\InvalidAnswerException',
              'invalidstakeexception' => 'App\\Exceptions\\InvalidStakeException',
              'invalidwagerstateexception' => 'App\\Exceptions\\InvalidWagerStateException',
              'useralreadyjoinedexception' => 'App\\Exceptions\\UserAlreadyJoinedException',
              'wagernotopenexception' => 'App\\Exceptions\\WagerNotOpenException',
              'group' => 'App\\Models\\Group',
              'user' => 'App\\Models\\User',
              'wager' => 'App\\Models\\Wager',
              'wagerentry' => 'App\\Models\\WagerEntry',
              'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
              'db' => 'Illuminate\\Support\\Facades\\DB',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'App\\Models\\Wager',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wager',
               'type' => 'App\\Models\\Wager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
