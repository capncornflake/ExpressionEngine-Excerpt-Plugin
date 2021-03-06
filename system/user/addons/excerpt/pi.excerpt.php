<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Excerpt {
	public static $name         = 'Excerpt';
	public static $version      = '2.1';
	public static $author       = 'Dennis Wyman';
	public static $author_url   = 'https://denniswyman.com';
	public static $description  = "Strips tags of whatever is in the tag pair and trunciates the remaining text";
	public static $typography   = FALSE;
    
	public $return_data = '';

	public function __construct()
	{
		// Fetch and sanitize tag parameters.
    	$indicator  = ee('Security/XSS')->clean(ee()->TMPL->fetch_param('indicator', ''));
    	$limit      = ee('Security/XSS')->clean(ee()->TMPL->fetch_param('limit', 100));
    	$limit_type = ee('Security/XSS')->clean(ee()->TMPL->fetch_param('limit_type', 'words'));
    	
		// Check that limit and limit_type parameters are supported options.
		// If either check fails, spit out errors to dev log and force default fallback.
		if ((! ctype_digit($limit)) || (in_array($limit_type, array('words','chars')) === FALSE))
		{
			ee()->load->library('logger');
	    	if (! ctype_digit($limit))
	    	{
				ee()->logger->developer('Excerpt plugin error: Limit parameter not numeric.', TRUE, 86400);
				unset($limit);
				$limit = 100;
	    	}
			if (in_array($limit_type, array('words','chars')) === FALSE)
			{
				ee()->logger->developer('Excerpt plugin error: Unknown limit_type. Supported options: words, chars', TRUE, 86400);
				unset($limit_type);
				$limit_type = 'words';
			}
		}

		// Fetch and sanitize tag data.
		$totrim = ee('Security/XSS')->clean(ee()->TMPL->tagdata);

		// Strip tags and other characters from tag data.
		$totrim = strip_tags($totrim);
        $totrim = str_replace("\n", ' ', $totrim);
        $totrim = preg_replace("/\s+/", ' ', $totrim);
        $totrim = trim($totrim);
        
        // Do a word count on stripped tag data.
        $words = explode(' ', $totrim);
        $count = count($words);

		// If our excerpt is generated by word count:
        if ($limit_type === 'words')
        {
        	if ($count <= $limit)
        	{
				// Do nothing if word count within limit parameter.
				$trimmed = $totrim;
        	}
        	else
        	{
				// Trunciate tag data if limit parameter exceeded.
				$totrim = trim(implode(' ', array_slice($words, 0, $limit)));
				$trimmed = $totrim . (count($words) > $limit ? $indicator : '');
			}
		}
		
		// If our excerpt is generated by character count:
		elseif ($limit_type === 'chars')
		{
			$output = "";

			// Do a character count.
			foreach($words as $word)
			{
				$output .= $word;
				if (strlen($output) > $limit) break;
				$output .= ' ';
        	}

			if (strlen($output) > $limit)
			{
				// Trunciate tag data if limit parameter exceeded.
		        $trimmed = $output . $indicator;
			}
			else
			{
				// Do nothing if character count within limit parameter.
		        $trimmed = $output;
			}
		}

		// Spit out generated excerpt to template.
		$this->return_data = $trimmed;
	}
}

/* End of file pi.excerpt.php */
/* Location: ./system/user/addons/excerpt/pi.excerpt.php */