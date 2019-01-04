<?php
/*
 * (c) Tim Goudriaan <tim@codedmonkey.com>
 */

namespace Codedmonkey\Jenkins\Tests;

use CodedMonkey\Jenkins\Builder\JobConfigBuilder;
use PHPUnit\Framework\TestCase;

class JobConfigBuilderTest extends TestCase
{
    /** @var JobConfigBuilder */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new JobConfigBuilder();
    }

    public function testFolder()
    {
        $config = $this->builder
            ->setType(JobConfigBuilder::TYPE_FOLDER)
            ->buildConfig()
        ;

        $this->assertEquals($this->getFixture('folder'), $config);
    }

    protected function getFixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $name . '.xml');
    }
}
