<?php
/**
 * SocialcastOGP
 *
 * @file
 * @ingroup Extensions
 * @author Monica Wilkinson (http://www.mediawiki.org/wiki/User:Ciberch) <ciberch@socialcast.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


if ( !defined( 'MEDIAWIKI' ) ) die( "This is an extension to the MediaWiki package and cannot be run standalone." );

$wgExtensionCredits['parserhook'][] = array (
	"path" => __FILE__,
	"name" => "SocialcastOGP",
	"author" => "[http://mediawiki.org/wiki/User:Ciberch]",
	'descriptionmsg' => 'socialcastogp-desc',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SocialcastOGP',
);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['SocialcastOGP'] = $dir . '/SocialcastOGP.magic.php';
$wgExtensionMessagesFiles['SocialcastOGP'] = $dir . '/SocialcastOGP.i18n.php';

$wgHooks['ParserFirstCallInit'][] = 'efSocialcastOGPParserInit';
function efSocialcastOGPParserInit( $parser ) {
	$parser->setFunctionHook( 'setmainimage', 'efSetMainImagePF' );
	return true;
}

function sc_add_like($style='width:300px;height:60px') {
	return get_div('like_container_id', $style, $wgSocialcastButtonToken);
}

function sc_add_discussion($style='width:300px;height:400px') {
	return get_div('discussion_container_id', $style, $wgSocialcastDiscussionToken);
}

function get_div($id, $style, $token) {
	if ($id != '' && $token != '') {
		return '<div id="' . $id . '" style="' . $style .
		'"></div><script type="text/javascript">_reach.push({container: "' . $id . '", domain: "https://' 
		. $wgSocialcastCommunityUrl . '", token: "' . $token . '"});</script>';
	} else {
		return '';
	}
}

function sc_add_author_stream($email, $style='width:300px;height:400px') { 
  return get_div_email($email, 'profile_container_id', $style, $wgSocialcastProfileToken);
}

function get_div_email($email, $id, $style, $token) {
	if ($id != '' && $token != '') {
		return '<div id="' . $id . '" style="' . $style .
		'"></div><script type="text/javascript">_reach.push({container: "' . $id . '", domain: "https://' 
		. $wgSocialcastCommunityUrl . '", token: "' . $token . '", email:"'. $email . '"});</script>';
	} else {
		return '';
	}
}


function efSetMainImagePF( $parser, $mainimage ) {
	$parserOutput = $parser->getOutput();
	if ( isset($parserOutput->eHasMainImageAlready) && $parserOutput->eHasMainImageAlready )
		return $mainimage;
	$file = Title::newFromText( $mainimage, NS_FILE );
	$parserOutput->addOutputHook( 'setmainimage', array( 'dbkey' => $file->getDBkey() ) );
	$parserOutput->eHasMainImageAlready = true;
	
	return $mainimage;
}

$wgParserOutputHooks['setmainimage'] = 'efSetMainImagePH';
function efSetMainImagePH( $out, $parserOutput, $data ) {
	$out->mMainImage = wfFindFile( Title::newFromDBkey($data['dbkey'], NS_FILE) );
}

$wgHooks['BeforePageDisplay'][] = 'efSocialcastOGPPageHook';
function efSocialcastOGPPageHook( &$out, &$sk ) {
	global $wgArticle, $wgLogo, $wgSitename, $wgXhtmlNamespaces;
	
	$title = $out->getTitle();
	$isMainpage = $title->equals(Title::newMainPage());
	
	$meta = array();
	
	$og_ns = 'http://ogp.me/ns#';
	$og_type = $isMainpage ? "website" : "article";
	
	if ($wgSocialcastUseMicrodata === false) {
		$meta["og:title"] = $title->getPrefixedText();
		if ( isset($out->mMainImage) ) {
			$meta["og:image"] = wfExpandUrl($out->mMainImage->createThumb(100*3, 100));
		} else if ( $isMainpage ) {
			$meta["og:image"] = $wgLogo;
		}
		if ( isset($out->mDescription) ) // set by Description2 extension, install it if you want proper og:description support
			$meta["og:description"] = $out->mDescription;
		$meta["og:url"] = $title->getFullURL();
		$wgXhtmlNamespaces["og"] = $og_ns;
		$meta["og:type"] = $og_type;
		$meta["og:site_name"] = $wgSitename;
		foreach( $meta as $property => $value ) {
			if ( $value )
				//$out->addMeta($property, $value ); // FB wants property= instead of name= blech, is that even valid html?
				$out->addHeadItem("meta:property:$property", "	".Html::element( 'meta', array( 'property' => $property, 'content' => $value ) )."\n");
		}
	}
	
	
	return true;
}


function sc_reach_add_js() {
return "
  <script type='text/javascript'>
    (function(){
      var e=document.createElement('script');
      e.type='text/javascript';
      e.async = true;
      e.src= document.location.protocol + '" . $wgSocialcastCommunityUrl . "/services/reach/extension.js';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(e, s);
    })();
  </script>";
}

$wgHooks['ParserBeforeTidy'][] = 'efSocialcastOGPParserBeforeTidyHook';
function efSocialcastOGPParserBeforeTidyHook(&$parser, &$text){
	// $og_ns = 'http://ogp.me/ns#';
	// $og_type = $isMainpage ? "website" : "article";
	// $title = $parser->getTitle();
	// $url = $title->getFullURL() || '';
	// Adds a like/recommend button to the top and a discussion to the bottom
	$text = '<script type="text/javascript">var _reach = _reach || [];</script>' . sc_add_like() . $text . sc_add_discussion() . sc_reach_add_js();	
// TODO: Figure out how to do the image
	return true;
}

# Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'efSocialcastReachFunction_Setup';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]       = 'efSocialcastReachFunction_Magic';
 
function efSocialcastReachFunction_Setup( &$parser ) {
        # Set a function hook associating the "example" magic word with our function
 $parser->setFunctionHook( 'reach', 'efSocialcastReachFunction_Render' );
        return true;
}
 
function efSocialcastReachFunction_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is whether to be case sensitive, in this case (0) it is not case sensitive, 1 would be sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['reach'] = array( 0, 'reach' );
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}
 
function efSocialcastReachFunction_Render( $parser, $id = 'reach_container_id', $style = '' , $token = '') {
        # The parser function itself
        # The input parameters are wikitext with templates expanded
        # The output should be wikitext too
        $output = get_div($id, $style, $token);
        return $output;
}


