<?php

/*
    Copyright 2010 Daniel Bond

    This file is part of the juju-framework.

    juju-framework is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    juju-framework is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with juju-framework.  If not, see <http://www.gnu.org/licenses/>.
*/

class Page
{
  public $title;
  public $opts = array();

  function __construct($title, $opts = NULL) {

    if ($opts != NULL && is_array($opts))
      $this->opts = $opts;

    if (!array_key_exists('template', $opts))
      trigger_error("template is a required option!", E_USER_ERROR);

    $this->title = $title;
  }

  function title() {
    return $this->title;
  }

  function template() {
    return $this->template_path( $this->opts['template'] );
  }

  function layout() {
    return (array_key_exists('layout', $this->opts)) ? $this->template_path($this->opts['layout']) : $this->template_path('layout.php');
  }

  private function template_path($template) {
    if ( substr($template, -4) == '.php' )
      return 'templates/' . $template;
    
    return 'templates/' . $template . '.php';
  }
}

class Site
{
	public $name = "Company Inc";
	private $prefix;
  private $loaded_path = false;
	private $page = false;
	private $use_path_info = false;

	private $navindex = array();

	private $menu = array();

  public function loaded_path() {
    return $this->loaded_path;
  }

	public function load_requested_page() {

		$req = $this->clean_path();

		// match page?
		if(array_key_exists($req, $this->navindex)) {
			$this->page = $this->navindex[$req];
      $this->loaded_path = $req;
			return $this->page;
    }

		return false;
	}

	public function use_path_info() {
		$this->use_path_info = true;
	}

	public function path_components() {
		$req = substr($this->clean_path(), 1);
	
		$harvest = array();
		$comp = array();

		if(!empty($req))
		$comp = explode('/', $req);

		// harvest real parts, from bottom up
		for($i = 0; $i <= count($comp); $i++) {

			$try = '/' . implode('/', array_slice($comp, 0, $i));

			if(array_key_exists($try, $this->navindex))
				array_push($harvest, array(
					'page' => $this->navindex[$try],
					'path' => $this->url_for($try)
				));
		}

		return $harvest;
	}

   private function clean_path() {

    if($this->use_path_info) {
      $req = $_SERVER['PATH_INFO'];

      // remove preceeding slash
      if(substr($req, 0, 1) == '/')
        $req = substr($req, 1);

    } else
  		$req = array_key_exists('url', $_GET) ? $_GET['url'] : "";

		// remove trailing '/'-char
		if( substr($req, -1) == '/' )
			$req = substr($req, 0, -1);

		// prefix with '/'
		$req = '/' . $req;

    // /index.php == /
    if($req == '/index.php')
      $req = '/';

		return $req;

	}

	public function image_path($image) {
		return $this->url_for('/images/' . $image);
	}

	public function set_prefix($prefix) {

		if(substr($prefix, -1) == '/')
			$prefix = substr($prefix, 0, -1);

		$this->prefix = $prefix;
		return true;
	}

	public function prefix() {
		return $this->prefix;
	}

	public function page() {
		return $this->page;
	}

	public function get_menu($menu) {
		if(!array_key_exists($menu, $this->menu))
			trigger_error("The requested menu '$menu' dosn't exist!", E_USER_NOTICE);

		return $this->menu[$menu];
	}

	public function template_file() {
		return $this->page->template();
	}

	public function is_valid_page() {
		return $this->page;
	}

	public function add_page($path, &$page, $menu=NULL) {
		if(!is_object($page) || get_class($page) != "Page")
			trigger_error('page must be a Page.class', E_USER_ERROR);

		// only add simple sanity checks on URLs for now
		if($path == NULL || $path == '')
			$path = '/';

		if(substr($path, 0, 1) != '/')
			trigger_error("path must start with '/'", E_USER_ERROR);

		if(array_key_exists($path, $this->navindex))
			trigger_error("page '$path' is allready mapped as a page", E_USER_ERROR);

		// remove trailing slash if provided
		if( strlen($path) > 1 && substr($path, -1) == "/" )
			$path = substr($path, 0, -1);

		// add the item to navindex
		$this->navindex[$path] = $page;

		// add the item to menu, if requested
    if($menu && !empty($menu))
      if(is_array($menu)) {
        foreach($menu as $m)
          $this->menu[$m][] = array( 'page' => $page, 'path' => $this->url_for($path) );
      } else {
    		$this->menu[$menu][] = array( 'page' => $page, 'path' => $this->url_for($path) );
		  }
	}

	public function url_for($path) {
		if ($path == "/")
			return $this->prefix . "/";

		return $this->prefix . $path;
		
	}

	public function http_error($status) {
		// assume 404 for now
		if($status == '404') {
			ob_start();
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");

		} else {
			trigger_error("Handled error '$status' in Site.class, but missing handler", E_USER_ERROR);
		}
	}
}

?>
