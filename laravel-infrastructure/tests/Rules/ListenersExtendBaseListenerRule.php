<?php

namespace Tests\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class ListenersExtendBaseListenerRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (
            empty($scope->getNamespace())
            || !str_starts_with($scope->getNamespace(), 'abenevaut\Infrastructure\App\Listeners')
        ) {
            return [];
        }

        $reflectionClass = $node->getClassReflection();

        if (
            $reflectionClass->getName() === 'abenevaut\Infrastructure\App\Listeners\ListenerAbstract'
            && $reflectionClass->getParentClass() !== null
        ) {
            return [
                RuleErrorBuilder::message("abenevaut\Infrastructure\App\Listeners\ListenerAbstract should not extend any class")->build(),
            ];
        }

        if (
            $reflectionClass->getName() !== 'abenevaut\Infrastructure\App\Listeners\ListenerAbstract'
            && !$reflectionClass->isSubclassOf('abenevaut\Infrastructure\App\Listeners\ListenerAbstract')
        ) {
            return [
                RuleErrorBuilder::message("Listener should extend 'abenevaut\Infrastructure\App\Listeners\ListenerAbstract'")->build(),
            ];
        }

        return [];
    }
}
