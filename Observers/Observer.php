<?php
namespace NetteLab\Observers;

class Observer
{
	protected static $observers = null;
	
	public static function pseudeInit()
	{
		if(is_null(self::$observers))
		{
			self::$observers = new ItemsList();
		}
	}
	
	public static function addObserver($observerableClass, $observerableEvent, $calledObserver)
	{
		self::$observers->add($observerableClass, $observerableEvent, $calledObserver);
	}
	
	public static function loadConfig($config = null)
	{
		if(is_null($config) || empty($config))
			return;
		
		if(!is_array($config['observer']) && !($config['observer'] instanceof \Nette\Config\Config))
		{
			throw new \InvalidArgumentException('First parameter should be array, '.gettype($config['observer']).' given.');
		}
		
		foreach($config['observer'] as $class => $tmp)
		{
			foreach($tmp as $event => $call)
			{
				self::addObserver($class, $event, $call);
			}
		}
	}
	
	/**
	 * Call the observers
	 *
	 * <example>
	 * \NetteLab\Observers\Observer::call('MyClass', 'MyStaticMethod', array('id' => 5, 'newState' => 3));
	 * </example>
	 *
	 * @param	string	$class	Name of the class with namespace
	 * @param	string	$event	Name of the event
	 * @param	mixed	$data	Data for the observer
	 */
	public static function call($class, $event, $data)
	{
		$observers = self::$observers->search($class, $event);
		foreach($observers as $v)
		{
			$v->call($data);
		}
	}
}

class MissingObserverData extends \Exception
{
	public function __construct($message, $code = null, $parent = null)
	{
		parent::__construct($message, $code = null, $parent = null);
	}
}

Observer::pseudeInit();