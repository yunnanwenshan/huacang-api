<?php

namespace App\Contracts;

/**
 * Request Validate.
 *
 * Interface RequestValidate
 */
interface ClientRequestAuth extends Auth
{
    /**
     * validate server token.
     *
     * @return mixed
     */
    public function valServerToken();

    /**
     * validate client signature.
     *
     * @return mixed
     */
    public function valClientSig();
}
