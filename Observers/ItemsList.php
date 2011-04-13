<?php
namespace NetteLab\Observers;

class ItemsList
{
	protected $list = array();
	
	public function add($observerableClass, $observerableEvent, $calledObserver)
	{
		$this->list[] = new Item($observerableClass, $observerableEvent, $calledObserver);
	}
	
	public function search($class, $event)
	{
		$ret = array();
		
		foreach($this->list as $k => $v)
		{
			if($v->getObserverableClass() == $class && $v->getObserverableEvent() == $event)
			{
				$ret[] = $v;
			}
		}
		
		return $ret;
	}
}