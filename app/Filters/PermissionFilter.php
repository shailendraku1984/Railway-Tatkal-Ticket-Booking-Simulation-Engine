<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

class PermissionFilter implements FilterInterface
{
    /**
     * This logic intercepts the web request BEFORE the controller executes.
     */
public function before(\CodeIgniter\HTTP\RequestInterface $request, $arguments = null)
{
    // 1. If no specific permission parameter was provided, skip the check
    if (empty($arguments)) {
        return;
    }

    // 2. FIXED: Unpack the array passed by the colon syntax (can:expenses.create)
    // CodeIgniter sends it as [0 => 'expenses.create']. We must extract element 0.
    $requiredPermission = is_array($arguments) ? $arguments[0] : $arguments;

    // 3. Fetch the logged-in user ID from the active session container
    $userId = session()->get('user_id'); 

    // 4. Instantiate the authorization database service class
    $authService = new \App\Services\AuthorizationService();

    // 5. Run the strict permission verification query link check
    if (!$userId || !$authService->checkUserPermission((int)$userId, $requiredPermission)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            "Unauthorized access attempt! This module action configuration is restricted."
        );
    }
}


    /**
     * This logic executes AFTER the controller task finishes (Not required for security gates).
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
