<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Neon;


/**
 * Simple parser & generator for Nette Object Notation.
 */
class Neon
{
	const BLOCK = Encoder::BLOCK;
	const CHAIN = '!!chain';


	/**
	 * Returns the NEON representation of a value.
	 * @param  mixed
	 * @param  int
	 * @return string
	 */
	public static function encode($var, $options = NULL)
	{
		$encoder = new Encoder;
		return $encoder->encode($var, $options);
	}


	/**
	 * Decodes a NEON string.
	 * @param  string
	 * @return mixed
	 */
	public static function decode($input)
	{
		$decoder = new Decoder;
		return $decoder->decode($input);
	}

}
