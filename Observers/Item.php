<?php
namespace NetteLab\Observers;

class Item
{
	protected $observerableClass;
	
	public function getObserverableClass()
	{
		return $this->observerableClass;
	}
	
	protected $observerableEvent;
	
	public function getObserverableEvent()
	{
		return $this->observerableEvent;
	}
	
	protected $calledObserver;
	
	public function getCalledObserver()
	{
		return $this->calledObserver;
	}
	
	public function __construct($observerableClass, $observerableEvent, $calledObserver)
	{
		$this->observerableClass = $observerableClass;
		$this->observerableEvent = $observerableEvent;

		if(is_string($calledObserver))
		{
			list($class, $method) = explode('::', $calledObserver);
			$params = null;
			if(strpos($method, '(') !== false)
			{
				list($method, $paramsString) = explode('(', $method);
				$paramsString = substr($paramsString, 0, -1);
				$params = explode(',', $paramsString);
				array_walk($params, 'trim');
			}
			$observer = array('class' => $class, 'method' => $method, 'params' => $params);
		}
		else
		{
			throw new \InvalidArgumentException('Invalid observer value. Third parameter should be callback or factory string \''.gettype($calledObserver).'\' given.');
		}
		
		$this->calledObserver = $observer;
	}
	
	public function call($data)
	{
		$observer = $this->getCalledObserver();
		if(is_null($observer['params']) || empty($observer['params']))
		{
			call_user_func($this->getCalledObserver(), $data);
		}
		else
		{
			$callParams = array();
			foreach($observer['params'] as $param)
			{
				if(!isset($data[$param]))
				{
					throw new MissingObserverData('Required value for '.$param.' is missing');
				}
			}
		}
	}
}