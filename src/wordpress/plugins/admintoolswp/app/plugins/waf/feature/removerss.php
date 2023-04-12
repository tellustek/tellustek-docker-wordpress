<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureRemoverss extends AtsystemFeatureAbstract
{
	protected $loadOrder = 71;

	public function isEnabled()
	{
		return $this->cparams->getValue('removerss', 1);
	}

	/**
	 * On our custom hook, let's ask WordPress to remove RSS links and handle any RSS request
	 */
	public function onCustomHooks()
	{
		add_action('do_feed', array($this, 'disable_feed'), 1);
		add_action('do_feed_rdf', array($this, 'disable_feed'), 1);
		add_action('do_feed_rss', array($this, 'disable_feed'), 1);
		add_action('do_feed_rss2', array($this, 'disable_feed'), 1);
		add_action('do_feed_atom', array($this, 'disable_feed'), 1);
		add_action('do_feed_rss2_comments', array($this, 'disable_feed'), 1);
		add_action('do_feed_atom_comments', array($this, 'disable_feed'), 1);

		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );
	}

	public function disable_feed()
	{
		die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
	}
}
