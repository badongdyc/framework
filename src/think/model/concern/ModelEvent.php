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

namespace think\model\concern;

use think\App;
use think\Container;
use think\exception\ModelEventException;

/**
 * 模型事件处理
 */
trait ModelEvent
{
    /**
     * 模型事件观察
     * @var array
     */
    protected static $observe = ['after_read', 'before_write', 'after_write', 'before_insert', 'after_insert', 'before_update', 'after_update', 'before_delete', 'after_delete', 'before_restore', 'after_restore'];

    /**
     * 模型事件观察者类名
     * @var string
     */
    protected $observerClass;

    /**
     * 是否需要事件响应
     * @var bool
     */
    protected $withEvent = true;

    /**
     * 注册一个模型观察者
     *
     * @param  string  $class
     * @return void
     */
    protected static function observe(string $class): void
    {
        foreach (static::$observe as $event) {
            $call = 'on' . App::parseName($event, 1, false);

            if (method_exists($class, $call)) {
                $instance = Container::getInstance()->invokeClass($class);

                Container::pull('event')->listen(static::class . '.' . $event, [$instance, $call]);
            }
        }
    }

    /**
     * 当前操作的事件响应
     * @access protected
     * @param  bool $event  是否需要事件响应
     * @return $this
     */
    public function withEvent(bool $event)
    {
        $this->withEvent = $event;
        return $this;
    }

    /**
     * 触发事件
     * @access protected
     * @param  string $event  事件名
     * @return bool
     */
    protected function trigger(string $event): bool
    {
        if (!$this->withEvent) {
            return true;
        }

        $call  = 'on' . App::parseName($event, 1, false);
        $class = static::class;

        try {
            if (method_exists($class, $call)) {
                $result = Container::getInstance()->invoke([$class, $call], [$this]);
            } else {
                $result = Container::pull('event')->trigger($class . '.' . $event, $this, true);
            }

            return false === $result ? false : true;
        } catch (ModelEventException $e) {
            return false;
        }
    }
}
