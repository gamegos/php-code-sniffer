# Gamegos PHP Code Sniffer

Gamegos PHP Code Sniffer is a PHP code standard checker/fixer tool based on `PHP_CodeSniffer`.

## Install via Composer
```
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
```
#!/bin/sh
./COMPOSER_BIN_DIR/phpcs-pre-commit
```
Make sure the hook script is executable.
```bash
chmod +x .git/hooks/pre-commit
```

## Customize
You can customize configuration by adding a file called `phpcs.xml` file into
the root directory of your project. The phpcs.xml file has exactly the same
format as a normal ruleset.xml file, so all the same options are available in
it. You need to define `Gamegos` rule to import all the `Gamegos` rules.
```
<?xml version="1.0" encoding="UTF-8"?>
<ruleset>
    <rule ref="Gamegos" />
</ruleset>
```
### Using a custom bootstrap file
You can add custom bootstap files to be included before beginning the run.
Some sniffs need to load classes from your project; so adding a autoload file
will allow sniffs to do this.
```
<?xml version="1.0" encoding="UTF-8"?>
<ruleset>
    <rule ref="Gamegos" />
    <arg name="bootstrap" value="vendor/autoload.php" />
</ruleset>
```

## Imported Standards

### PSR2
All PSR2 sniffs are imported by default.
* `PSR2.ControlStructures.ElseIfDeclaration.NotAllowed` rule type is considered as `error` instead of `warning`.

### Generic
All sniffs under `Generic.Formatting` category except `DisallowMultipleStatements` (has an alternative) and `NoSpaceAfterCast` are imported.

### Squiz
Imported sniffs:
* `Squiz.Commenting.DocCommentAlignment`
* `Squiz.Commenting.InlineComment`
  * `InvalidEndChar` rule type is considered as `warning` instead of `error`.
* `Squiz.WhiteSpace.SuperfluousWhitespace`
* `Squiz.WhiteSpace.OperatorSpacing`

## Custom Sniffs & Overrides

### Gamegos.Arrays.ArrayDeclaration
* Extended from `Squiz.Arrays.ArrayDeclaration`.
* Arranged array element indents by start position of the first (declaration) line.
* Number of spaces before array elements is increased from 1 to 4.
* Removed rules:
  * NoKeySpecified
  * KeySpecified
  * MultiLineNotAllowed
  * NoCommaAfterLast
  * NoComma

### Gamegos.Commenting.DocComment
* Extended from `Generic.Commenting.DocComment`.
* Changed `MissingShort` rule type from `error` to `warning`.
* Ignored `MissingShort` rule for PHPUnit test class methods (requires a class loader [^1]).
* Removed rules for comments with long descriptions:
  * SpacingBetween
  * LongNotCapital
  * SpacingBeforeTags
  * ParamGroup
  * NonParamGroup
  * SpacingAfterTagGroup
  * TagValueIndent
  * ParamNotFirst
  * TagsNotGrouped

### Gamegos.Commenting.FunctionComment
* Extended from `PEAR.Commenting.FunctionComment`.
* Added `{@inheritdoc}` validation for overrided methods (requires a class loader [^1]).
* Added PHPUnit test class control for methods without doc comment (requires a class loader [^1]).

### Gamegos.Commenting.VariableComment
* Extended from `Squiz.Commenting.VariableComment`.
* Added `bool` and `int` into allowed variable types.

### Gamegos.Formatting.DisallowMultipleStatements
* Extended from `Generic.Formatting.DisallowMultipleStatements`.
* Fixed adding 2 blank lines when applying `SameLine` fixer with `Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace` fixer together.

### Gamegos.Strings.ConcatenationSpacing
This sniff has two rules and fixes.
* **`PaddingFound`**: There must be only one space between the concatenation operator (.) and the strings being concatenated.
* **`NotAligned`**: Multiline string concatenations must be aligned.

### Gamegos.WhiteSpace.FunctionSpacing
* Extended from `Squiz.WhiteSpace.FunctionSpacing`.
* No blank line required before the method which is the first defined element of a class.
* No blank line required after the method which is the last defined element of a class.
* Fixed fixing spaces before method definitions.

### Gamegos.WhiteSpace.MemberVarSpacing
* Extended from `Squiz.WhiteSpace.MemberVarSpacing`.
* No blank line required before the property which is the first defined element of a class.
* Fixed fixing spaces before property definitions.

## Development
You can test any modifications by running `phpcs.php`, `phpcbf.php` and `phpcs-pre-commit.php` scripts under `scripts` directory. To build binaries, run the command below:
```bash
php scripts/build.php
```

[^1]: A class loader is required (eg. via a bootstrap file), otherwise a warning (`Internal.Gamegos.NeedClassLoader`) will be generated. Override this warning in `phpcs.xml` file in your project to prevent warnings.
