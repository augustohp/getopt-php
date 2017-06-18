<?php

namespace tflori\Getopt;

class CommandLineParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseNoOptions()
    {
        $parser = new CommandLineParser(array(
            new Option('v', null)
        ));
        $parser->parse('some-command');
        $this->assertCount(
            0,
            $parser->getOptions(),
            'No options should be identified because no valid ones were passed.'
        );

        $operands = $parser->getOperands();
        $this->assertCount(
            0,
            $operands,
            'Operands are only identified after a - or --. None passed.'
        );
    }

    public function testParseNoOptionsWithAnOperandAfterDoubleDashes()
    {
        $parser = new CommandLineParser(array(
            new Option('v', null)
        ));
        $parser->parse('some-command -- an-operand');
        $this->assertCount(
            0,
            $parser->getOptions(),
            'No options should be identified because no valid ones were passed.'
        );

        $operands = $parser->getOperands();
        $this->assertCount(
            1,
            $operands,
            'One operand passed after --.'
        );
        $this->assertEquals(
            'an-operand',
            $operands[0]
        );
    }

    public function testParseUnknownOption()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null)
        ));
        $parser->parse('-b');
    }

    public function testParseRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a');
    }

    public function testParseMultipleOptionsWithOneHyphen()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $parser->parse('-ab');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOption()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $parser->parse('-a -b -a -a');

        $options = $parser->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseCumulativeOptionShort()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null)
        ));
        $parser->parse('-abaa');

        $options = $parser->getOptions();
        $this->assertEquals(3, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseShortOptionWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a value');

        $options = $parser->getOptions();
        $this->assertEquals('value', $options['a']);
    }

    public function testParseZeroArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a 0');

        $options = $parser->getOptions();
        $this->assertEquals('0', $options['a']);
    }

    public function testParseNumericOption()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('2', null)
        ));
        $parser->parse('-a 2 -2');

        $options = $parser->getOptions();
        $this->assertEquals('2', $options['a']);
        $this->assertEquals(1, $options['2']);
    }

    public function testParseCollapsedShortOptionsRequiredArgumentMissing()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-ab');
    }

    public function testParseCollapsedShortOptionsWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
            new Option('b', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-ab value');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals('value', $options['b']);
    }

    public function testParseNoArgumentOptionAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null),
        ));
        $parser->parse('-a - b');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(
            1,
            $operands,
            var_export($operands, true)
        );
        $this->assertEquals('b', $operands[0]);
    }

    public function testParsedRequiredArumentWithNoSpace()
    {
        $parser = new CommandLineParser(array(
            new Option('p', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-ppassword');
        $options = $parser->getOptions();
        $this->assertEquals('password', $options['p']);
    }
    public function testParseCollapsedRequiredArgumentWithNoSpace()
    {
        $parser = new CommandLineParser(array(
            new Option('v', null),
            new Option('p', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-vvvppassword');
        $options = $parser->getOptions();
        $this->assertEquals('password', $options['p']);
        $this->assertEquals(3, $options['v']);
    }

    public function testParseOperandsOnly()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $parser->parse('-- -a -b');

        $this->assertCount(0, $parser->getOptions());
        $operands = $parser->getOperands();
        $this->assertCount(2, $operands);
        $this->assertEquals('-a', $operands[0]);
        $this->assertEquals('-b', $operands[1]);
    }

    public function testParseLongOptionWithoutArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['option']);
    }

    public function testParseLongOptionWithoutArgumentAndOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::NO_ARGUMENT)
        ));
        $parser->parse('--option -- something');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['option']);
        $operands = $parser->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option value');

        $options = $parser->getOptions();
        $this->assertEquals('value', $options['option']);
        $this->assertEquals('value', $options['o']);
    }

    public function testParseLongOptionWithEqualsSignAndArgument()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::OPTIONAL_ARGUMENT)
        ));
        $parser->parse('--option=value -- something');

        $options = $parser->getOptions();
        $this->assertEquals('value', $options['option']);
        $operands = $parser->getOperands();
        $this->assertCount(1, $operands);
        $this->assertEquals('something', $operands[0]);
    }

    public function testParseLongOptionWithValueStartingWithHyphen()
    {
        $parser = new CommandLineParser(array(
            new Option('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('--option=-value');

        $options = $parser->getOptions();
        $this->assertEquals('-value', $options['option']);
    }

    public function testParseNoValueStartingWithHyphenRequired()
    {
        $this->setExpectedException('UnexpectedValueException');
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT),
            new Option('b', null)
        ));
        $parser->parse('-a -b');
    }

    public function testParseNoValueStartingWithHyphenOptional()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::OPTIONAL_ARGUMENT),
            new Option('b', null)
        ));
        $parser->parse('-a -b');

        $options = $parser->getOptions();
        $this->assertEquals(1, $options['a']);
        $this->assertEquals(1, $options['b']);
    }

    public function testParseOptionWithDefaultValue()
    {
        $optionA = new Option('a', null, Getopt::REQUIRED_ARGUMENT);
        $optionA->setArgument(new Argument(10));
        $optionB = new Option('b', 'beta', Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(20));
        $parser = new CommandLineParser(array($optionA, $optionB));
        $parser->parse('-a 12');

        $options = $parser->getOptions();
        $this->assertEquals(12, $options['a']);
        $this->assertEquals(20, $options['b']);
        $this->assertEquals(20, $options['beta']);
    }

    public function testDoubleHyphenNotInOperands()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a 0 foo -- bar baz');

        $options = $parser->getOptions();
        $this->assertEquals('0', $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(
            2,
            $operands,
            var_export($operands, true)
        );
        $this->assertNotContains(
            'foo',
            $operands,
            '"foo" was passed before "--", so it is not considered an operand.'
        );
        $this->assertContains('bar', $operands);
        $this->assertContains('baz', $operands);
    }

    public function testSingleHyphenValue()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'alpha', Getopt::REQUIRED_ARGUMENT)
        ));

        $parser->parse('-a -');

        $options = $parser->getOptions();
        $this->assertEquals('-', $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(0, $operands);

        $parser->parse('--alpha -');

        $options = $parser->getOptions();
        $this->assertEquals('-', $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(0, $operands);
    }

    public function testSingleHyphenOperand()
    {
        $parser = new CommandLineParser(array(
            new Option('a', null, Getopt::REQUIRED_ARGUMENT)
        ));
        $parser->parse('-a 0 -');

        $options = $parser->getOptions();
        $this->assertEquals('0', $options['a']);
        $operands = $parser->getOperands();
        $this->assertCount(
            0,
            $operands,
            'Nothing passed after "-", so no operands expected.'
        );
    }

    public function testParseWithArgumentValidation()
    {
        $validation = 'is_numeric';
        $optionA = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $optionA->setArgument(new Argument(null, $validation));
        $optionB = new Option('b', null, Getopt::REQUIRED_ARGUMENT);
        $optionB->setArgument(new Argument(null, $validation));
        $optionC = new Option('c', null, Getopt::OPTIONAL_ARGUMENT);
        $optionC->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($optionA, $optionB, $optionC));
        $parser->parse('-a 1 -b 2 -c');

        $options = $parser->getOptions();
        $this->assertSame('1', $options['a']);
        $this->assertSame('2', $options['b']);
        $this->assertSame(1, $options['c']);
    }

    public function testParseInvalidArgument()
    {
        $this->setExpectedException('UnexpectedValueException');
        $validation = 'is_numeric';
        $option = new Option('a', null, Getopt::OPTIONAL_ARGUMENT);
        $option->setArgument(new Argument(null, $validation));
        $parser = new CommandLineParser(array($option));
        $parser->parse('-a nonnumeric');
    }

    public function testStringWithSingleQuotes()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse('-a \'the value\'');
        $options = $parser->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testStringWithDoubleQuotes()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse('-a "the value"');
        $options = $parser->getOptions();

        self::assertSame('the value', $options['a']);
    }

    public function testSingleQuotesInString()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse('-a "the \'"');
        $options = $parser->getOptions();

        self::assertSame('the \'', $options['a']);
    }

    public function testDoubleQuotesInString()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse('-a \'the "\'');
        $options = $parser->getOptions();

        self::assertSame('the "', $options['a']);
    }

    public function testQuoteConcatenation()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
            new Option('b', 'optB', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse('-a \'this uses \'"\'"\' inside single quote\' -b "this uses "\'"\'" inside double quote"');
        $options = $parser->getOptions();

        self::assertSame('this uses \' inside single quote', $options['a']);
        self::assertSame('this uses " inside double quote', $options['b']);
    }

    public function testLinefeedAsSeparator()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse("-a\nvalue");
        $options = $parser->getOptions();

        self::assertSame('value', $options['a']);
    }

    public function testTabAsSeparator()
    {
        $parser = new CommandLineParser(array(
            new Option('a', 'optA', Getopt::REQUIRED_ARGUMENT),
        ));

        $parser->parse("-a\tvalue");
        $options = $parser->getOptions();

        self::assertSame('value', $options['a']);
    }
}
