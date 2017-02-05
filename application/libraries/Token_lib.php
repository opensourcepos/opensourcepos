<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries/tokens/Token.php');
require_once(APPPATH . 'libraries/tokens/Token_customer.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_invoice_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_quote_sequence.php');
require_once(APPPATH . 'libraries/tokens/Token_suspended_invoice_count.php');
require_once(APPPATH . 'libraries/tokens/Token_year_invoice_count.php');

/**
 * Class Token_lib
 * $token_invoice_sequence = new Token_invoice_sequence();
 * $token_customer = new Token_customer();
 * $token_invoice_count = new Token_invoice_count();
 * $token_quote_sequence = new Token_quote_sequence();
 * $token_suspended_invoice_count = new Token_suspended_invoice_count();
 * $token_year_invoice_count = new Token_year_invoice_count();
*/
class Token_lib
{
	private $CI;

	private $_registered_tokens;


	public function __construct()
	{
		$this->CI =& get_instance();

		foreach (glob("tokens/*.php") as $filename)
		{
			include $filename;
		}
	}

	/**
	 * Expands all of the tokens found in a given text string and returns the results.
	 * @param $text
	 * @return mixed|string
	 */
	public function render($tokened_text)
	{

		// First apply the rendering for the "%" tokens if any are used
		if (strpos($tokened_text, '%') !== false)
		{
			$tokened_text = strftime($tokened_text);
		}

		// Call scan to build an array of all of the tokens used in the text to be transformed
		$token_tree = $this->scan($tokened_text);

		if (empty($token_tree))
		{
			if (strpos($tokened_text, '%') !== false)
			{
				return strftime($tokened_text);
			}
			else
			{
				return $tokened_text;
			}
		}

		$token_values = array();
		$tokens_to_replace = array();
		$this->generate($token_tree, $tokens_to_replace, $token_values);

		return str_replace($tokens_to_replace, $token_values, $tokened_text);
	}


	public function scan($text)
	{
		// Matches tokens with the following pattern: [$token:$length]
		preg_match_all('/
      \{             # [ - pattern start
      ([^\s\{\}:]+)  # match $token not containing whitespace : { or }
      (?:
      :              # : - separator
      ([^\s\{\}:]+)     # match $length not containing whitespace : { or }
      )?
      \}             # ] - pattern end
      /x', $text, $matches);

		$tokens = $matches[1];
		$lengths = $matches[2];

		$token_tree = array();
		for ($i = 0; $i < count($tokens); $i++) {
			$token_tree[$tokens[$i]][$lengths[$i]] = $matches[0][$i];
		}

		return $token_tree;
	}

	public function generate($used_tokens, &$tokens_to_replace, &$token_values)
	{
		foreach ($used_tokens as $token_code => $token_info)
		{
			// Generate value here based on the key value
			$token = Token::get_token($token_code);
			$token_value = $token->get_value();

			foreach ($token_info as $length => $token_spec)
			{
				$tokens_to_replace[] = $token_spec;
				if (!empty($length))
				{
					$token_values[] = str_pad($token_value, $length, '0', STR_PAD_LEFT);
				}
				else{
					$token_values[] = $token_value;
				}
			}
		}
		return $token_values;
	}

	/**
	 * This function is used to register the tokens that should be permitted in the context
	 * of this particular use
	 * @param array $supported_tokens
	 */
	public function register(array &$registered_tokens)
	{
		$_registered_tokens = $registered_tokens;
	}
}
?>
