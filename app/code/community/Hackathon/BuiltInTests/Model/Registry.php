<?php
/**
 * This file is part of Hackathon_BuiltInTests for Magento.
 *
 * @license MIT
 * @author Fabian Schmengler <fs@integer-net.de> <@fschmengler>
 * @category Hackathon
 * @package Hackathon_BuiltInTests
 */

/**
 * BIT Registry Singleton, handles replacement of components with the corresponding BIT and passes its data to the test
 * case
 *
 * @package Hackathon_BuiltInTests
 */
class Hackathon_BuiltInTests_Model_Registry
{
    /**
     * @var Hackathon_BuiltInTests_TestCaseInterface
     */
    protected $_testCase;

    /**
     * @var string[]
     */
    protected $_bitComponents = array();

    /**
     * Registers current test case
     *
     * @param Hackathon_BuiltInTests_TestCaseInterface $testCase
     * @return $this
     */
    public function registerTestCase(Hackathon_BuiltInTests_TestCaseInterface $testCase)
    {
        $this->_testCase = $testCase;
        return $this;
    }

    /**
     * Registers a BIT component
     *
     * @param string $type         type like in config.xml (models, helpers, blocks)
     * @param string $alias        class alias for Magento rewrite
     * @param string $bitClassName class name of the BIT component
     * @return $this
     */
    public function registerBit($type, $alias, $bitClassName)
    {
        list($module, $class) = explode('/', $alias, 2);
        $rewriteNodePath = "global/{$type}/{$module}/rewrite/{$class}";
        $originalValue = (string) Mage::getConfig()->getNode($rewriteNodePath);
        $this->_bitComponents[$rewriteNodePath] = $originalValue;
        Mage::getConfig()->setNode($rewriteNodePath, $bitClassName);
        return $this;
    }

    /**
     * Resets registry state
     *
     * @return $this
     */
    public function reset()
    {
        $this->_testCase = null;
        foreach ($this->_bitComponents as $rewriteNodePath => $originalValue) {
            Mage::getConfig()->setNode($rewriteNodePath, $originalValue);
        }
        return $this;
    }

    /**
     * Logs "before" state to be tested by the test case
     *
     * @param array|object $state
     */
    public function logStateBefore($state)
    {
        $this->_testCase->logStateBefore($state);
    }

    /**
     * Logs "after" state to be tested by the test case
     *
     * @param array|object $state
     */
    public function logStateAfter($state)
    {
        $this->_testCase->logStateAfter($state);
    }
}