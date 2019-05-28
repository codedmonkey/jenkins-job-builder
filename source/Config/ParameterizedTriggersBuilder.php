<?php
/*
 * (c) Tim Goudriaan <tim@codedmonkey.com>
 */

namespace CodedMonkey\Jenkins\Builder\Config;

class ParameterizedTriggersBuilder implements BuilderInterface
{
    private $triggers = [];

    public function getTriggers(): array
    {
        return $this->triggers;
    }

    public function addTrigger(ParameterizedTriggerBuilder $trigger): self
    {
        $this->triggers[] = $trigger;

        return $this;
    }
}
