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

Customize
---------
You can customize configuration by adding a file called `phpcs.xml` file into
the root directory of your project. The phpcs.xml file has exactly the same
format as a normal ruleset.xml file, so all the same options are available in
it. You need to define `Gamegos` rule to import all the `Gamegos` rules.

    <?xml version="1.0" encoding="UTF-8"?>
    <ruleset>
        <rule ref="Gamegos" />
    </ruleset>

### Using a custom bootstrap file
You can add custom bootstap files to be included before beginning the run.
Some sniffs need to load classes from your project; so adding a autoload file
will allow sniffs to do this.

    <?xml version="1.0" encoding="UTF-8"?>
    <ruleset>
        <rule ref="Gamegos" />
        <arg name="bootstrap" value="vendor/autoload.php" />
    </ruleset>

