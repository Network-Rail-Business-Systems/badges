# Readme Badges Generator

Generate badges as part of your pre-push hooks!

## Installation

1. Add `networkrailbusinesssystems/badges` to your Composer dev dependencies
```bash
composer require --dev networkrailbusinesssystems/badges
```

2. Ensure that PHPUnit outputs logs and coverage with the following settings:
```xml
<coverage>
    <report>
        <html outputDirectory=".phpunit.cache/html" />
        <text outputFile=".phpunit.cache/coverage.txt" showUncoveredFiles="false" showOnlySummary="true" />
    </report>
</coverage>
<logging>
    <junit outputFile=".phpunit.cache/tests.xml" />
</logging>
```

3. Add the following commands to the pre-push hook:
```bash
php vendor/networkrailbusinesssystems/badges/badges.php
git add .github/*.svg
git diff-index --quiet --cached HEAD || git commit -m "Updated badges" --no-verify
```

4. Add the following lines to `readme.md`, excluding badges as required:
```markdown
![Composer status](.github/composer.svg)
![Coverage status](.github/coverage.svg)
![Frontend version](.github/frontend.svg)
![Laravel version](.github/laravel.svg)
![NPM status](.github/npm.svg)
![PHP version](.github/php.svg)
![Tests status](.github/tests.svg)
```

## Usage

1. Run `git push` as normal
2. The pre-push hooks will fire, running your tests and coverage
3. The badges will automatically generate, update, and be added to the commit
