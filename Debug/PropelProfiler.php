<?php
/**
 * This file is part of the Nette-lab
 *
 * Copyright (c) 2011 Martin Malek (http://www.marten-online.com)
 *
 * This source file is subject to the GPL license.
 * @package    nette-lab
 */
namespace NetteLab\Debug;

use Propel;
use PropelConfiguration;

/**
 * Propel profiler for Nette framework
 *
 * Profiler options:
 *   - 'explain' - explain SELECT queries
 *
 * Part of this file is used from dibi profiler.
 *
 * Put this into your code after including the Propel
 * <code>
 * new NetteLab\Debug\PropelProfiler(array());
 * </code>
 * 
 * @author	Martin Malek
 * @version	0.1-dev
 */
class PropelProfiler implements \Nette\IDebugPanel
{
	/** @var array */
	protected $log = array();

	/** @var bool  log to firebug? */
	public $useFirebug;

	/** @var bool  explain queries? */
	public $explainQuery = false;

	public function __construct(array $config)
	{
		if (is_callable('Nette\Debug::addPanel'))
		{
			call_user_func('Nette\Debug::addPanel', $this);
		}
		elseif (is_callable('NDebug::addPanel'))
		{
			NDebug::addPanel($this);
		}
		elseif (is_callable('Debug::addPanel'))
		{
			Debug::addPanel($this);
		}

		$config = Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
		$config->setParameter('debugpdo.logging.details.method.enabled', true);
		$config->setParameter('debugpdo.logging.details.time.enabled', true);
		$config->setParameter('debugpdo.logging.details.mem.enabled', true);
		$config->setParameter('debugpdo.logging.details.time.precision', 6);
		Propel::setLogger($this);

		$this->useFirebug = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/');

		if (isset($config['filter']))
		{
			$this->setFilter($config['filter']);
		}

		if (isset($config['explain']))
		{
			$this->explainQuery = (bool) $config['explain'];
		}
	}

	public function emergency($m)
	{
		$this->log($m, Propel::LOG_EMERG);
	} 
	
	public function alert($m)
	{
		$this->log($m, Propel::LOG_ALERT);
	}
	
	public function crit($m)
	{
		$this->log($m, Propel::LOG_CRIT);
	}
	
	public function err($m)
	{
		$this->log($m, Propel::LOG_ERR);
	}
	
	public function warning($m)
	{
		$this->log($m, Propel::LOG_WARNING);
	}
	
	public function notice($m)
	{
		$this->log($m, Propel::LOG_NOTICE);
	}
	
	public function info($m)
	{
		$this->log($m, Propel::LOG_INFO);
	}
	
	public function debug($m)
	{
		$this->log($m, Propel::LOG_DEBUG);
	}
	
	public function log($m, $priority)
	{
		list($method, $time, $mem, $query) = explode('|', $m, 4);
		list(,$mem) = explode(':', trim($mem));
		list(,$time) = explode(':', trim($time));
		list($time,) = explode(' ', trim($time));
		
		$explain = null;
		// @TODO get the explain information for query
		if ($this->explainQuery && strpos(strtolower(trim($query)), 'select') === 0)
		{
			$tmpSql = dibi::$sql;
			try
			{
				$explain = dibi::dump('EXPLAIN ' . $query, TRUE);
			}
			catch (DibiException $e) {}
		}

		// get a backtrace to pass class, function, file, & line
		$trace = debug_backtrace();

		$this->log[] = array(
			'priority' => $priority,
			'time' => (float)trim($time),
			'memory' => trim($mem),
			'query' => trim($query),
			'explain' => $explain,
			'method' => $trace[2]['class'].'->'.$trace[2]['function'].' in '.$trace[1]['file'].' at '.$trace[1]['line']
		);
	}
	
	public function getLog()
	{
		return $log;
	}
	
	public function getSumTime()
	{
		$sum = (float)0.000000;
		foreach($this->log as $v)
		{
			$sum += (float)sprintf('%0.6f', $v['time']);
		}
		
		return $sum;
	}
	
	public function getId()
	{
		return 'PropelProfiler';
	}
	
	public function getTab()
	{
		return '<img src="data:image/gif;base64,R0lGODlhEQAQAMQYAPT09Pj4+P39/f7+/vLy8vv7+/n5+d3d3fHx8ejo6OTk5OXl5fr6+nh4eO7u7vX19fz8/Orq6s/Pz6Kiotra2n5+fjMzM////////wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAABgALAAAAAARABAAAAVmIIZVZGmKqFhdbMuSqeq6axWv80tRNornpt4oN+NhAgEJ0XWYMAaDpUtBQFiu1ws2a7kkAleBoNVldbsO8PZMiADOF0GhAYhq79oz4AKArPNYgFcGBntSFwULDyo7jY6NQkGSJRghADs%3D"> '.count($this->log).' queries in '.sprintf('%0.1f', $this->getSumTime()*1000).'ms';
	}
	
	public function renderTab()
	{
		echo $this->getTab();
	}
	
	public function getPanel()
	{
		$total_time = 0.0000;
		
		foreach($this->log as $v)
		{
			$total_time += $v['time'] * 1000;
		}
		
		$content = "
<h1>Queries: " . count($this->log) . ($total_time > 0 ? ', time: ' . sprintf('%0.3f', $total_time) . ' ms' : '') . "</h1>

<style>
	#nette-debug-PropelProfiler td.propel-sql { background: white }
	#nette-debug-PropelProfiler .nette-alt td.propel-sql { background: #F5F5F5 }
	#nette-debug-PropelProfiler .propel-sql div { display: none; margin-top: 10px; max-height: 150px; overflow:auto }
</style>

<div class='nette-inner'>
<table>
<tr>
	<th>Time</th><th>SQL Statement</th><th>Memory</th>
</tr>
";

		$i = 0; $classes = array('class="nette-alt"', '');
		foreach($this->log as $v)
		{
			$content .= "
<tr {$classes[++$i%2]}>
	<td>" . sprintf('%0.3f', $v['time'] * 1000) . (!empty($v['explain']) ? "
	<br /><a href='#' class='nette-toggler' rel='#nette-debug-PropelProfiler-row-$i'>explain&nbsp;&#x25ba;</a>" : '') . "</td>
	<td class='propel-sql'>" . $v['query'] . (!empty($v['explain']) ? "
	<div id='nette-debug-PropelProfiler-row-$i'>{$v['explain']}</div>" : '') . "</td>
	<td>{$v['memory']}</td>
</tr>
";
		}
		$content .= '</table></div>';
		return $content;
	}
	
	public function renderPanel()
	{
		echo $this->getPanel();
	}

	private function display($message, $color)
	{
		//echo "<p style='color: $color'>$message</p>";
	}
	
	private function priorityToColor($priority)
	{
		switch($priority)
		{
			case Propel::LOG_EMERG:
			case Propel::LOG_ALERT:
			case Propel::LOG_CRIT:
			case Propel::LOG_ERR:
				return 'red';
			break;       
			case Propel::LOG_WARNING:
				return 'orange';
			break;
			case Propel::LOG_NOTICE:
				return 'green';
			break;
			case Propel::LOG_INFO:
				return 'blue';
			break;
			case Propel::LOG_DEBUG:
				return 'grey';
			break;
		}
	}
}

?>