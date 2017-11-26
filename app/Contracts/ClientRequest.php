<?php

namespace App\Contracts;

interface ClientRequest
{
    /**
     * Set client info and User info.
     *
     * @return mixed
     */
    public function set($clientInfo, $userId, $ticket);

    /**
     * Set client info.
     *
     * @return mixed
     */
    public function setClientInfo($clientInfo);

    /**
     * Set client info and User info.
     *
     * @return mixed
     */
    public function setUser($userId, $ticket);

    /**
     * Get client info and User info.
     *
     * @return mixed
     */
    public function setRequest($request);
}
