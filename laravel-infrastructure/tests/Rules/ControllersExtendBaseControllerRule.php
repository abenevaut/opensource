<?php

namespace Tests\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class ControllersExtendBaseControllerRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (
            empty($scope->getNamespace())
            || !str_starts_with($scope->getNamespace(), 'abenevaut\Infrastructure\Http\Controllers')
        ) {
            return [];
        }

        $reflectionClass = $node->getClassReflection();

        if (
            $reflectionClass->getName() === 'abenevaut\Infrastructure\Http\Controllers\ControllerAbstract'
            && !$reflectionClass->isSubclassOf('Illuminate\Routing\Controller')
        ) {
            return [
                RuleErrorBuilder::message("abenevaut\Infrastructure\Http\Controllers\ControllerAbstract should extend 'Illuminate\Routing\Controller'")->build(),
            ];
        }

        if (
            $reflectionClass->getName() !== 'abenevaut\Infrastructure\Http\Controllers\ControllerAbstract'
            && !$reflectionClass->isSubclassOf('abenevaut\Infrastructure\Http\Controllers\ControllerAbstract')
        ) {
            return [
                RuleErrorBuilder::message("Controller should extend 'abenevaut\Infrastructure\Http\Controllers\ControllerAbstract'")->build(),
            ];
        }

        return [];
    }
}
