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
	$wgXhtmlNamespaces["og"] = "http://ogp.me/ns#";
	$title = $out->getTitle();
	$isMainpage = $title->equals(Title::newMainPage());
	
	$meta = array();
	
	$meta["og:type"] = $isMainpage ? "website" : "article";
	$meta["og:site_name"] = $wgSitename;
	$meta["og:title"] = $title->getPrefixedText();
	if ( isset($out->mMainImage) ) {
		$meta["og:image"] = wfExpandUrl($out->mMainImage->createThumb(100*3, 100));
	} else if ( $isMainpage ) {
		$meta["og:image"] = $wgLogo;
	}
	if ( isset($out->mDescription) ) // set by Description2 extension, install it if you want proper og:description support
		$meta["og:description"] = $out->mDescription;
	$meta["og:url"] = $title->getFullURL();
	
	foreach( $meta as $property => $value ) {
		if ( $value )
			//$out->addMeta($property, $value ); // FB wants property= instead of name= blech, is that even valid html?
			$out->addHeadItem("meta:property:$property", "	".Html::element( 'meta', array( 'property' => $property, 'content' => $value ) )."\n");
	}
	
	return true;
}
$wgHooks['ParserBeforeTidy'][] = 'efSocialcastOGPParserBeforeTidyHook';
function efSocialcastOGPParserBeforeTidyHook(&$parser, &$text){
	$text = "<h1>Socialcast</h1>" . $text;	
	
	return true;
}

