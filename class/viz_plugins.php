<?php
class viz_plugin{
	public $api;
	public $redis;
	public $mongo;
	function viz_plugin($api,$redis,$mongo){
		$this->api=&$api;
		$this->redis=&$redis;
		$this->mongo=&$mongo;
	}
	function execute($id,$data){
		foreach($data as $transaction){
			$operation_name=$transaction['op'][0];
			$operation_data=$transaction['op'][1];
			if(method_exists($this,$operation_name)){
				$date=date_parse_from_format('Y-m-d\TH:i:s',$transaction['timestamp']);
				$unixtime=mktime($date['hour'],$date['minute'],$date['second'],$date['month'],$date['day'],$date['year']);

				$this->$operation_name(array('block_id'=>$transaction['block'],'tx_id'=>$transaction['trx_in_block'],'op_id'=>$transaction['op_in_trx'],'tx_hash'=>$transaction['trx_id'],'timestamp'=>$transaction['timestamp'],'unixtime'=>$unixtime),$operation_data);
			}
		}
	}
}
class viz_plugins{
	public $listeners=array();
	private $api;
	private $redis;
	private $mongo;
	function viz_plugins(){
		global $config,$site_root,$api,$redis,$mongo_connect;
		$this->api=&$api;
		$this->redis=&$redis;
		$this->mongo=&$mongo_connect;
		foreach($config['plugins'] as $plugin_name){
			include_once($site_root.'/module/plugins/'.$plugin_name.'.php');
			$plugin_class='viz_plugin_'.$plugin_name;
			if(class_exists($plugin_class)){
				$plugin=new $plugin_class($this->api,$this->redis,$this->mongo);
				$this->listeners[]=&$plugin;
			}
		}
	}
	function block($id,$data){
		if(!isset($data[0])){
			return false;
		}
		foreach($this->listeners as $listener){
			if(method_exists($listener,'get_block_header')){
				$listener->get_block_header($this->api->execute_method('get_block_header',array($id)));
			}
			if(method_exists($listener,'get_block')){
				$listener->get_block($this->api->execute_method('get_block',array($id)));
			}
			$listener->execute($id,$data);
		}
		return true;
	}
}