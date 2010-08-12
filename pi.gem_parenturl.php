<?php
/**
 *
 * Green Egg Media Structure Parent URL
 * ExpressionEngion Engine 2.0
 * 
 * http://www.greeneggmedia.com/
 *
 * This file must be placed in the /system/expressionengine/third_party/gem_parenturl/ folder in your ExpressionEngine installation.
 * @package 		gem_parenturl
 * @category		Plugin
 * @version 		Version 0.1.0 (Alpha)
 * @author			Lance Johnson (Green Egg Media)
 * @copyright 		Copyright (c) 2010 Green Egg Medias
 * @license 		Attribution-ShareAlike 3.0 Unported http://creativecommons.org/licenses/by-sa/3.0/
 * 
 * Purpose: Simple extension to Structure module which returns the URL of a child's parent.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info =	array(  'pi_name'           => 'EE2 GEM Structure Parent URL',
                        'pi_version'        => '0.1.0',
                        'pi_author'         => 'Green Egg Media',
                        'pi_author_url'     => 'http://www.greeneggmedia.com/',
                        'pi_description'    => 'Returns the parent URL of Structure pages',
                        'pi_usage'          => Gem_parenturl::usage()
				);

class Gem_parenturl {
	
	public $return_data = ""; // return data from the Constructor
	
	function Gem_parenturl($entry_id=NULL) {
		
		
		$this->EE =& get_instance();
		
		//check to verify that structure is installed
		$installed_sql =   "SELECT module_name
							FROM exp_modules
							WHERE module_name = 'Structure'
							LIMIT 1";
		$installed_result = $this->EE->db->query($installed_sql);
		$installed = $installed_result->num_rows > 0 ? TRUE : FALSE;
		if (!$installed) {
			$this->return_data = "";
			return;
		}

		require_once(PATH_THIRD . 'structure/mod.structure.php');
		$structure = new Structure();

		$home = trim($this->EE->functions->fetch_site_index(0, 0), '/');

		// get site pages data
		$site_pages = $structure->get_site_pages();
		if (!$site_pages) return FALSE;

		// get current uri path
		$uri = '/'.$this->EE->uri->uri_string().'/';
		// get current entry id
		$entry_id = $entry_id ? $entry_id : array_search($uri, $site_pages['uris']);
		// get node of the current entry
		$node = $entry_id ? $structure->nset->getNode($entry_id) : false;
		
		// node does not have any structure data we return the home page to prevent errors
		if ($node === false && ! $entry_id)
		{
			$this->return_data = $home;
			return;
		}
		
		// if we have an entry id but no node, we have listing entry. Do nothing.
		if ($entry_id && ! $node)
		{
			$this->return_data = $home;
			return;
		}

		// get entry's parent id
		$sql = "SELECT parent_id
				FROM exp_structure
				WHERE entry_id = $entry_id
				LIMIT 1";
		$result = $this->EE->db->query($sql);
		$pid = $result->row('parent_id');

		$this->return_data = $home . $site_pages['uris'][$pid];
		
	} /* END OF GEM_PARENT FUNCTION */

	static function usage() {
		ob_start();
		?>

Extends the Structure module and allows you to return the URL of the parent of the current entry. If requested for an entry that is not managed by Strucutre, that is a Structure listing page, or that has no parent, the URL returned will be the home page.

The Structure module must be installed.

The tag - {exp:gem_parenturl} - will return only the URL of the parent entry. It does not generate any HTML tags, which gives you flexibility to use it in many different ways.

USAGE EXAMPLE:

<a href="{exp:gem_parenturl}" class="some_class">Up One Level</a>
		
		<?php
		$buffer = ob_get_contents();

		ob_end_clean(); 

		return $buffer;
		
	} /* END OF USAGE FUNCTION */
	
} /* END OF CLASS */

/* End of file pi.gem_parenturl.php */ 
/* Location: ./system/expressionengine/third_party/gem_parenturl/pi.gem_parenturl.php */

?>