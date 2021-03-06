<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\middleware;

use Closure;
use think\App;
use think\Request;
use think\Session;

class SessionInit
{

    /** @var Session */
    protected $session;

    /** @var App */
    protected $app;

    public function __construct(App $app, Session $session)
    {
        $this->app     = $app;
        $this->session = $session;
    }

    /**
     * Session初始化
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle($request, Closure $next)
    {
        // Session初始化
        $varSessionId = $this->app->config->get('route.var_session_id');

        if ($varSessionId && $request->request($varSessionId)) {
            $this->session->setId($request->request($varSessionId));
        } else {
            $cookieName = $this->app->config->get('session.cookie_name', 'PHPSESSID');
            $sessionId  = $request->cookie($cookieName) ?: '';
            $this->session->setId($sessionId);
        }

        $request->withSession($this->session);

        $response = $next($request);

        if (isset($cookieName)) {
            $this->app->cookie->set($cookieName, $this->session->getId());
        }

        return $response;
    }
}
