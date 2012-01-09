<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Iterators
 */



/**
 * Callback iterator filter.
 *
 * @author     David Grudl
 */
class NCallbackFilterIterator extends FilterIterator
{
	/** @var callback */
	private $callback;


	/**
	 * Constructs a filter around another iterator.
	 * @param
	 * @param  callback
	 */
	public function __construct(Iterator $iterator, $callback)
	{
		parent::__construct($iterator);
		$this->callback = $callback;
	}



	public function accept()
	{
		return call_user_func($this->callback, $this);
	}

}
