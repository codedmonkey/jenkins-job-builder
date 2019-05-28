<?php
/*
 * (c) Tim Goudriaan <tim@codedmonkey.com>
 */

namespace CodedMonkey\Jenkins\Builder\Dumper\Config;

use CodedMonkey\Jenkins\Builder\Config\ParameterizedTriggerBuilder;
use CodedMonkey\Jenkins\Builder\Config\ParameterizedTriggersBuilder;
use CodedMonkey\Jenkins\Builder\Exception\BuilderException;

class ParameterizedTriggerBuilderDumper
{
    public function dump(\DOMDocument $dom, $builder)
    {
        if (!$builder instanceof ParameterizedTriggerBuilder && !$builder instanceof ParameterizedTriggersBuilder) {
            throw new BuilderException(sprintf('Invalid builder class: %s', get_class($builder)));
        }

        $node = $dom->createElement('hudson.plugins.parameterizedtrigger.TriggerBuilder');
        $node->setAttribute('plugin', 'parameterized-trigger');

        $configsNode = $dom->createElement('configs');
        $node->appendChild($configsNode);

        $triggers = $builder instanceof ParameterizedTriggerBuilder ? [$builder] : $builder->getTriggers();

        foreach ($triggers as $trigger) {
            $triggerNode = $this->dumpTrigger($dom, $trigger);
            $configsNode->appendChild($triggerNode);
        }

        return $node;
    }

    public function dumpTrigger(\DOMDocument $dom, ParameterizedTriggerBuilder $trigger): \DOMElement
    {
        $node = $dom->createElement('hudson.plugins.parameterizedtrigger.BlockableBuildTriggerConfig');

        $configsNode = $dom->createElement('configs');
        $node->appendChild($configsNode);

        if (count($trigger->getBooleanParameters())) {
            $booleanParametersNode = $dom->createElement('hudson.plugins.parameterizedtrigger.BooleanParameters');

            $booleanParametersConfigNode = $dom->createElement('configs');
            $booleanParametersNode->appendChild($configsNode);

            foreach ($trigger->getBooleanParameters() as $key => $value) {
                $booleanParameterNode = $dom->createElement('hudson.plugins.parameterizedtrigger.BooleanParameterConfig');
                $booleanParametersConfigNode->appendChild($booleanParameterNode);

                $booleanParameterNameNode = $dom->createElement('name', $key);
                $booleanParameterNode->appendChild($booleanParameterNameNode);

                $booleanParameterValueNode = $dom->createElement('value', $value);
                $booleanParameterNode->appendChild($booleanParameterValueNode);
            }
        }

        if (count($trigger->getPredefinedParameters())) {
            $predefinedParameters = implode("\n", array_map(function($key, $value) {
                return sprintf('%s=%s', $key, $value);
            }, array_keys($trigger->getPredefinedParameters()), $trigger->getPredefinedParameters()));

            $predefinedParametersNode = $dom->createElement('hudson.plugins.parameterizedtrigger.PredefinedBuildParameters');
            $configsNode->appendChild($predefinedParametersNode);

            $predefinedParametersPropertiesNode = $dom->createElement('properties');
            $predefinedParametersPropertiesNode->appendChild($dom->createTextNode($predefinedParameters));
            $predefinedParametersNode->appendChild($predefinedParametersPropertiesNode);

            // todo make configurable
            $textParametersNode = $dom->createElement('textParamValueOnNewLine', 'false');
            $predefinedParametersNode->appendChild($textParametersNode);
        }

        if ($trigger->getCurrentParameters()) {
            $currentParametersNode = $dom->createElement('hudson.plugins.parameterizedtrigger.CurrentBuildParameters');
            $configsNode->appendChild($currentParametersNode);
        }

        if ($trigger->getCurrentNode()) {
            $currentNodeNode = $dom->createElement('hudson.plugins.parameterizedtrigger.NodeParameters');
            $configsNode->appendChild($currentNodeNode);
        }

        if (!$configsNode->hasChildNodes()) {
            $configsNode->setAttribute('class', 'empty-list');
        }

        $projectsNode = $dom->createElement('projects', implode(' ,', $trigger->getJobs()));
        $node->appendChild($projectsNode);

        $conditionNode = $dom->createElement('condition', strtoupper($trigger->getCondition()));
        $node->appendChild($conditionNode);

        $noParametersNode = $dom->createElement('triggerWithNoParameters', $trigger->getTriggerWithNoParameters() ? 'true' : 'false');
        $node->appendChild($noParametersNode);

        $fromChildProjectsNode = $dom->createElement('triggerFromChildProjects', $trigger->getTriggerFromChildProjects() ? 'true' : 'false');
        $node->appendChild($fromChildProjectsNode);

        if ($trigger->getBlockProject()) {
            $blockNode = $dom->createElement('block');
            $node->appendChild($blockNode);

            $thresholds = $trigger->getBlockProjectThresholds();

            if (null !== $buildStepFailureThreshold = ($thresholds['buildStepFailure'] ?? null)) {
                $buildStepFailureThresholdNode = $dom->createElement('buildStepFailureThreshold');
                $blockNode->appendChild($buildStepFailureThresholdNode);

                $this->fillBlockThresholdNode($dom, $buildStepFailureThresholdNode, $buildStepFailureThreshold);
            }

            if (null !== $unstableThreshold = ($thresholds['unstable'] ?? null)) {
                $unstableThresholdNode = $dom->createElement('unstableThreshold');
                $blockNode->appendChild($unstableThresholdNode);

                $this->fillBlockThresholdNode($dom, $unstableThresholdNode, $unstableThreshold);
            }

            if (null !== $failureThreshold = ($thresholds['failure'] ?? null)) {
                $failureThresholdNode = $dom->createElement('failureThreshold');
                $blockNode->appendChild($failureThresholdNode);

                $this->fillBlockThresholdNode($dom, $failureThresholdNode, $failureThreshold);
            }
        }

        // todo make configurable
        $buildWithLabelsNode =  $dom->createElement('buildAllNodesWithLabel', 'false');
        $node->appendChild($buildWithLabelsNode);

        return $node;
    }

    private function fillBlockThresholdNode(\DOMDocument $dom, \DOMElement $thresholdNode, ?string $threshold): void
    {
        static $thresholdParameters = [
            'SUCCESS' => [
                'ordinal' => 0,
                'color' => 'BLUE',
                'completeBuild' => true,
            ],
            'FAILURE' => [
                'ordinal' => 2,
                'color' => 'RED',
                'completeBuild' => true,
            ],
            'UNSTABLE' => [
                'ordinal' => 1,
                'color' => 'YELLOW',
                'completeBuild' => true,
            ],
        ];

        $nameNode = $dom->createElement('name', $threshold);
        $thresholdNode->appendChild($nameNode);

        $ordinalNode = $dom->createElement('ordinal', $thresholdParameters[$threshold]['ordinal']);
        $thresholdNode->appendChild($ordinalNode);

        $colorNode = $dom->createElement('color', $thresholdParameters[$threshold]['color']);
        $thresholdNode->appendChild($colorNode);

        $completeBuildNode = $dom->createElement('completeBuild', $thresholdParameters[$threshold]['completeBuild'] ? 'true' : 'false');
        $thresholdNode->appendChild($completeBuildNode);
    }
}
