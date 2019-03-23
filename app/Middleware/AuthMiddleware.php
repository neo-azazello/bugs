<?php

namespace App\Middleware;


class AuthMiddleware extends Middleware {

    public function __invoke($request, $response, $next)
    {

        if(!$this->container->auth->check()){
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            $this->container->flash->addMessage('error', 'Please sign in with your code.');
            return $response->withRedirect($this->container->router->pathFor('auth.signin') . '?redirect='. $_SESSION['redirect']);
        }

        $response = $next($request, $response);
        return $response;
    }
}