<?php
/**
+----------------------------------------------------------------------
| swoolefy framework bases on swoole extension development, we can use it easily!
+----------------------------------------------------------------------
| Licensed ( https://opensource.org/licenses/MIT )
+----------------------------------------------------------------------
| Author: bingcool <bingcoolhuang@gmail.com || 2437667702@qq.com>
+----------------------------------------------------------------------
*/

namespace Swoolefy\Udp;

use Swoolefy\Core\Swfy;
use Swoolefy\Core\Swoole;
use Swoolefy\Rpc\RpcDispatch;
use Swoolefy\Core\HanderInterface;

class UdpHander extends Swoole implements HanderInterface {

	/**
	 * $client_info 客户端信息
	 * @var null
	 */
	public $client_info = null;

	/**
	 * __construct 初始化
	 * @param    array  $config
	 */
	public function __construct(array $config=[]) {
		parent::__construct($config);
	}

	/**
	 * init 当执行run方法时,首先会执行init->bootstrap
	 * @param  mixed  $recv
	 * @return void       
	 */
	public function init($recv) {}

	/**
	 * bootstrap 当执行run方法时,首先会执行init->bootstrap
	 * @param  mixed  $recv
	 * @return void
	 */
	public function bootstrap($recv) {}


	/**
	 * run 完成初始化后,开始路由匹配和创建访问实例
	 * @param  int   $fd
	 * @param  mixed $recv
	 * @return mixed
	 */
	public function run($recv, $clientInfo) {
		// 必须要执行父类的run方法
		parent::run($fd = null , $recv);

		$this->client_info = $clientInfo;
		// worker进程
		if($this->isWorkerProcess()) {
			$recv = array_values(json_decode($recv, true));
			if(is_array($recv) && count($recv) == 3) {
				list($service, $event, $params) = $recv;
			}

			if($service && $event) {
				$callable = [$service, $event];
			}
		}else {
			// 任务task进程
			list($callable, $params) = $recv;
		}
		
		// 控制器实例
		if($callable && $params) {
			$Dispatch = new RpcDispatch($callable, $params);
			$Dispatch->dispatch();
		}

		// 必须执行
		parent::end();
		return;
	}

	/**
	 * author 认证
	 * @return 
	 */
	public function author() {

	}
}