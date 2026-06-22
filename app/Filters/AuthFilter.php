<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()
                ->to(url_to('auth.login'))
                ->with('error', 'Please sign in to continue.');
        }
		
		if (session()->get('isLoggedIn') && $request!==null) {
			$allData = session()->get(); 
			$userId=$allData['auth_user_id'];
			$sql = "SELECT 
				u.id AS user_id,
				r.name AS role_name,
				p.module AS module_name,
				p.name AS permission_name
			FROM users u
			JOIN role_user ru ON ru.user_id = u.id
			JOIN role r ON r.id = ru.role_id
			JOIN permission_role pr ON pr.role_id = r.id
			JOIN permissions p ON p.id = pr.permission_id
			WHERE u.id = ?";

			$query = service('doctrine')->getEntityManager()->getConnection()->fetchAllAssociative($sql, [$userId]);
			$userPermissions = array_column($query, 'permission_name');
			
            $userPermissions[]="admin.dashboard";
			$userPermissions[]="admin.profile.picture";
			$userPermissions[]="admin.profile";
			$userPermissions[]="auth.logout";
			$userPermissions[]="logout";

			
			$router = service('router');
			$routeOptions = $router->getMatchedRouteOptions();

			$routeName = $routeOptions['as'] ?? null;
			 
			//dd(['checking_for' => $routeName, 'user_has' => $userPermissions]);exit;

			if($allData['auth_user_role']!=='SUPER_ADMIN'){ 
				if (!in_array($routeName, $userPermissions, true)) {
					
					throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
						"Unauthorized access attempt! This module action configuration is restricted."
					);
				}
			} 
			
			  
		}	
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
