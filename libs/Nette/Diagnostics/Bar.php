<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Diagnostics
 */



/**
 * Debug Bar.
 *
 * @author     David Grudl
 * @internal
 */
class NDebugBar extends NObject
{
	/** @var array */
	private $panels = array();



	/**
	 * Add custom panel.
	 * @param  IBarPanel
	 * @param  string
	 * @return void
	 */
	public function addPanel(IBarPanel $panel, $id = NULL)
	{
		if ($id === NULL) {
			$c = 0;
			do {
				$id = get_class($panel) . ($c++ ? "-$c" : '');
			} while (isset($this->panels[$id]));
		}
		$this->panels[$id] = $panel;
	}



	/**
	 * Renders debug bar.
	 * @return void
	 */
	public function render()
	{
		$panels = array();
		foreach ($this->panels as $id => $panel) {
			try {
				$panels[] = array(
					'id' => preg_replace('#[^a-z0-9]+#i', '-', $id),
					'tab' => $tab = (string) $panel->getTab(),
					'panel' => $tab ? (string) $panel->getPanel() : NULL,
				);
			} catch (Exception $e) {
				$panels[] = array(
					'id' => "error-$id",
					'tab' => "Error: $id",
					'panel' => nl2br(htmlSpecialChars((string) $e)),
				);
			}
		}
		require dirname(__FILE__) . '/templates/bar.phtml';
	}

}
