<?php

namespace PHPUnit\Framework;

use RuntimeException;

class AssertionFailedError extends RuntimeException
{
}

\class_alias(AssertionFailedError::class, 'PHPUnit_Framework_AssertionFailedError');
