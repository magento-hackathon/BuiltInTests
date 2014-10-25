Built-in Tests
====

The idea
----

Unit tests for Magento components are sometimes hard to write because the components depend on too many external
conditions and are called by the framework under circumstances that you have to simulate. This is error prone and could
be simplified by testing the components in their "natural environment" instead of completely isolate them.

But what if you still want to test concrete input and output of some methods in your component? You don't have access
to them because they are called by the framework, you only can test the final outcome.

This is where built-in tests come into place. A built-in test component (BIT component) replaces the original component
under test (CUT) transparently. It passes all requests to the original component but logs input and output so that it
can be tested by the test controller (which would be a test case in the unit test framework of your choice).

[[http://i.imgur.com/8mj1zFg.jpg]]

Use cases
----

After we all agreed that this is a cool idea, we could not come up with a good use case where tests really benefit from
this method, i.e. get simpler.

> Ben ‏@benmarks 11 Min.
>
> OH (@tobi_pb): "Well, we've implemented our solution completely, but now we cannot think of a use case." #mhzh #mm14ch

There is a special case however, where it has been used and actually made sense: Rewriting a complex untestable method
of a class in a third party extension. The method relied on the object being in a certain state which was hard to simulate.
And the final outcome was a generated PDF, which is also difficult to test.
The test needed to make sure that the rewrite changed the right things when triggered from the invoice generation process
of Magento.

So, consider using built-in tests if the following conditions are met

1. the component under test is a rewrite that modifies code with bad testability
2. the final outcome of the rewrite is also hard to test


Usage
----

The BIT Component has to be implemented like this:
```
class My_Module_Test_Component_Bit extends Hackathon_BuiltInTests_Component
{
    /**
     * Constructs original component
     */
    public function __construct()
    {
        $this->_component = new My_Module_Component();
    }

    /**
     * Method under test
     *
     */
    public function someMethod($someParameter)
    {
        Mage::getSingleton('hackathon_builtintest/registry')->logStateBefore(array(
            'parameter' => $someParameter
            'something_else' => $this->_component->getSomething(),
        ));
        $result = $this->_component->someMethod($someParameter);
        Mage::getSingleton('hackathon_builtintest/registry')->logStateAfter(array(
            'result' => $result,
            'something_else' => $this->_component->getSomething(),
        ));
        return $result;
    }
}
```

All methods that are undefined will get passed to the original component immediately. Note that you have to create the
real component with the "new" keyword because `Mage::getModel()`  and friends would create the BIT component.
The methods `logStateBefore()` and `logStateAfter` can take any kind of data that you want to check in your test case,
usually input and output of the original method.

In the setup and teardown methods of your test case you have to register the BIT component and also the test case, which
has to implement `Hackathon_BuiltInTests_TestCaseInterface`

```
class My_Module_Test_Component extends Whatever_Test_Framework_Test_Case
    implements Hackathon_BuiltInTests_TestCaseInterface
{
    protected function setUp()
    {
        Mage::getSingleton('hackathon_builtintest/registry')
            ->registerTestCase($this)
            ->registerBit('model', 'class_alias/to_test', 'My_Module_Test_Component_Bit');
    }

    protected function tearDown()
    {
        Mage::getSingleton('hackathon_builtintest/registry')->reset();
    }

    public function logStateBefore($state)
    {
        $this->_stateBefore = $state;
    }
    public function logStateAfter($state)
    {
        $this->_stateAfter = $state;
    }
}
```

The actual test case then follows this structure:

```
    public function testSomeMethod()
    {
        // 1) do stuff with Magento that triggers the components method eventually
        // 2) test assertions on $this->_stateBefore and $this->_stateAfter
    }
```