The juju-framework is a minimalistic framework, that can be used for simple template-driven pages.

Contact me via http://bondconsult.no/Contact, or github.com/bond - for submitting patches or questions.

The framework is very lightweight and flexible and can be used in many ways, it is intended for php developers. Here is a quick example of how to use it:

First, drop the juju-framework in your library-folder, in example "yourproject/lib/juju-framework". Create a index.php file that will describe your site:

<?php

$root = $_SERVER['DOCUMENT_ROOT'];
$lib = "$root/lib";

// include framework
require_once "$lib/juju-framework/Site.inc.php";

// define site
$site = new Site;
$site->name = 'My homepage';

// The script expects to have URLs rewritten to /index.php?url=$1, if you prefer to use PATH_INFO and rewrite to /index.php/$1 use this:
// $site->use_path_info();

// Add a page to the $site. This page will react to '/' URI, the page name (ie, for use in titles - will be 'welcome'. 
// The second option to the new page, is a array of options. The template-option is required. The template named 'welcome', will be translated into 'templates/welcome.php'. The options can be accessed via $page->opts['option_name']. IE, an option for including jquery can be set on the pages that needs jquery.
// The third option to $site->add_page(), 'main', is which menu's the site should be included in. This can also be an array, if you wish to include the page into multiple menus.
// the menu's can be fetched with: $site->get_menu('main'), or the name that was given to it.

$site->add_page('/', 
	new Page('welcome', 
		array(
			'template' => 'welcome',
		)
	),
  'main'
);

$site->add_page('/test',
  new Page('test',
    array(
      'template' => 'test',
    )
  )
);

// check if the current request is valid
if ($page = $site->load_requested_page()) {
      // valid request, render the usual layout-file. the layout file is where all your html design goes. Usually you will: "<?php include($page->template()) ?>" where you want the pages content to go.
      include( $page->layout() );
} else {
	$site->http_error('404');
  echo "<html><body><h1>404: Page not found</h1></body></html>";
  exit();
}

?>
