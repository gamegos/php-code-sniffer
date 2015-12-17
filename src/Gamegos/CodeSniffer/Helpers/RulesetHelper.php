<?php
namespace Gamegos\CodeSniffer\Helpers;

/* Imports from PHP core */
use ReflectionProperty;
use InvalidArgumentException;

/* Imports from PHP_CodeSniffer */
use PHP_CodeSniffer_File;

/**
 * Ruleset Helper
 * Modifies the ruleset at the runtime.
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class RulesetHelper
{
    /**
     * CodeSniffer file reference
     * @var \PHP_CodeSniffer_File
     */
    protected $phpcsFile;

    /**
     * Reflection object to access non-public fields of CodeSniffer file.
     * @var \ReflectionProperty
     */
    protected $reflection;

    /**
     * Current ruleset
     * @var array
     */
    protected $current;

    /**
     * Original ruleset to be restored.
     * @var array
     */
    protected $backup;

    /**
     * Constructor
     * @param \PHP_CodeSniffer_File $phpcsFile
     */
    public function __construct(PHP_CodeSniffer_File $phpcsFile)
    {
        $this->phpcsFile  = $phpcsFile;
        $this->reflection = new ReflectionProperty($this->phpcsFile, 'ruleset');
        $this->reflection->setAccessible(true);
        $this->current = $this->reflection->getValue($this->phpcsFile);
        $this->backup  = $this->current;
    }

    /**
     * Restore before destruct.
     */
    public function __destruct()
    {
        $this->restore();
    }

    /**
     * Set severity of a rule.
     * @param string $rule
     * @param int $severity
     */
    public function setRuleSeverity($rule, $severity)
    {
        $this->updateRuleProperty($rule, 'severity', $severity);
    }

    /**
     * Set type of a rule.
     * @param string $rule
     * @param string $type
     */
    public function setRuleType($rule, $type)
    {
        $this->updateRuleProperty($rule, 'type', $type);
    }

    /**
     * Set property of a sniff.
     * @param string $sniff
     * @param string $property
     * @param mixed $value
     */
    public function setSniffProperty($sniff, $property, $value)
    {
        $this->updateSniffProperty($sniff, $property, $value);
    }

    /**
     * Restore rules.
     */
    public function restore()
    {
        $this->current = $this->backup;
        $this->commit();
    }

    /**
     * Update a property of a rule.
     * @param  string $rule
     * @param  string $key
     * @param  mixed $value
     * @throws \InvalidArgumentException
     */
    protected function updateRuleProperty($rule, $key, $value)
    {
        if (preg_match('/^[^.]+(\.[^.]+){3}$/', $rule)) {
            $this->current[$rule][$key] = $value;
            $this->commit();
        } else {
            throw new InvalidArgumentException('Invalid rule name!');
        }
    }

    /**
     * Update a property of a sniff.
     * @param  string $sniff
     * @param  string $key
     * @param  mixed $value
     * @throws \InvalidArgumentException
     */
    protected function updateSniffProperty($sniff, $key, $value)
    {
        if (preg_match('/^[^.]+(\.[^.]+){2}$/', $sniff)) {
            $this->current[$sniff]['properties'][$key] = $value;
            $this->commit();
        } else {
            throw new InvalidArgumentException('Invalid sniff name!');
        }
    }

    /**
     * Commit changes to the CodeSniffer file.
     */
    protected function commit()
    {
        $this->reflection->setValue($this->phpcsFile, $this->current);
    }
}
