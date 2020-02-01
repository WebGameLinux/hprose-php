<?php
/*--------------------------------------------------------*\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: https://hprose.com                     |
|                                                          |
| Hprose/RPC/Plugins/ExecuteTimeoutHandler.php             |
|                                                          |
| Hprose ExecuteTimeoutHandler for PHP 7.1+                |
|                                                          |
| LastModified: Jan 31, 2020                               |
| Author: Ma Bingyao <andot@hprose.com>                    |
|                                                          |
\*________________________________________________________*/

namespace Hprose\RPC\Plugins;

use Hprose\RPC\Core\Context;
use Hprose\RPC\Core\TimeoutException;

class ExecuteTimeoutHandler {
    public $timeout;
    public function __construct(int $timeout = 30000) {
        $this->timeout = $timeout;
    }
    public function handler(string $name, array &$args, Context $context, callable $next) {
        $timeout = $context->method->options['timeout'] ?? $this->timeout;
        $timeout = (int) ($timeout / 1000);
        if ($timeout > 0) {
            $async = pcntl_async_signals();
            try {
                pcntl_async_signals(true);
                pcntl_signal(SIGALRM, function () {
                    throw new TimeoutException('timeout');
                });
                pcntl_alarm($timeout);
                return $next($name, $args, $context);
            } finally {
                pcntl_alarm(0);
                pcntl_async_signals($async);
            }
        } else {
            return $next($name, $args, $context);
        }
    }
}