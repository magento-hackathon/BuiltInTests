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
 * Interface for test case
 */
interface Hackathon_BuiltInTests_TestCaseInterface
{
    /**
     * @param array|object $state
     * @return Hackathon_BuiltInTests_TestCaseInterface
     */
    public function logStateBefore($state);

    /**
     * @param array|object $state
     * @return Hackathon_BuiltInTests_TestCaseInterface
     */
    public function logStateAfter($state);
}