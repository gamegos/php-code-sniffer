# Gamegos PHP Code Sniffer

Gamegos PHP Code Sniffer is a PHP code standard checker/beautifier/fixer tool
based on [PHP_CodeSniffer] and
includes [custom sniffs](#custom-sniffs) used in PHP projects developed by Gamegos.

## Requirements

Gamegos PHP Code Sniffer requires PHP 5.3 or later.

## Install via Composer

```json
{
    "require-dev": {
        "gamegos/php-code-sniffer": "*"
    }
}
```

## Binaries

Binaries are located in `bin` directory but composer installer creates links under
your vendor binary directory depending on your composer configuration.

* **phpcs :** Checks PHP files against defined coding standard rules.
* **phpcbf :** Corrects fixable coding standard violations.
* **phpcs-pre-commit :** Runs phpcs for modified files in git repository.

## Pre-Commit Hook
Save the following script as `.git/hooks/pre-commit` by replacing `COMPOSER_BIN_DIR`
as your vendor binary directory name depending on your composer configuration.

```sh
#!/bin/sh
./COMPOSER_BIN_DIR/phpcs-pre-commit
```

Make sure the hook script is executable.

```sh
chmod +x .git/hooks/pre-commit
```

## Customize

You can customize configuration by adding a file called `phpcs.xml` file into
the root directory of your project. The phpcs.xml file has exactly the same
format as a normal ruleset.xml file, so all the same options are available in
it. You need to define `Gamegos` rule to import all the `Gamegos` rules.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset>
    <rule ref="Gamegos" />
</ruleset>
```

### Using a custom bootstrap file
You can add custom bootstap files to be included before beginning the run.
Some sniffs need to load classes from your project; so adding a autoload file
will allow sniffs to do this.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset>
    <rule ref="Gamegos" />
    <arg name="bootstrap" value="vendor/autoload.php" />
</ruleset>
```

## Imported Standards

### PSR2
All PSR2 sniffs except `Squiz.WhiteSpace.ControlStructureSpacing` are imported by default.
* `PSR2.ControlStructures.ElseIfDeclaration.NotAllowed` rule type is considered as `error` instead of `warning`.

### Generic
Imported sniffs:
* All sniffs in `Generic.Formatting` category except:
  * `DisallowMultipleStatements` (replaced by [`Gamegos.Formatting.DisallowMultipleStatements`](#gamegosformattingdisallowmultiplestatements))
  * `NoSpaceAfterCast`
  * `SpaceAfterNot`
* `Generic.Arrays.DisallowLongArraySyntax`

### Squiz
Imported sniffs:
* `Squiz.Commenting.DocCommentAlignment`
* `Squiz.Commenting.InlineComment`
  * `InvalidEndChar` rule type is considered as `warning` instead of `error`.
* `Squiz.WhiteSpace.SuperfluousWhitespace`
* `Squiz.WhiteSpace.OperatorSpacing`

## Custom Sniffs

### Gamegos.Arrays.ArrayDeclaration
* Extended from `Squiz.Arrays.ArrayDeclaration`.
* Arranged array element indents by start position of the first (declaration) line.
* Number of spaces before array elements is increased from 1 to 4.
* Removed rules:
  * `NoKeySpecified`
  * `KeySpecified`
  * `MultiLineNotAllowed`
  * `NoCommaAfterLast`
  * `NoComma`

### Gamegos.Commenting.DocComment
* Extended from `Generic.Commenting.DocComment`.
* Ignored `MissingShort` rule for PHPUnit test class methods <sup name="fn1c1">[[1]](#fn1)</sup>.
* Changed `MissingShort` rule type from `error` to `warning`.
* Removed rules for comments with long descriptions:
  * `SpacingBetween`
  * `LongNotCapital`
  * `SpacingBeforeTags`
  * `ParamGroup`
  * `NonParamGroup`
  * `SpacingAfterTagGroup`
  * `TagValueIndent`
  * `ParamNotFirst`
  * `TagsNotGrouped`

### Gamegos.Commenting.FunctionComment
* Extended from `PEAR.Commenting.FunctionComment`.
* Added PHPUnit test class control for methods without doc comment <sup name="fn1c2">[[1]](#fn1)</sup>.
* Added `{@inheritdoc}` validation for overrided methods <sup name="fn1c3">[[1]](#fn1)</sup>.
* Removed `MissingParamComment`, `MissingReturn`, `SpacingAfterParamType` and `SpacingAfterParamName` rules.
* Ignored `MissingParamTag` rule for PHPUnit test class methods <sup name="fn1c4">[[1]](#fn1)</sup>.

### Gamegos.Commenting.VariableComment
* Extended from `Squiz.Commenting.VariableComment`.
* Added `bool` and `int` into allowed variable types.

### Gamegos.Formatting.DisallowMultipleStatements
* Extended from `Generic.Formatting.DisallowMultipleStatements`.
* Fixed adding 2 blank lines when applying `SameLine` fixer with `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` fixer together.

### Gamegos.Strings.ConcatenationSpacing
This sniff has two rules and fixes.
* `PaddingFound`: There must be only one space between the concatenation operator (.) and the strings being concatenated.
* `NotAligned`: Multiline string concatenations must be aligned.

### Gamegos.WhiteSpace.FunctionSpacing
* Extended from `Squiz.WhiteSpace.FunctionSpacing`.
* Expected no blank lines before the method which is the first defined element of a class.
* Expected no blank lines after the method which is the last defined element of a class.
* Fixed fixing spaces before method definitions.

### Gamegos.WhiteSpace.MemberVarSpacing
* Extended from `Squiz.WhiteSpace.MemberVarSpacing`.
* Expected no blank lines before the property which is the first defined element of a class.
* Fixed fixing spaces before property definitions.

## Development

### Live Testing
You can test any modifications by running [phpcs.php](scripts/phpcs.php), [phpcbf.php](scripts/phpcbf.php) and
[phpcs-pre-commit.php](scripts/phpcs-pre-commit.php) scripts under `scripts` directory.

### Building Binaries
Run the command below to re-build binaries:

```sh
php scripts/build.php
```

### PHP_CodeSniffer Dependency
Current version is built on [PHP_CodeSniffer 2.8.1](https://github.com/squizlabs/PHP_CodeSniffer/releases/tag/2.8.1)
which is locked in [composer.lock](composer.lock) file. To import new versions; edit [composer.json](composer.json) file if required and
run `composer update` command, then commit the modified [composer.lock](composer.lock) file. Updating [PHP_CodeSniffer] version may
break some of [Gamegos sniffs](#custom-sniffs), so you must carefully track any changes on [PHP_CodeSniffer] before updating.

___
<a name="fn1"><sup>[1]</sup></a> A class loader is required (eg. via a bootstrap file),
otherwise a warning (`Internal.Gamegos.NeedClassLoader`) will be generated.
You can override this rule in `phpcs.xml` file in your project to prevent warnings.
[↩](#fn1c1) [↩](#fn1c2) [↩](#fn1c3) [↩](#fn1c4)
___
#### License Notices
[PHP_CodeSniffer] is licensed under the [BSD 3-Clause](http://opensource.org/licenses/BSD-3-Clause) license.

[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer

