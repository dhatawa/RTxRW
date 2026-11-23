<?php

namespace App\Exceptions;

use RuntimeException;

/** Dilempar saat transisi status tidak sah. Pesan aman ditampilkan ke pengguna. */
class WorkflowException extends RuntimeException
{
}
