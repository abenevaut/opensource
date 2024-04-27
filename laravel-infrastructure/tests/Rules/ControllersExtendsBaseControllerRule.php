<?php

namespace Tests\Rules;


use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;

class ControllersExtendsBaseControllerRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! str_starts_with($scope->getNamespace(), 'abenevaut\Infrastructure\Http\Controllers')) {
            return [];
        }

        $reflectionClass = $node->getClassReflection();

        if ($reflectionClass->getName() === 'abenevaut\Infrastructure\Http\Controllers\ControllerAbstract') {
            return [];
        }

        if (!$reflectionClass->isSubclassOf('abenevaut\Infrastructure\Http\Controllers\ControllerAbstract')) {
            return [
                "Controllers should extend 'abenevaut\Infrastructure\Http\Controllers\ControllerAbstract' (see rule #49)"
            ];
        }

        return [];
    }
}
