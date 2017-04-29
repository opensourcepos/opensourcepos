<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Tracy;


/**
 * IBarPanel implementation helper.
 * @internal
 */
class DefaultBarPanel implements IBarPanel
{
	private $id;

	public $data;


	public function __construct($id)
	{
		$this->id = $id;
	}


	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start(function () {});
		$data = $this->data;
		require __DIR__ . "/assets/Bar/{$this->id}.tab.phtml";
		return ob_get_clean();
	}


	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start(function () {});
		if (is_file(__DIR__ . "/assets/Bar/{$this->id}.panel.phtml")) {
			$data = $this->data;
			require __DIR__ . "/assets/Bar/{$this->id}.panel.phtml";
		}
		return ob_get_clean();
	}

}
