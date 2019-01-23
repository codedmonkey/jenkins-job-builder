<?php
/*
 * (c) Tim Goudriaan <tim@codedmonkey.com>
 */

namespace CodedMonkey\Jenkins\Builder\Dumper\Config;

use CodedMonkey\Jenkins\Builder\Config\ParameterInterface;

abstract class AbstractParameterDumper
{
    public function dump(\DOMDocument $dom, ParameterInterface $parameter)
    {
        $node = $dom->createElement($this->getNodeName());

        $nameNode = $dom->createElement('name');
        $nameNode->appendChild($dom->createTextNode($parameter->getName()));
        $node->appendChild($nameNode);

        $descriptionNode = $dom->createElement('description');
        if ('' != $parameter->getDescription()) {
            $descriptionNode->appendChild($dom->createTextNode($parameter->getDescription()));
        }
        $node->appendChild($descriptionNode);

        $defaultNode = $dom->createElement('defaultValue');
        if ('' !== $defaultValue = $this->dumpDefaultValue($parameter->getDefaultValue())) {
            $defaultNode->appendChild($dom->createTextNode($defaultValue));
        }
        $node->appendChild($defaultNode);

        return $node;
    }

    abstract protected function getNodeName(): string;

    abstract protected function dumpDefaultValue($defaultValue): string;
}
