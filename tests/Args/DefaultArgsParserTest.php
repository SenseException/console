<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Args;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\CommandName;
use Webmozart\Console\Api\Args\Format\CommandOption;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Args\DefaultArgsParser;
use Webmozart\Console\Args\StringArgs;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultArgsParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultArgsParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new DefaultArgsParser();
    }

    public function testParseCommandNames()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandName(new CommandName('add'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server add'), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseIgnoresMissingCommandNames()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandName(new CommandName('add'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs(''), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseCommandOptionsLongName()
    {
        $format = ArgsFormat::build()
            ->addCommandOption(new CommandOption('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('--server --add'), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseCommandOptionsShortName()
    {
        $format = ArgsFormat::build()
            ->addCommandOption(new CommandOption('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('--server -a'), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseIgnoresMissingCommandOptions()
    {
        $format = ArgsFormat::build()
            ->addCommandOption(new CommandOption('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs(''), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseArguments()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addArgument(new Argument('argument1'))
            ->addArgument(new Argument('argument2'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add foo bar'), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array('argument1' => 'foo', 'argument2' => 'bar'), $args->getArguments(false));
    }

    public function testParseIgnoresMissingOptionalArguments()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addArgument(new Argument('argument1', Argument::OPTIONAL))
            ->addArgument(new Argument('argument2', Argument::OPTIONAL))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add foo'), $format);

        $this->assertSame(array(), $args->getOptions(false));
        $this->assertSame(array('argument1' => 'foo'), $args->getArguments(false));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\CannotParseArgsException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testParseFailsIfMissingRequiredArgument()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addArgument(new Argument('argument1', Argument::REQUIRED))
            ->addArgument(new Argument('argument2', Argument::REQUIRED))
            ->getFormat();

        $this->parser->parseArgs(new StringArgs('server --add foo'), $format);
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\CannotParseArgsException
     * @expectedExceptionMessage Too many arguments
     */
    public function testParseFailsIfTooManyArguments()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addArgument(new Argument('argument'))
            ->getFormat();

        $this->parser->parseArgs(new StringArgs('server --add foo bar'), $format);
    }

    public function testParseLongOptionWithoutValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add --option'), $format);

        $this->assertSame(array('option' => true), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseLongOptionWithValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', null, Option::VALUE_OPTIONAL))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add --option foo'), $format);

        $this->assertSame(array('option' => 'foo'), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseLongOptionWithValue2()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', null, Option::VALUE_OPTIONAL))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add --option=foo'), $format);

        $this->assertSame(array('option' => 'foo'), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\CannotParseArgsException
     * @expectedExceptionMessage The "--option" option requires a value
     */
    public function testParseLongOptionFailsIfMissingValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', null, Option::VALUE_REQUIRED))
            ->getFormat();

        $this->parser->parseArgs(new StringArgs('server --add --option'), $format);
    }

    public function testParseShortOptionWithoutValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', 'o'))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add -o'), $format);

        $this->assertSame(array('option' => true), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseShortOptionWithValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', 'o', Option::VALUE_OPTIONAL))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add -o foo'), $format);

        $this->assertSame(array('option' => 'foo'), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    public function testParseShortOptionWithValue2()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', 'o', Option::VALUE_OPTIONAL))
            ->getFormat();

        $args = $this->parser->parseArgs(new StringArgs('server --add -ofoo'), $format);

        $this->assertSame(array('option' => 'foo'), $args->getOptions(false));
        $this->assertSame(array(), $args->getArguments(false));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Args\CannotParseArgsException
     * @expectedExceptionMessage The "--option" option requires a value
     */
    public function testParseShortOptionFailsIfMissingValue()
    {
        $format = ArgsFormat::build()
            ->addCommandName(new CommandName('server'))
            ->addCommandOption(new CommandOption('add', 'a'))
            ->addOption(new Option('option', 'o', Option::VALUE_REQUIRED))
            ->getFormat();

        $this->parser->parseArgs(new StringArgs('server --add -o'), $format);
    }
}
