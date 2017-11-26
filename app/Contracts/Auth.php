<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * Request Validate.
 *
 * Interface RequestValidate
 */
interface Auth
{
    /**
     * User Request Validate.
     *
     * @return mixed
     */
    public function validate();

    /**
     * Set Request Header Info.
     *
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);
}
