Gamegos PHP Code Sniffer
========================

Gamegos PHP Code Sniffer is a PHP code standard checker/fixer tool based on `PHP_CodeSniffer`.

Installing via Composer
-----------------------

    {
        "require-dev": {
            "gamegos/php-code-sniffer": "*"
        }
    }

Binaries
--------
Binaries are located in `bin` directory but composer installer creates links under
your vendor binary directory depending on your composer configuration.

* **phpcs :** Checks PHP files against defined coding standard rules.
* **phpcbf :** Corrects fixable coding standard violations.
* **phpcs-pre-commit :** Runs phpcs for modified files in git repository.

Pre-Commit Hook
---------------
Save the following script as `.git/hooks/pre-commit` by replacing `COMPOSER_BIN_DIR`
as your vendor binary directory name depending on your composer configuration.

    #!/bin/sh
    ./COMPOSER_BIN_DIR/phpcs-pre-commit

Make sure the hook script is executable.

    chmod +x .git/hooks/pre-commit
