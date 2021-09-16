<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Smileys extends BaseConfig
{
	/*
	| -------------------------------------------------------------------
	| SMILEYS
	| -------------------------------------------------------------------
	| This file contains an array of smileys for use with the emoticon helper.
	| Individual images can be used to replace multiple smileys.  For example:
	| :-) and :) use the same image replacement.
	|
	| Please see user guide for more info:
	| https://codeigniter.com/user_guide/helpers/smiley_helper.html
	|
	*/
	public $smileys = [
	//	smiley			image name					width	height	alt
	':-)'			=>	['grin.gif',			'19',	'19',	'grin'],
	':lol:'			=>	['lol.gif',			'19',	'19',	'LOL'],
	':cheese:'		=>	['cheese.gif',			'19',	'19',	'cheese'],
	':)'			=>	['smile.gif',			'19',	'19',	'smile'],
	';-)'			=>	['wink.gif',			'19',	'19',	'wink'],
	';)'			=>	['wink.gif',			'19',	'19',	'wink'],
	':smirk:'		=>	['smirk.gif',			'19',	'19',	'smirk'],
	':roll:'		=>	['rolleyes.gif',		'19',	'19',	'rolleyes'],
	':-S'			=>	['confused.gif',		'19',	'19',	'confused'],
	':wow:'			=>	['surprise.gif',		'19',	'19',	'surprised'],
	':bug:'			=>	['bigsurprise.gif',	'19',	'19',	'big surprise'],
	':-P'			=>	['tongue_laugh.gif',	'19',	'19',	'tongue laugh'],
	'%-P'			=>	['tongue_rolleye.gif',	'19',	'19',	'tongue rolleye'],
	';-P'			=>	['tongue_wink.gif',	'19',	'19',	'tongue wink'],
	':P'			=>	['raspberry.gif',		'19',	'19',	'raspberry'],
	':blank:'		=>	['blank.gif',			'19',	'19',	'blank stare'],
	':long:'		=>	['longface.gif',		'19',	'19',	'long face'],
	':ohh:'			=>	['ohh.gif',			'19',	'19',	'ohh'],
	':grrr:'		=>	['grrr.gif',			'19',	'19',	'grrr'],
	':gulp:'		=>	['gulp.gif',			'19',	'19',	'gulp'],
	'8-/'			=>	['ohoh.gif',			'19',	'19',	'oh oh'],
	':down:'		=>	['downer.gif',			'19',	'19',	'downer'],
	':red:'			=>	['embarrassed.gif',	'19',	'19',	'red face'],
	':sick:'		=>	['sick.gif',			'19',	'19',	'sick'],
	':shut:'		=>	['shuteye.gif',		'19',	'19',	'shut eye'],
	':-/'			=>	['hmm.gif',			'19',	'19',	'hmmm'],
	'>:('			=>	['mad.gif',			'19',	'19',	'mad'],
	':mad:'			=>	['mad.gif',			'19',	'19',	'mad'],
	'>:-('			=>	['angry.gif',			'19',	'19',	'angry'],
	':angry:'		=>	['angry.gif',			'19',	'19',	'angry'],
	':zip:'			=>	['zip.gif',			'19',	'19',	'zipper'],
	':kiss:'		=>	['kiss.gif',			'19',	'19',	'kiss'],
	':ahhh:'		=>	['shock.gif',			'19',	'19',	'shock'],
	':coolsmile:'	=>	['shade_smile.gif',	'19',	'19',	'cool smile'],
	':coolsmirk:'	=>	['shade_smirk.gif',	'19',	'19',	'cool smirk'],
	':coolgrin:'	=>	['shade_grin.gif',		'19',	'19',	'cool grin'],
	':coolhmm:'		=>	['shade_hmm.gif',		'19',	'19',	'cool hmm'],
	':coolmad:'		=>	['shade_mad.gif',		'19',	'19',	'cool mad'],
	':coolcheese:'	=>	['shade_cheese.gif',	'19',	'19',	'cool cheese'],
	':vampire:'		=>	['vampire.gif',		'19',	'19',	'vampire'],
	':snake:'		=>	['snake.gif',			'19',	'19',	'snake'],
	':exclaim:'		=>	['exclaim.gif',		'19',	'19',	'exclaim'],
	':question:'	=>	['question.gif',		'19',	'19',	'question']
	];
}

