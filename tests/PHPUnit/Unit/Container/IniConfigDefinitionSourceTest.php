<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Container;

use DI\Definition\ValueDefinition;
use Piwik\Config;
use Piwik\Container\IniConfigDefinitionSource;

class IniConfigDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getDefinition_withMergeableDefinition_shouldReturnNull()
    {
        $definition = $this->getMockForAbstractClass('DI\Definition\MergeableDefinition');

        $definitionSource = new IniConfigDefinitionSource($this->createConfig());

        $this->assertNull($definitionSource->getDefinition('foo', $definition));
    }

    /**
     * @test
     */
    public function getDefinition_whenNotMatchingPrefix_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource($this->createConfig(), 'prefix.');

        $this->assertNull($definitionSource->getDefinition('foo'));
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigSection_shouldReturnEmptyArray()
    {
        $definitionSource = new IniConfigDefinitionSource(Config::getInstance());

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('old_config.foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('old_config.foo', $definition->getName());
        $this->assertSame(array(), $definition->getValue());
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigSectionAndKey_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(Config::getInstance());

        $this->assertNull($definitionSource->getDefinition('old_config.foo.bar'));
    }

    /**
     * @test
     */
    public function getDefinition_withUnknownConfigKey_shouldReturnNull()
    {
        $definitionSource = new IniConfigDefinitionSource(Config::getInstance());

        $this->assertNull($definitionSource->getDefinition('old_config.General.foo'));
    }

    /**
     * @test
     */
    public function getDefinition_withExistingConfigSection_shouldReturnValueDefinition()
    {
        $config = $this->createConfig();
        $config->expects($this->once())
            ->method('__get')
            ->with('General')
            ->willReturn(array('foo' => 'bar'));

        $definitionSource = new IniConfigDefinitionSource($config);

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('old_config.General');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('old_config.General', $definition->getName());
        $this->assertInternalType('array', $definition->getValue());
        $this->assertEquals(array('foo' => 'bar'), $definition->getValue());
    }

    /**
     * @test
     */
    public function getDefinition_withExistingConfigKey_shouldReturnValueDefinition()
    {
        $config = $this->createConfig();
        $config->expects($this->once())
            ->method('__get')
            ->with('General')
            ->willReturn(array('foo' => 'bar'));

        $definitionSource = new IniConfigDefinitionSource($config);

        /** @var ValueDefinition $definition */
        $definition = $definitionSource->getDefinition('old_config.General.foo');

        $this->assertTrue($definition instanceof ValueDefinition);
        $this->assertEquals('old_config.General.foo', $definition->getName());
        $this->assertEquals('bar', $definition->getValue());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Config
     */
    private function createConfig()
    {
        return $this->getMock('Piwik\Config', array(), array(), '', false);
    }
}
