# Gamegos PHP Code Sniffer Change Log

## [0.8.0] - 2023-08-23
* Fixed doc comment validation for override methods and test methods.

## [0.7.0] - 2023-08-21
* Added support for inheritdoc annotation variations.
* Updated `PHP_CodeSniffer` version to 2.9.2.

## [0.6.1] - 2017-04-26
* Fixed sniffing failure for files without class or interface declaration.

## [0.6.0] - 2017-03-22
* Updated `PHP_CodeSniffer` version to 2.8.1.
* Improved determining test classes for PHPUnit 6.
* Excluded some of the rules which are added by new PHP_CodeSniffer version:
  * `Generic.Formatting.SpaceAfterNot`
  * `Squiz.WhiteSpace.ControlStructureSpacing`
