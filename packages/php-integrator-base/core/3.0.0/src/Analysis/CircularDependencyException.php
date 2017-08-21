<?php

namespace PhpIntegrator\Analysis;

use RuntimeException;

/**
 * Indicates a circular dependency between classlikes (i.e. a class extending itself or an interface implementing
 * itself).
 */
class CircularDependencyException extends RuntimeException
{

}
